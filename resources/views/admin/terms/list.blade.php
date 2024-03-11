@extends('layouts.app')

@section('title', trans('terms.title'))

@section('scripts')
    <script type="text/javascript">
        CzechitasApp.datatables.init({
            ajax: {
                url: "{{ route('admin.terms.ajax_list') }}",
                error: CzechitasApp.datatables.ajaxError
            },
            processing: true,
            serverSide: true,
            withArchiveFilter: true,
            columns: CzechitasApp.datatables.formatColumns(@json($columnNames), ['id']),
            order: [[1, "asc"]],
            columnDefs: [{ targets: 1, responsivePriority: 1},{ targets: [2,4], orderable: false},
            {
                targets: 0,
                data: "flag",
                render: CzechitasApp.datatables.getRenderer('flag', $(".flag_change_template")),
            },
            {
                targets: 3,
                render: function (data, type, row, meta) {
                  return data+5;
                }
            },
            {
                targets: 4,
                data: "id",
                orderable: false,
                responsivePriority: 2,
                render: CzechitasApp.datatables.getRenderer('action', $(".action_buttons_template")),
            }]
        });
    </script>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col">
        <div class="card">
            <div class="card-header text-right">
                <div class="btn-group" role="group">
                    @can('create', \CzechitasApp\Models\Term::class)
                        <a href="{{ route('admin.terms.create') }}" class="btn btn-sm btn-info"><i class="fa fa-fw mr-1 fa-plus-circle "></i>{{ trans('app.actions.create') }}</a>
                    @endcan
                </div>
            </div>
                <table class="table table-striped" data-table>
                    <thead>
                        <tr>
                            <th><i class="fa fa-flag"></i></th>
                            <th>{{ trans('terms.table.range') }}</th>
                            <th>{{ trans('terms.table.category') }}</th>
                            <th>
                                <span data-toggle="tooltip" title="@lang('terms.table.students_info')">
                                    {{ trans('terms.table.students') }} <i class="fa fa-fw fa-info-circle pr-1"></i>
                                </span>
                            </th>
                            <th style="width: 10%">{{ trans('terms.table.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                    <tfoot>
                        <tr>
                            <th><i class="fa fa-flag"></i></th>
                            <th>{{ trans('terms.table.range') }}</th>
                            <th>{{ trans('terms.table.category') }}</th>
                            <th>{{ trans('terms.table.students') }}</th>
                            <th>{{ trans('terms.table.action') }}</th>
                        </tr>
                    </tfoot>
                </table>
                {{-- Buttons template --}}
                <div class="d-none action_buttons_template">
                    <div class="btn-group" role="group">
                          <a href="{{ route('admin.terms.show', ["__placeholder__"]) }}" data-can="view" title="@lang('app.actions.show')" class="btn btn-sm btn-secondary"><i class="fa fa-fw fa-info-circle"></i></a>
                          <a href="{{ routeBack('admin.terms.edit',["__placeholder__"], 'list') }}" data-can="update" title="@lang('app.actions.edit')" class="btn btn-sm btn-success"><i class="fa fa-fw fa-edit"></i></a>
                          <a href="#deleteTerm__placeholder__" data-toggle="modal" data-can="delete" title="@lang('app.actions.destroy')" class="btn btn-sm btn-danger"><i class="fa fa-fw fa-trash"></i></a>
                    </div>
                    <div data-can="delete">
                        @component('components.modal_yes_no_form', [ 'id' => 'deleteTerm__placeholder__', 'route' => route('admin.terms.destroy', "__placeholder__")] )
                            @lang('terms.delete_modal', ['date' => '{= data.term_range =}'])
                        @endcomponent
                    </div>
                </div>
                {{-- Flag change popup --}}
                <div class="d-none flag_change_template">
                    <a href="#flagChange__placeholder__" data-toggle="modal" class="btn btn-sm {= data.class_name =}"><i class="fa fa-fw {= data.flag_icon =}"></i></a>
                    <div data-can="update">
                        @component('components.flag_change', [ 'id' => 'flagChange__placeholder__', 'route' => route('admin.terms.flag_change', "__placeholder__")] )
                        @endcomponent
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
