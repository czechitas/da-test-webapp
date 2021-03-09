<div class="col-12 col-sm-6 col-md footer__about">
    <img src="{{ asset('img/logo_footer.png') }}" alt="Logo" class="mb-3">
    {!! trans('app.footer.about') !!}
</div>
@inject('newsService', 'CzechitasApp\Services\Models\NewsService')
<div class="col-12 col-sm-6 col-md">
    <h4>@lang('app.footer.news')</h4>
    @foreach ($newsService->getNewsListQuery()->limit(2)->get() as $news)
        <div class="footer__news">
            <a href="{{ route('home') }}#novinka_{{ $news->id }}">{{ $news->title }}</a>
            <time>{{ $news->created_at->format("d.m.Y H:i") }}</time>
        </div>
    @endforeach
</div>
<div class="col-12 col-sm-6 col-md footer__links">
    <h4>@lang('app.footer.links')</h4>
    <a href="{{ route('static.parents') }}">@lang('app.footer.parents')</a>
    <a href="{{ route('static.teachers') }}">@lang('app.footer.teachers')</a>
    <a href="{{ route('orders.create') }}">@lang('app.footer.order')</a>
    <a href="https://www.google.com/">Czechitas</a>
</div>
<div class="col-12 col-sm-6 col-md footer__contact">
    <h4>@lang('app.footer.contact')</h4>
    <p>
        <strong>Czechitas</strong><br>
        Dlouhá 123<br>
        123 45 Horní Dolní
    </p>
    <a href="https://www.czechitas.cz/">www.czechitas.cz</a>
</div>
