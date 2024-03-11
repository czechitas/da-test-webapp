@extends('layouts.app')

@section('title', trans('users.title'))
@section('header', trans('users.breadcrumbs.delete'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    @if ($possibleDelete)
                        <h4>@lang('users.delete.delete_heading')</h4>
                        <p>@lang('users.delete.delete')</p>

                        <p class="text-center">
                            <a href="#deleteUser" data-toggle="modal" title="@lang('users.actions.delete')" class="btn btn-sm btn-danger"><i class="fa fa-fw fa-trash"></i> @lang('users.delete.delete_btn')</a>
                        </p>

                        @component('components.modal_yes_no_form', [ 'id' => 'deleteUser', 'route' => route('admin.users.destroy', $user)] )
                            @lang('users.delete_modal', ['name' => $user->name])
                        @endcomponent
                    @else
                        <h4>@lang('users.delete.cannot')</h4>
                        <ul>
                            @foreach ($constraints as $constraint => $count)
                                <li>{{ trans('users.delete.constraints.'.$constraint) }}: <strong>{{ $count }}x</strong></li>
                            @endforeach
                        </ul>
                    @endif

                    <hr>

                    <h4>@lang('users.delete.block_heading')</h4>
                    <p>@lang('users.delete.block')</p>

                    <form action="{{ route('admin.users.block', $user) }}" method="POST" class="text-center">
                        @csrf
                        <button type="submit" class="btn btn-large btn-warning">@lang('users.delete.block_btn')</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
