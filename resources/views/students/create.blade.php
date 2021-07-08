@extends('layouts.app')

@section('title', trans('students.title_new'))

@inject('termService', 'CzechitasApp\Services\Models\TermService')

@section('content')
    <div class="row justify-content-center">
        <div class="col">
            <div class="card">
                <script>
                    var CzechitasTermData = {};
                    @foreach ($terms as $term) CzechitasTermData[{{ $term->id }}] = @json($termService->setContext($term)->getNewApplicationTermData()); @endforeach

                </script>
                <form action="{{ route('students.store') }}" method="POST">
                    @csrf

                    <table class="table table-twocols">
                        <tr>
                            <td>@lang('students.form.category')</td>
                            <td>{{ $category->name }}</td>
                        </tr>

                        <tr>
                            <td>@lang('students.form.term'):</td>
                            <td>
                                 <select name="term_id" id="term_id" class="selectpicker form-control{{ $errors->has('term_id') ? ' is-invalid' : '' }}" data-live-search="true" title="@lang('students.form.term_select')..." required>
                                    @foreach ($terms as $term)
                                        <option value="{{ $term->id }}" {{ oldSelected('term_id', $term->id) }} data-term-id="{{ $term->id }}">
                                            {{ $term->term_range }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('term_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('term_id') }}</strong>
                                    </span>
                                @enderror
                            </td>
                        </tr>

                        <tr>
                            <td>@lang('students.form.parent_name'):</td>
                            <td>
                                <input id="parent_name" type="text" class="form-control{{ $errors->has('parent_name') ? ' is-invalid' : '' }}" name="parent_name" value="{{ old('parent_name', Auth::user()->name ?? null) }}" required>
                                @error('parent_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('parent_name') }}</strong>
                                    </span>
                                @enderror
                            </td>
                        </tr>

                        <tr>
                            <td>@lang('students.form.forename'):</td>
                            <td>
                                <input id="forename" type="text" class="form-control{{ $errors->has('forename') ? ' is-invalid' : '' }}" name="forename" value="{{ old('forename') }}" required>
                                @error('forename')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('forename') }}</strong>
                                    </span>
                                @enderror
                            </td>
                        </tr>

                        <tr>
                            <td>@lang('students.form.surname'):</td>
                            <td>
                                <input id="surname" type="text" class="form-control{{ $errors->has('surname') ? ' is-invalid' : '' }}" name="surname" value="{{ old('surname') }}" required>
                                @error('surname')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('surname') }}</strong>
                                    </span>
                                @enderror
                            </td>
                        </tr>

                        <tr>
                            <td>
                                @lang('students.form.birthday'):<br>
                                <small class="text-muted">@lang('students.form.birthday_note', ['years' => config('czechitas.student.minimum_age_term_starts')])</small>
                            </td>
                            <td>
                                <input id="birthday" type="text" class="form-control{{ $errors->has('birthday') ? ' is-invalid' : '' }}" name="birthday" value="{{ old('birthday') }}" placeholder="dd.mm.rrrr" required>
                                @error('birthday')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('birthday') }}</strong>
                                    </span>
                                @enderror
                            </td>
                        </tr>

                        <tr>
                            <td>@lang('students.form.email'):</td>
                            <td>
                                <div class="input-group">
                                    <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email', Auth::user()->email ?? null) }}" required>
                                    <div class="input-group-append">
                                        <div class="input-group-text"><i class="fa fa-at"></i></div>
                                    </div>
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </td>
                        </tr>

                        <tr class="configurable" data-part="select_payment">
                            <td>@lang('students.form.payment'):</td>
                            <td>
                                @foreach (\CzechitasApp\Models\Enums\StudentPaymentType::getAvailableValues() as $paymentType)
                                    <span class="custom-control custom-radio{{ $errors->has('payment') ? ' is-invalid' : '' }}">
                                        <input type="radio" id="payment_{{ $paymentType }}" name="payment" value="{{ $paymentType }}" class="custom-control-input" {{ oldChecked('payment', $paymentType) }} required>
                                        <label class="custom-control-label" for="payment_{{ $paymentType }}">@lang('students.payments.'.$paymentType)<small class="text-muted"> - @lang("students.payments.{$paymentType}_desc")</small></label>
                                    </span>
                                @endforeach
                                @error('payment')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('payment') }}</strong>
                                    </span>
                                @enderror
                            </td>
                        </tr>

                        <tr>
                            <td>@lang('students.form.restrictions'):</td>
                            <td>
                                <span class="custom-control custom-checkbox">
                                    <input type="checkbox" id="restrictions_yes" name="restrictions_yes" value="1" class="custom-control-input" {{ oldChecked('restrictions_yes', '1') }}>
                                    <label class="custom-control-label" for="restrictions_yes">@lang('students.form.restrictions_yes')</label>
                                </span>
                                <textarea name="restrictions" class="form-control mt-2{{ $errors->has('restrictions') ? ' is-invalid' : '' }}" id="restrictions" rows="3" required placeholder="@lang('students.form.restrictions_desc')">{{ old('restrictions') }}</textarea>
                                @error('restrictions')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('restrictions') }}</strong>
                                    </span>
                                @enderror
                            </td>
                        </tr>

                        <tr>
                            <td>@lang('students.form.note'):</td>
                            <td>
                                <textarea maxlength="150" name="note" class="form-control{{ $errors->has('note') ? ' is-invalid' : '' }}" id="note" rows="3">{{ old('note') }}</textarea>
                                @error('note')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('note') }}</strong>
                                    </span>
                                @enderror
                            </td>
                        </tr>

                        <tr>
                            <td></td>
                            <td>
                                <span class="custom-control custom-checkbox{{ $errors->has('terms_conditions') ? ' is-invalid' : '' }}">
                                    <input type="checkbox" id="terms_conditions" name="terms_conditions" value="1" class="custom-control-input" {{ oldChecked('terms_conditions', '1') }}>
                                    <label class="custom-control-label" for="terms_conditions">{!! trans('students.form.terms_conditions') !!}</label>
                                </span>
                                @error('terms_conditions')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('terms_conditions') }}</strong>
                                    </span>
                                @enderror

                                <input type="submit" class="btn btn-primary mt-3" value="@lang('students.form.submit')">
                            </td>
                        </tr>

                    </table>

                </form>
            </div>
        </div>
    </div>
@endsection
