<!-- Vendor Header -->
<header class="bg-surface border-bottom border-default shadow-sm py-3">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <!-- Mobile sidebar toggle (visible only on small screens) -->
                <button id="sidebar-toggle" class="btn btn-outline-secondary me-3 rounded-circle d-md-none" type="button" style="width: 40px; height: 40px;">
                    <i class="fas fa-bars"></i>
                </button>
                <!-- Desktop sidebar toggle (visible only on medium+ screens) -->
                <button id="desktop-sidebar-toggle" class="btn btn-outline-secondary me-3 rounded-circle" type="button" title="Toggle sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div>
                    <h1 class="h4 mb-0 fw-semibold">@yield('page-title', 'Dashboard')</h1>
                    <!-- Breadcrumbs -->
                    @if (isset($breadcrumbs) && is_array($breadcrumbs))
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 small">
                                <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Home</a></li>
                                @foreach ($breadcrumbs as $label => $url)
                                    @if (is_string($label) && $url)
                                        <li class="breadcrumb-item"><a href="{{ $url }}">{{ $label }}</a></li>
                                    @else
                                        <li class="breadcrumb-item active" aria-current="page">{{ $url }}</li>
                                    @endif
                                @endforeach
                            </ol>
                        </nav>
                    @endif
                </div>
            </div>
            
            <div class="d-flex align-items-center">
                <!-- Notifications -->
                <div class="dropdown me-2">
                    <button class="btn btn-outline-secondary position-relative rounded-circle" type="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-count">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2 notification-dropdown" aria-labelledby="notificationsDropdown">
                        <li><h6 class="dropdown-header fw-semibold">Notifications</h6></li>
                        
                        @forelse(auth()->user()->notifications->take(5) as $notification)
                            <li>
                                <a class="dropdown-item d-flex align-items-start py-2 notification-item" href="#">
                                    <div class="rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="fas fa-bell text-info"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $notification->title ?? 'Notification' }}</div>
                                        <small class="text-secondary">{{ $notification->message ?? '' }}</small>
                                        <br>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li>
                                <a class="dropdown-item d-flex align-items-center py-2" href="#">
                                    <div class="text-center w-100">
                                        <div class="fw-medium">No notifications</div>
                                        <small class="text-secondary">You're all caught up</small>
                                    </div>
                                </a>
                            </li>
                        @endforelse
                        
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center fw-medium" href="#">Mark all as read</a></li>
                    </ul>
                </div>
                
                <!-- User Profile -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center py-1 px-2" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img class="rounded-circle me-2" src="{{ Auth::user()->avatar ? asset('storage/avatars/' . Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=random' }}" alt="{{ Auth::user()->name }}" width="32" height="32">
                        <div class="d-none d-md-block text-start">
                            <div class="fw-medium small mb-0">{{ Auth::user()->name }}</div>
                            <small>Vendor</small>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2" aria-labelledby="userDropdown">
                        <li><h6 class="dropdown-header fw-semibold">{{ Auth::user()->name }}</h6></li>
                        <li><a class="dropdown-item" href="{{ route('vendor.profile.index') }}"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('vendor.profile.store') }}"><i class="fas fa-cog me-2"></i>Store Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('vendor.logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>
