<?php

declare(strict_types=1);

namespace CzechitasApp\Services\Models;

use Carbon\Carbon;
use CzechitasApp\Models\SendEmail;
use CzechitasApp\Models\Student;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @method SendEmail getContext()
 * @method Builder<SendEmail> getQuery()
 */
class SendEmailService extends ModelBaseService
{
    public const MAIL_STORAGE_DIRECTORY    = 'send_email';

    /**
     * Get FQCN category model
     */
    public function getModel(): string
    {
        return SendEmail::class;
    }

    /**
     * Insert new email into DB
     *
     * @param array<string, mixed> $data
     */
    public function insert(array $data): SendEmail
    {
        $path = $this->generatePath($data['student']);
        if (Storage::exists($path)) {
            Log::warning(\sprintf("File '%s' exists", $path));
            $path = \str_replace('.html', \sprintf('_%04d.html', \rand(1, 9999)), $path);
        }
        Storage::put($path, $data['body']);
        $data['filename'] = $path;

        $data['student_id'] = $data['student']->id ?? null;
        unset($data['body']);
        unset($data['student']);

        return $this->getModel()::create($data);
    }

    protected function generatePath(Student $student): string
    {
        return \sprintf(
            '%s%s/%s/%06d-%s-%s.html',
            \baseFolderName(),
            self::MAIL_STORAGE_DIRECTORY,
            Carbon::now()->format('Y-m'),
            $student->id,
            Str::slug($student->name),
            Carbon::now()->format('Y-m-d_H-i-s.u'),
        );
    }

    public function getFileContent(): ?string
    {
        return null;

        $storageConfig = Storage::getConfig();
        if (!empty($storageConfig['url'])) {
            return Http::get(Storage::url($this->getContext()->filename))->body();
        }

        if (Storage::exists($this->getContext()->filename)) {
            return Storage::get($this->getContext()->filename);
        }

        return null;
    }
}
