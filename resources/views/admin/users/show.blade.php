@extends('layouts.app')

@section('title', trans('users.title'))

@section('scripts')
    <script type="text/javascript">
        CzechitasApp.datatables.init({
            columnDefs: [{targets: 0, responsivePriority: 1},{ targets: 1, responsivePriority: 1},{ targets: 4, orderable: false, responsivePriority: 2}],
            disableSearchAutoFocus: false,
            order: [[0, "asc"], [1, 'asc']]
        });
    </script>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col">
            <div class="card">
                <div class="card-header text-right">
                    <div class="btn-group" role="group">
                        @can('update', $user)
                            <a href="{{ route('admin.users.edit', [$user]) }}" class="btn btn-sm btn-success">
                                <i class="fa fa-fw fa-edit"></i>
                                <span class="d-none d-md-inline">@lang('app.actions.edit')</span>
                            </a>
                        @endcan
                        @can('delete', $user)
                            @component('components.modal_yes_no_form', [ 'id' => 'deleteUser', 'route' => route('admin.users.destroy', $user)] )
                                @lang('users.delete_modal', ['name' => str_replace(" ", "&nbsp;", $user->name)])
                            @endcomponent
                            <a href="#deleteUser" data-toggle="modal" class="btn btn-sm btn-danger">
                                <i class="fa fa-fw fa-trash"></i>
                                <span class="d-none d-md-inline">@lang('app.actions.destroy')</span>
                            </a>
                        @endcan
                    </div>
                </div>
                <table class="table table-twocols border-bottom">
                    <tr>
                        <td>@lang('users.form.name'):</td>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <td>@lang('users.form.email'):</td>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td>@lang('users.form.role'):</td>
                        <td>{{ trans('users.role.'.$user->role) }}</td>
                    </tr>
                    <tr>
                        <td>@lang('users.form.created_at'):</td>
                        <td>{{ $user->created_at->format('d.m.Y H:i') }}</td>
                    </tr>

                </table>

                <table class="table table-striped" data-table>
                    <thead>
                        <tr>
                            <th>{{ trans('students.table.name') }}</th>
                            <th>{{ trans('students.table.category') }}</th>
                            <th>{{ trans('students.table.term') }}</th>
                            <th>{{ trans('students.table.price') }}</th>
                            <th style="width: 10%">{{ trans('students.table.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            <tr class="{{ studentListClass($student) }}">
                                <td>{{ $student->name }}</td>
                                <td>{{ $student->term->category->name }}</td>
                                <td data-order="{{ $student->term->start->format("Ymd") }}">{{ $student->term->term_range }}</td>
                                <td data-order="{{ $student->price_to_pay }}">{{ formatPrice($student->price_to_pay) }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @can('view', $student)
                                            <a href="{{ route('admin.students.show', $student) }}" title="@lang('app.actions.show')" class="btn btn-sm btn-secondary"><i class="fa fa-fw fa-info-circle pr-1"></i>@lang('students.table.info')</a>
                                        @endcan
                                        @can('update', $student)
                                            <a href="{{ route('admin.students.edit', $student) }}" title="@lang('app.actions.edit')" class="btn btn-sm btn-success"><i class="fa fa-fw fa-edit pr-1"></i>@lang('students.table.edit')</a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>{{ trans('students.table.name') }}</th>
                            <th>{{ trans('students.table.category') }}</th>
                            <th>{{ trans('students.table.term') }}</th>
                            <th>{{ trans('students.table.price') }}</th>
                            <th>{{ trans('students.table.action') }}</th>
                        </tr>
                    </tfoot>
                </table>

            </div>
        </div>
    </div>
@endsection
