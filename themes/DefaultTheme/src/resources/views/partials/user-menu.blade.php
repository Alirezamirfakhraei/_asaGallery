<!--start .author-author__info-->
@if(auth()->check() || auth('sub_client')->check())
    <ul class="nav float-left">
        @include('front::partials.language')
        <li class="nav-item account dropdown">
            <a class="nav-link" href="#" data-toggle="dropdown" aria-haspopup="true"
               aria-expanded="false">
                <span class="label-dropdown">{{ trans('front::messages.header.account') }}</span>
                <i class="mdi mdi-account-circle-outline"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-sm dropdown-menu-left">

                @if(auth()->check() && (auth()->user()->level == 'admin' || auth()->user()->level == 'creator'))
                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                        {{ trans('front::messages.header.control-panel') }}
                    </a>
                @endif


                <div class="dropdown-divider" role="presentation"></div>

                @if(auth()->check() && auth('sub_client')->check())
                        <a class="dropdown-item" href="{{ route('front.user.profile') }}">
                            <i class="mdi mdi-account-card-details-outline"></i>{{ trans('front::messages.header.profile') }}
                        </a>
                        <a class="dropdown-item" href="{{ route('front.orders.index') }}">
                            <i class="mdi mdi-account-edit-outline"></i>{{ trans('front::messages.header.my-orders') }}

                        </a>
                        <div class="dropdown-divider" role="presentation"></div>
                        <a class="dropdown-item" href="{{ route('logout') }}">
                        <i class="mdi mdi-logout-variant"></i>
                        {{ trans('front::messages.header.exit') }}
                    </a>
                @elseif(auth('sub_client')->check())
                    <form action="{{ route('client.logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="mdi mdi-logout-variant"></i>
                            خروج
                        </button>
                    </form>
                @endif
            </div>
        </li>
    </ul>
@else
    <ul class="nav float-left">
        @include('front::partials.language')

        <li class="nav-item account dropdown">
            <a class="nav-link" href="#" data-toggle="dropdown" aria-haspopup="true"
               aria-expanded="false">
                <span class="label-dropdown">{{ trans('front::messages.header.account') }}</span>
                <i class="mdi mdi-account-circle-outline"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-sm dropdown-menu-left">
                <a class="dropdown-item" href="{{ route('login') }}">
                    <i class="mdi mdi-account-card-details-outline"></i>
                    {{ trans('front::messages.header.sign-in-to-site') }}
                </a>
                <a class="dropdown-item" href="{{ route('register') }}">
                    <i class="mdi mdi-account-edit-outline"></i>
                    {{ trans('front::messages.header.register') }}
                </a>
                <div class="dropdown-divider" role="presentation"></div>
                <a class="dropdown-item" href="{{ route('client.login') }}">
                    <i class="mdi mdi-account-group-outline"></i>
                    ورود همکاران
                </a>
            </div>
        </li>
    </ul>
@endif
<!--end /.author-author__info-->
