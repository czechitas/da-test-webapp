@extends('layouts.app')

@section('title', trans('students.title_detail', ['student' => $student->name]))
@section('header', $student->name)

@section('content')
    <div class="row justify-content-center">
        <div class="col">
            <div class="card">

                <div class="card-header text-right">
                    <div class="btn-group" role="group">
                        @if (Auth::user()->can('sendEmails', $student) && $student->sendEmails()->count() > 0)
                            <a href="{{ route('students.send_emails', $student) }}" title="@lang('students.send_emails.button')" class="btn btn-sm btn-info"><i class="fa fa-fw fa-envelope pr-1"></i>@lang('students.send_emails.button')</a>
                        @endif
                        @if ($student->studentPayments->count() > 0)
                            <button type="button" title="@lang('students.payments.history.show_short')" data-toggle="modal" data-target="#studentPaymentHistory" class="btn btn-sm btn-secondary"><i class="fa fa-fw fa-dollar-sign pr-1"></i>@lang('students.payments.history.show_short')</button>
                        @endif
                        @can('update', $student)
                            <a href="{{ route('students.edit', $student) }}" title="@lang('app.actions.edit')" class="btn btn-sm btn-success"><i class="fa fa-fw fa-edit pr-1"></i>@lang('students.table.edit')</a>
                        @endcan
                        @can('logout', $student)
                            <a href="{{ route('students.logout', $student) }}" title="@lang('app.actions.edit')" class="btn btn-sm btn-danger"><i class="fa fa-fw fa-ban pr-1"></i>@lang('students.table.logout')</a>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    @php
                        $warnings = [];
                        if($student->canceled){
                            $warnings[] = trans('students.warnings.canceled', ['reason' => e($student->canceled)]);
                        }else if($student->logged_out){
                            $msg = null;
                            if(empty($student->logged_out_date)){
                                $msg = empty($student->alternate)
                                    ? trans('students.warnings.logged_out')
                                    : trans('students.warnings.logged_out_alternate', ['alternate' => e($student->alternate) ] );
                            }else{
                                $msg = empty($student->alternate)
                                    ? trans('students.warnings.logged_out_date', ['since' => $student->logged_out_date->format("d.m.Y H:i")])
                                    : trans('students.warnings.logged_out_date_alternate', [
                                        'alternate' => e($student->alternate),
                                        'since' => $student->logged_out_date->format("d.m.Y H:i")
                                    ] );
                            }
                            $msgReason = null;
                            if($student->logged_out === \CzechitasApp\Models\Enums\StudentLogOutType::ILLNESS) {
                                $msgReason = trans('students.warnings.logged_out_illness');
                            } else {
                                $msgReason = empty($student->logged_out_reason)
                                    ? trans('students.warnings.logged_out_other')
                                    : trans('students.warnings.logged_out_reason', ['reason' => e($student->logged_out_reason)]);
                            }
                            $warnings[] = "{$msg} - {$msgReason}";
                        }
                    @endphp
                    @if (!empty($warnings))
                        <div class="alert alert-warning" role="alert">
                          <ul class="m-0">
                              @foreach ($warnings as $note)
                                  <li>{!! $note !!}</li>
                              @endforeach
                          </ul>
                        </div>
                    @endif
                    <h4>
                        @lang('terms.form.term_range'): {{ $student->term->term_range }}<br>{{ $student->term->category->name }}
                    </h4>
                    @if (!$student->logged_out && !$student->canceled && !empty($student->term->note_public))
                        <hr>
                        <h4>@lang('terms.form.note_public')</h4>
                        {{ markdownToHtml($student->term->note_public) }}
                    @endif
                </div>

                <table class="table table-twocols real-middle border-top">
                    @if ( $student->canceled === null && $student->logged_out === null)
                        <tr>
                            <td colspan="2" class="bg-dark text-light text-center">@lang('students.form.payment_instructions')</td>
                        </tr>
                        <tr>
                            <td>@lang('students.form.payment'):</td>
                            <td>
                                <strong>{{ trans('students.payments.'.($student->payment ?? 'none')) }}</strong>
                                @if ($student->studentPayments->count() > 0)
                                    <br><button type="button" data-toggle="modal" class="btn btn-info" data-target="#studentPaymentHistory"><i class="fa fa-fw fa-dollar-sign pr-1"></i>@lang('students.payments.history.show')</button>
                                @endif
                            </td>
                        </tr>
                        @if ($student->price_to_pay < 0)
                            <tr>
                                <td colspan="2" class="text-center">@lang('students.form.payment_over', ['pay_over' => formatPrice(abs($student->price_to_pay))])</td>
                            </tr>
                        @elseif($student->price_to_pay == 0)
                            <tr>
                                <td colspan="2" class="text-center">@lang('students.form.payment_exact')</td>
                            </tr>
                        @else
                            @if ($student->payment == \CzechitasApp\Models\Enums\StudentPaymentType::CASH)
                                <tr>
                                    <td colspan="2" class="text-center">@lang('students.form.payment_cash_desc')</td>
                                </tr>
                            @elseif($student->payment == \CzechitasApp\Models\Enums\StudentPaymentType::FKSP)
                                <tr>
                                    <td colspan="2" class="text-center">{!! trans('students.form.payment_fksp_desc') !!}</td>
                                </tr>
                            @else
                                <tr>
                                    <td>@lang('students.form.acc_number')</td>
                                    <td>@lang('students.form.acc_number_val')</td>
                                </tr>
                                @if ($student->payment == \CzechitasApp\Models\Enums\StudentPaymentType::POSTAL_ORDER)
                                    <tr>
                                        <td>@lang('students.form.acc_address')</td>
                                        <td>{!! trans('students.form.acc_address_val') !!}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td>@lang('students.form.price')</td>
                                    <td><strong>{{ formatPrice($student->price_to_pay) }}</strong></td>
                                </tr>
                                <tr>
                                    <td>@lang('students.form.v_symbol')</td>
                                    <td>{{ $student->variable_symbol }}</td>
                                </tr>
                                <tr>
                                    <td>@lang('students.form.k_symbol')</td>
                                    <td>@lang('students.form.k_symbol_val')</td>
                                </tr>
                                <tr>
                                    <td>@lang('students.form.message')</td>
                                    <td>{{ $student->payment_message }}</td>
                                </tr>
                                @if ($student->payment == \CzechitasApp\Models\Enums\StudentPaymentType::TRANSFER)
                                    @inject('studentService', 'CzechitasApp\Services\Models\StudentService')
                                    <tr>
                                        <td colspan="2" class="text-center">
                                            <h4>@lang('students.form.qr_payment')</h4>
                                            <img src="data:image/png;base64,{!! $studentService->setContext($student)->getQRPayment()->toPngText(true) !!}" alt="QR Platba" style="width: 200px;">
                                        </td>
                                    </tr>
                                @endif
                            @endif
                        @endif
                    @endif

                    <tr>
                        <td colspan="2" class="bg-dark text-light text-center">@lang('students.form.student_details')</td>
                    </tr>
                    <tr>
                        <td>@lang('students.form.parent_name'):</td>
                        <td>{{ $student->parent_name }}</td>
                    </tr>

                    <tr>
                        <td>@lang('students.form.forename'):</td>
                        <td>{{ $student->forename }}</td>
                    </tr>

                    <tr>
                        <td>@lang('students.form.surname'):</td>
                        <td>{{ $student->surname }}</td>
                    </tr>

                    <tr>
                        <td>@lang('students.form.birthday'):</td>
                        <td>{{ $student->birthday }}</td>
                    </tr>

                    <tr>
                        <td>@lang('students.form.email'):</td>
                        <td>{{ $student->email }}</td>
                    </tr>
                    @if (!empty($student->restrictions))
                        <tr>
                            <td>@lang('students.form.restrictions'):</td>
                            <td>{{ $student->restrictions }}</td>
                        </tr>
                    @endif
                    @if (!empty($student->note))
                        <tr>
                            <td>@lang('students.form.note'):</td>
                            <td>{{ $student->note }}</td>
                        </tr>
                    @endif
                    @if (!empty($student->private_note))
                        <tr>
                            <td>@lang('students.form.note_private'):</td>
                            <td>{{ $student->private_note }}</td>
                        </tr>
                    @endif
                    @canany(['certificateLogin', 'certificatePayment'], $student) <tr>
                            <td>@lang('students.form.certificate')</td>
                            <td>
                                @can('certificateLogin', $student)
                                    <a href="{{ route('students.certificate.login', $student) }}" title="@lang('students.certificates.login_download')" class="mb-1 btn btn-sm btn-success"><i class="fa fa-fw fa-file-pdf pr-1"></i>@lang('students.certificates.login_download')</a><br>
                                @endcan
                                @can('certificatePayment', $student)
                                    <a href="{{ route('students.certificate.payment', $student) }}" title="@lang('students.certificates.payment_download')" class="mb-1 btn btn-sm btn-success"><i class="fa fa-fw fa-file-pdf pr-1"></i>@lang('students.certificates.payment_download')</a><br>
                                @endcan
                            </td>
                        </tr>
                    @endcanany
                    <tr>
                        <td>@lang('students.form.created_at'):</td>
                        <td>{{ $student->created_at->format("d.m.Y H:i") }}</td>
                    </tr>

                </table>
            </div>
        </div>
    </div>
    @if ($student->studentPayments->count() > 0)
        @component('components.student_payment_history', ['student' => $student])
        @endcomponent
    @endif
@endsection

