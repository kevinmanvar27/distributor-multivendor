<!-- Vendor Sidebar -->
<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-surface sidebar">
    <!-- Mobile close button -->
    <button type="button" class="sidebar-close" id="sidebar-close" aria-label="Close sidebar">
        <i class="fas fa-times"></i>
    </button>
    <div class="position-sticky pt-3 d-flex flex-column vh-100">
        <div class="px-3 pb-3 border-bottom border-default sidebar-header">
            <div class="d-flex align-items-center mb-3">
                @if(Auth::user()->vendor && Auth::user()->vendor->store_logo)
                    <img src="{{ asset('storage/vendor/' . Auth::user()->vendor->store_logo) }}" alt="{{ Auth::user()->vendor->store_name ?? 'Vendor' }}" class="me-2 rounded sidebar-logo" height="48">
                @elseif(setting('header_logo'))
                    <img src="{{ asset('storage/' . setting('header_logo')) }}" alt="{{ setting('site_title', 'Vendor Panel') }}" class="me-2 rounded sidebar-logo" height="48">
                @else
                    <div class="bg-theme rounded-circle d-flex align-items-center justify-content-center me-2 sidebar-logo-icon" style="width: 48px; height: 48px;">
                        <i class="fas fa-store text-white"></i>
                    </div>
                @endif
                <h1 class="h5 mb-0 fw-bold sidebar-header-text">{{ Auth::user()->vendor->store_name ?? 'Vendor Panel' }}</h1>
            </div>
        </div>
        
        <ul class="nav flex-column flex-grow-1">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}" href="{{ route('vendor.dashboard') }}" data-title="Dashboard">
                    <i class="fas fa-home me-3"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            
            <!-- Products Section -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.products*') ? 'active' : '' }}" href="{{ route('vendor.products.index') }}" data-title="Products">
                    <i class="fas fa-box me-3"></i>
                    <span class="sidebar-text">Products</span>
                </a>
            </li>
            
            <!-- Categories Section -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.categories*') ? 'active' : '' }}" href="{{ route('vendor.categories.index') }}" data-title="Categories">
                    <i class="fas fa-tags me-3"></i>
                    <span class="sidebar-text">Categories</span>
                </a>
            </li>
            
            <!-- Media Library -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.media*') ? 'active' : '' }}" href="{{ route('vendor.media.index') }}" data-title="Media Library">
                    <i class="fas fa-images me-3"></i>
                    <span class="sidebar-text">Media Library</span>
                </a>
            </li>
            
            <!-- Orders Section -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.orders*') ? 'active' : '' }}" href="{{ route('vendor.orders.index') }}" data-title="Orders">
                    <i class="fas fa-shopping-cart me-3"></i>
                    <span class="sidebar-text">Orders</span>
                </a>
            </li>
            
            <!-- Coupons -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.coupons*') ? 'active' : '' }}" href="{{ route('vendor.coupons.index') }}" data-title="Coupons">
                    <i class="fas fa-ticket-alt me-3"></i>
                    <span class="sidebar-text">Coupons</span>
                </a>
            </li>
            
            <!-- Leads -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.leads*') ? 'active' : '' }}" href="{{ route('vendor.leads.index') }}" data-title="Leads">
                    <i class="fas fa-user-plus me-3"></i>
                    <span class="sidebar-text">Leads</span>
                </a>
            </li>
            
            <!-- Reports & Analytics -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.reports*') ? 'active' : '' }}" href="{{ route('vendor.reports.index') }}" data-title="Reports">
                    <i class="fas fa-chart-bar me-3"></i>
                    <span class="sidebar-text">Reports</span>
                </a>
            </li>
            
            <!-- Product Analytics -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.analytics*') ? 'active' : '' }}" href="{{ route('vendor.analytics.products') }}" data-title="Product Analytics">
                    <i class="fas fa-chart-line me-3"></i>
                    <span class="sidebar-text">Product Analytics</span>
                </a>
            </li>
            
            <!-- Salary Management -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.salary*') ? 'active' : '' }}" href="{{ route('vendor.salary.index') }}" data-title="Salary">
                    <i class="fas fa-money-bill-wave me-3"></i>
                    <span class="sidebar-text">Salary</span>
                </a>
            </li>
            
            <!-- Attendance Management -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.attendance*') ? 'active' : '' }}" href="{{ route('vendor.attendance.index') }}" data-title="Attendance">
                    <i class="fas fa-calendar-check me-3"></i>
                    <span class="sidebar-text">Attendance</span>
                </a>
            </li>
            
            <!-- Staff Management -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.staff*') ? 'active' : '' }}" href="{{ route('vendor.staff.index') }}" data-title="Staff">
                    <i class="fas fa-users me-3"></i>
                    <span class="sidebar-text">Staff</span>
                </a>
            </li>
            
            <!-- Invoices -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.invoices*') ? 'active' : '' }}" href="{{ route('vendor.invoices.index') }}" data-title="Invoices">
                    <i class="fas fa-file-invoice me-3"></i>
                    <span class="sidebar-text">Invoices</span>
                </a>
            </li>
            
            <!-- Pending Bills -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.pending-bills*') ? 'active' : '' }}" href="{{ route('vendor.pending-bills.index') }}" data-title="Pending Bills">
                    <i class="fas fa-file-invoice-dollar me-3"></i>
                    <span class="sidebar-text">Pending Bills</span>
                </a>
            </li>
            
            <!-- Store Profile -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.profile.index') ? 'active' : '' }}" href="{{ route('vendor.profile.index') }}" data-title="Profile">
                    <i class="fas fa-user me-3"></i>
                    <span class="sidebar-text">Profile</span>
                </a>
            </li>
            
            <!-- Store Settings -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.profile.store') ? 'active' : '' }}" href="{{ route('vendor.profile.store') }}" data-title="Store Settings">
                    <i class="fas fa-store me-3"></i>
                    <span class="sidebar-text">Store Settings</span>
                </a>
            </li>
        </ul>
        
        <div class="px-3 py-3 border-top border-default mt-auto sidebar-footer">
            <div class="d-flex align-items-center mb-3 sidebar-status">
                @php
                    $vendorStatus = Auth::user()->vendor->status ?? 'pending';
                    $statusColor = match($vendorStatus) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'suspended' => 'danger',
                        'rejected' => 'danger',
                        default => 'secondary'
                    };
                @endphp
                <div class="bg-{{ $statusColor }} rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                <div class="small sidebar-text">
                    <span class="text-secondary">Store {{ ucfirst($vendorStatus) }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('vendor.logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100 rounded py-2" data-title="Logout">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    <span class="sidebar-text">Logout</span>
                </button>
            </form>
        </div>
    </div>
</nav>
