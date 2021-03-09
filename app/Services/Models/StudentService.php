<?php

declare(strict_types=1);

namespace CzechitasApp\Services\Models;

use Carbon\Carbon;
use CzechitasApp\Models\Enums\StudentPaymentType;
use CzechitasApp\Models\Student;
use CzechitasApp\Models\StudentPayment;
use CzechitasApp\Models\Term;
use CzechitasApp\Models\User;
use CzechitasApp\Modules\Pdf\Pdf;
use CzechitasApp\Modules\QRPayment\QRPayment;
use CzechitasApp\Notifications\Student\StudentAdminUpdatedNotification;
use CzechitasApp\Notifications\Student\StudentCreatedNotification;
use CzechitasApp\Notifications\Student\StudentInsertedPaymentNotification;
use CzechitasApp\Notifications\Student\StudentLoggedOutNotification;
use CzechitasApp\Notifications\Student\StudentUpdatedNotification;
use CzechitasApp\Services\VariableSymbolService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @method Student getContext()
 * @method Builder<Student> getQuery()
 */
class StudentService extends ModelBaseService
{
    /** @var VariableSymbolService */
    private $variableSymbolService;

    public function __construct(VariableSymbolService $variableSymbolService)
    {
        $this->variableSymbolService = $variableSymbolService;
    }

    /**
     * Get FQCN category model
     */
    public function getModel(): string
    {
        return Student::class;
    }

    /**
     * @return Builder<StudentPayment>
     */
    public function getPaymentsQuery(): Builder
    {
        return StudentPayment::query();
    }

    /**
     * @return Builder<Student>
     */
    public function getListQuery(): Builder
    {
        return $this->getQuery()->whereHas('term');
    }

    /**
     * Get term or fail
     */
    public function findStudentOrFail(int $id): Student
    {
        return $this->getModel()::findOrFail($id);
    }

    /**
     * Get list of students for given parent
     *
     * @return Builder<Student>
     */
    public function getListForParent(User $user): Builder
    {
        return $this->getQuery()
            ->where('parent_id', $user->id)
            ->with([
                'term' => static function (BelongsTo $query): void {
                    /** @var BelongsTo<Term, Student> $query */
                    $query->withTrashed();
                },
                0 => 'term.category:id,name',
            ])
            ->withPaymentSum();
    }

    /**
     * @return Builder<Student>
     */
    public function getListForTerm(Term $term): Builder
    {
        return $this->getQuery()
            ->where('term_id', $term->id)
            ->with('term') // Add because of policies
            ->withPaymentSum();
    }

    /**
     * @return Builder<Student>
     */
    public function getListForFullTermExport(Term $term): Builder
    {
        return $this->getQuery()
            ->where('term_id', $term->id)
            ->with('term')
            ->withPaymentSum()
            ->orderBy('canceled')
            ->orderBy('logged_out')
            ->orderBy('surname')
            ->orderBy('forename');
    }

    /**
     * @return Builder<Student>
     */
    public function getListOfOverUnderPaid(Carbon $termDateFrom, Carbon $termDateTo): Builder
    {
        return $this->getQuery()
            ->whereHas('term', static function (Builder $query) use ($termDateFrom, $termDateTo): void {
                /** @var Builder<Term> $query */
                $query
                    ->where('start', '>=', $termDateFrom->format('Y-m-d'))
                    ->where('end', '<=', $termDateTo->format('Y-m-d'));
            })
            ->with('term', 'term.category:id,name')
            ->withPaymentSum();
    }

    /**
     * Show QR code payment - if user should pay and selected transfer
     */
    public function showQRPayment(): bool
    {
        $student = $this->getContext();

        return $student->price_to_pay > 0 && $student->payment == StudentPaymentType::TRANSFER;
    }

    /**
     * Get QRPayment instance configured for context student
     */
    public function getQRPayment(): QRPayment
    {
        $student = $this->getContext();

        return new QRPayment($student->price_to_pay, $student->variable_symbol, 308, null, $student->payment_message);
    }

