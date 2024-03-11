<nav class="navbar navbar-light navbar-expand-md top-menu">
    <a href="{{ route('home') }}" class="navbar-brand align-self-start">
        <img class="logo" src="{{ asset('img/logo.png') }}" alt="@lang('app.menu.home')">
    </a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="@lang('app.show_menu')">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse menu_content" id="navbarSupportedContent">
        <div class="navbar-nav">
            <a class="nav-item nav-link {{ Route::is('home') ? 'active' : null }}" href="{{ route('home') }}">
                @lang('app.menu.home')
            </a>
            @if (Auth::user() !== null)
                @if (Auth::user()->isRoleParent())
                    <a class="nav-item nav-link {{ Route::is('students.*') ? 'active' : null }}" href="{{ route('students.index') }}">
                        @lang('app.menu.students')
                    </a>
                @endif
            @endif
            {{-- @if (Auth::user() == null || Auth::user()->isRoleParent()) --}}
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ Route::is('static.parents') ? 'active' : null }}" href="{{ route('static.parents') }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        @lang('app.menu.parents')
                    </a>
                    <div class="dropdown-menu submenu">
                        <a class="dropdown-item {{ Route::is('static.parents') ? 'active' : null }}" href="{{ route('static.parents') }}">@lang('app.menu.parents_tuts')</a>
                        <a class="dropdown-item" href="{{ route('students.create') }}">@lang('app.menu.application')</a>
                    </div>
                </div>
            {{-- @endif --}}
            {{-- @if (Auth::user() == null) --}}
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle{{ Route::is(['static.teachers', 'orders.create']) ? ' active' : null }}" href="{{ route('static.teachers') }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        @lang('app.menu.teachers')
                    </a>
                    <div class="dropdown-menu submenu">
                        <a class="dropdown-item {{ Route::is('static.teachers') ? 'active' : null }}" href="{{ route('static.teachers') }}">@lang('app.menu.teachers_tuts')</a>
                        <a class="dropdown-item {{ Route::is('orders.create') ? 'active' : null }}" href="{{ route('orders.create') }}">@lang('app.menu.teachers_order')</a>
                    </div>
                </div>
            {{-- @endif --}}
            <a class="nav-item nav-link {{ Route::is('static.contact') ? 'active' : null }}" href="{{ route('static.contact') }}">
                @lang('app.menu.contact')
            </a>
        </div>

        <!-- Right Side Of Navbar -->
        <div class="nav navbar-nav navbar-right ml-auto">
            @if (Auth::guest())
                <a class="nav-item nav-link {{ Route::is('login') ? 'active' : null }}" href="{{ route('login') }}">
                    <i class="fa fa-user mr-2"></i>@lang('app.menu.login')
                </a>
            @else
                <div class="nav-item dropdown">
                    <span>@lang('app.menu.logged_in')</span>
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" title="{{ Auth::user()->name }}">
                        <strong>{{ Str::limit(Auth::user()->name, 20) }}</strong>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right submenu" role="menu">
                        <a href="{{ route('profile') }}" class="dropdown-item {{ Route::is('profile') ? 'active' : null }}">
                            @lang('app.menu.profile')
                        </a>
                        <a class="dropdown-item" href="{{ route('logout') }}" id="logout-link">
                            @lang('app.menu.logout')
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            {{ csrf_field() }}
                        </form>
                    </div>
                </div>
            @endif
        </div>

    </div>

</nav>
