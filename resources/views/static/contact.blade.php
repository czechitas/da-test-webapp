@extends('layouts.app')

@section('title', trans('pages.title.contact'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8" style="max-width: 800px;">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-5">
                            <h3>Czechitas</h3>
                            <p>
                                Dlouhá 123<br>
                                123 45 Horní Dolní
                            </p>
                            <p>
                                <a href="https://www.czechitas.cz/">www.czechitas.cz</a>
                            </p>
                        </div>
                        <div class="col-sm-7">
                            <iframe style="border:none" src="https://frame.mapy.cz/s/jajasumedo" width="400" height="280" frameborder="0"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