    /**
     * Save new student, send notification and set context
     *
     * @param array<string, mixed> $data
     */
    public function insert(array $data): Student
    {
        if (isset($data['term'])) {
            unset($data['term']);
        }
        try {
            $data['parent_id'] = Auth::user()->id;

            DB::beginTransaction();
            $student = $this->getModel()::create($data);
            $student->variable_symbol = $this->variableSymbolService->generate($student->id);
            $student->save();
            DB::commit();

            $this->setContext($student);
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
        try {
            $student->notify(new StudentCreatedNotification($student));
        } catch (\Throwable $e) {
            Log::warning($e->getMessage(), ['exception' => $e]);
        }

        return $this->getContext();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(array $data): ?bool
    {
        $ret = null;
        $student = $this->getContext()->fill($data);
        if ($student->isDirty()) {
            $dirty = $student->getDirty();
            $ret = $student->save();

            try {
                // If only private_note is changed, do not send notification
                if (!(\count($dirty) == 1 && isset($dirty['private_note']))) {
                    $student->notify(new StudentUpdatedNotification($student));
                }
            } catch (\Throwable $e) {
                Log::warning($e->getMessage(), ['exception' => $e]);
            }
        }

        return $ret;
    }

    /**
     * Insert payment and send notification
     *
     * @param array<string, mixed> $data
     */
    public function insertPayment(array $data, bool $sendNotification = true): StudentPayment
    {
        $student = $this->getContext();
        unset($data['student_id']);

        if (empty($data['received_at'])) {
            $data['received_at'] = Carbon::now();
        }
        $data['user_id'] = Auth::user()->id ?? null;
        /** @var StudentPayment $payment */
        $payment = $student->studentPayments()->create($data);

        if ($sendNotification) {
            try {
                $student->notify(new StudentInsertedPaymentNotification($student, (float)$data['price']));
            } catch (\Throwable $e) {
                Log::warning($e->getMessage(), ['exception' => $e]);
            }
        }

        return $payment;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function logout(array $data): ?bool
    {
        $ret = null;
        $student = $this->getContext()->fill($data);
        if ($student->isDirty()) {
            $student->logged_out_date = Carbon::now();
            $ret = $student->save();
            try {
                $student->notify(new StudentLoggedOutNotification($student));
            } catch (\Throwable $e) {
                Log::warning($e->getMessage(), ['exception' => $e]);
            }
        }

        return $ret;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateLogout(array $data): ?bool
    {
        $sendNotification = true; //$data['send_notification'];
        unset($data['send_notification']);

        $ret = null;
        $student = $this->getContext()->fill($data);
        if ($student->isDirty()) {
            // Change logged_out_date only if is changed from regular to logged out or opposite
            // Not when only type of logout is changed
            if (
                $student->isDirty('logged_out') &&
                    ($student->logged_out === null || $student->getOriginal('logged_out') === null)
            ) {
                $student->logged_out_date = Carbon::now();
            }
            $ret = $student->save();

            try {
                if ($sendNotification) {
                    if ($student->logged_out) {
                        $student->notify(new StudentLoggedOutNotification($student));
                    } else {
                        $student->notify(new StudentAdminUpdatedNotification($student, 'logout'));
                    }
                }
            } catch (\Throwable $e) {
                Log::warning($e->getMessage(), ['exception' => $e]);
            }
        }

        return $ret;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateCanceled(array $data): ?bool
    {
        $sendNotification = true; //$data['send_notification'];
        unset($data['send_notification']);

        $ret = null;
        $student = $this->getContext()->fill($data);
        if ($student->isDirty()) {
            $ret = $student->save();

            try {
                if ($sendNotification) {
                    $student->notify(new StudentAdminUpdatedNotification($student, 'cancel'));
                }
            } catch (\Throwable $e) {
                Log::warning($e->getMessage(), ['exception' => $e]);
            }
        }

        return $ret;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     */
    protected function generateCertificatePdf(string $file, array $data = [], array $options = []): Pdf
    {
        $options = \array_merge([
            'title' => \trans("students.certificates.{$file}_file"),
        ], $options);

        return Pdf::loadView(
            'pdf.certificate.' . $file,
            \array_merge(['student' => $this->getContext()], $data),
            $options
        );
    }

    public function certificateLogin(): Pdf
    {
        return $this->generateCertificatePdf('login');
    }

    public function certificatePayment(): Pdf
    {
        /** @var StudentPayment $lastPayment */
        $lastPayment = $this->getContext()
            ->studentPayments()
            ->where('price', '>', 0)
            ->orderBy('received_at', 'desc')
            ->first();

        return $this->generateCertificatePdf('payment_non_exist', ['paymentDate' => $lastPayment->received_at]);
    }
}
