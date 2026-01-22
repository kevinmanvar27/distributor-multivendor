<!-- Sidebar -->
<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-surface sidebar">
    <!-- Mobile close button -->
    <button type="button" class="sidebar-close" id="sidebar-close" aria-label="Close sidebar">
        <i class="fas fa-times"></i>
    </button>
    <div class="position-sticky pt-3 d-flex flex-column vh-100">
        <div class="px-3 pb-3 border-bottom border-default sidebar-header">
            <div class="d-flex align-items-center mb-3">
                @if(setting('header_logo'))
                    <img src="{{ asset('storage/' . setting('header_logo')) }}" alt="{{ setting('site_title', 'Admin Panel') }}" class="me-2 rounded sidebar-logo" height="48">
                @else
                    <div class="bg-theme rounded-circle d-flex align-items-center justify-content-center me-2 sidebar-logo-icon" style="width: 48px; height: 48px;">
                        <i class="fas fa-cube text-white"></i>
                    </div>
                    <h1 class="h5 mb-0 fw-bold sidebar-header-text">{{ setting('site_title', 'Admin Panel') }}</h1>
                @endif
            </div>
        </div>
        
        <ul class="nav flex-column flex-grow-1">
            <!-- 1. Dashboard -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" data-title="Dashboard">
                    <i class="fas fa-home me-3"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            
            <!-- 2. Products Section -->
            @if(auth()->user()->hasPermission('viewAny_product') || 
                auth()->user()->hasPermission('create_product') || 
                auth()->user()->hasPermission('update_product') || 
                auth()->user()->hasPermission('delete_product'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.products*') && !request()->routeIs('admin.attributes*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}" data-title="Products">
                        <i class="fas fa-box me-3"></i>
                        <span class="sidebar-text">Products</span>
                    </a>
                </li>
            @endif
            
            <!-- 3. Product Attributes Section -->
            @if(auth()->user()->hasPermission('viewAny_product') || 
                auth()->user()->hasPermission('create_product') || 
                auth()->user()->hasPermission('update_product') || 
                auth()->user()->hasPermission('delete_product'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.attributes*') ? 'active' : '' }}" href="{{ route('admin.attributes.index') }}" data-title="Product Attributes">
                        <i class="fas fa-sliders-h me-3"></i>
                        <span class="sidebar-text">Product Attributes</span>
                    </a>
                </li>
            @endif
            
            <!-- 3.5 Product Analytics Section -->
            @if(auth()->user()->hasPermission('viewAny_product') || 
                auth()->user()->hasPermission('create_product') || 
                auth()->user()->hasPermission('update_product') || 
                auth()->user()->hasPermission('delete_product') ||
                auth()->user()->isSuperAdmin())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.analytics.products*') ? 'active' : '' }}" href="{{ route('admin.analytics.products') }}" data-title="Product Analytics">
                        <i class="fas fa-chart-line me-3"></i>
                        <span class="sidebar-text">Product Analytics</span>
                    </a>
                </li>
            @endif
            
            <!-- 4. Category Section -->
            @if(auth()->user()->hasPermission('viewAny_category') || 
                auth()->user()->hasPermission('create_category') || 
                auth()->user()->hasPermission('update_category') || 
                auth()->user()->hasPermission('delete_category'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}" data-title="Category">
                        <i class="fas fa-tags me-3"></i>
                        <span class="sidebar-text">Category</span>
                    </a>
                </li>
            @endif
            
            <!-- 5. Coupons Section -->
            @if(auth()->user()->hasPermission('viewAny_coupon'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.coupons*') ? 'active' : '' }}" href="{{ route('admin.coupons.index') }}" data-title="Coupons">
                    <i class="fas fa-ticket-alt me-3"></i>
                    <span class="sidebar-text">Coupons</span>
                </a>
            </li>
            @endif
            
            <!-- 5.5 Referrals Section -->
            @if(auth()->user()->hasPermission('viewAny_referral'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.referrals*') ? 'active' : '' }}" href="{{ route('admin.referrals.index') }}" data-title="Referrals">
                    <i class="fas fa-user-friends me-3"></i>
                    <span class="sidebar-text">Referrals</span>
                </a>
            </li>
            @endif
            
            <!-- 6. Invoice Section -->
            @if(auth()->user()->hasPermission('manage_proforma_invoices'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.proforma-invoice*') ? 'active' : '' }}" href="{{ route('admin.proforma-invoice.index') }}" data-title="Invoice">
                        <i class="fas fa-file-invoice me-3"></i>
                        <span class="sidebar-text">Invoice</span>
                    </a>
                </li>
            @endif

            <!-- 7. Pending Bills Section -->
            @if(auth()->user()->hasPermission('manage_pending_bills'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.pending-bills*') ? 'active' : '' }}" href="{{ route('admin.pending-bills.index') }}" data-title="Pending Bills">
                        <i class="fas fa-money-bill-wave me-3"></i>
                        <span class="sidebar-text">Pending Bills</span>
                    </a>
                </li>
            @endif
            
            <!-- 8. Users Section -->
            @php 
                $hasUserPermission = auth()->user()->hasPermission('show_user') ||
                                    auth()->user()->hasPermission('add_user') || 
                                    auth()->user()->hasPermission('edit_user') || 
                                    auth()->user()->hasPermission('delete_user'); 
            @endphp
            @if($hasUserPermission)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users.index') && !request()->routeIs('admin.users.staff*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}" data-title="Users">
                        <i class="fas fa-users me-3"></i>
                        <span class="sidebar-text">Users</span>
                    </a>
                </li>
            @endif
            
            <!-- 9. User Groups Section -->
            @if($hasUserPermission)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.user-groups*') ? 'active' : '' }}" href="{{ route('admin.user-groups.index') }}" data-title="User Groups">
                        <i class="fas fa-users-cog me-3"></i>
                        <span class="sidebar-text">User Groups</span>
                    </a>
                </li>
            @endif
            
            <!-- 10. Staff Section -->
            @php 
                $hasStaffPermission = auth()->user()->hasPermission('show_staff') ||
                                    auth()->user()->hasPermission('add_staff') || 
                                    auth()->user()->hasPermission('edit_staff') || 
                                    auth()->user()->hasPermission('delete_staff'); 
            @endphp
            @if($hasStaffPermission)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users.staff*') ? 'active' : '' }}" href="{{ route('admin.users.staff') }}" data-title="Staff">
                        <i class="fas fa-user-tie me-3"></i>
                        <span class="sidebar-text">Staff</span>
                    </a>
                </li>
            @endif
            
            <!-- 10.5 Vendor Management Section -->
            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('viewAny_vendor'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.vendors*') ? 'active' : '' }}" href="{{ route('admin.vendors.index') }}" data-title="Vendors">
                        <i class="fas fa-store me-3"></i>
                        <span class="sidebar-text">Vendors</span>
                    </a>
                </li>
            @endif
            
            <!-- 11. Attendance Section -->
            @php 
                $hasAttendancePermission = auth()->user()->hasPermission('viewAny_attendance') ||
                                    auth()->user()->hasPermission('create_attendance') || 
                                    auth()->user()->hasPermission('update_attendance') || 
                                    auth()->user()->hasPermission('delete_attendance') ||
                                    auth()->user()->isSuperAdmin(); 
            @endphp
            @if($hasAttendancePermission)
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.attendance*') ? 'active' : '' }}" href="{{ route('admin.attendance.index') }}" data-title="Attendance">
                    <i class="fas fa-calendar-check me-3"></i>
                    <span class="sidebar-text">Attendance</span>
                </a>
            </li>
            @endif

            <!-- 12. Salary Section -->
            @php 
                $hasSalaryPermission = auth()->user()->hasPermission('viewAny_salary') ||
                                    auth()->user()->hasPermission('create_salary') || 
                                    auth()->user()->hasPermission('update_salary') || 
                                    auth()->user()->hasPermission('delete_salary') ||
                                    auth()->user()->isSuperAdmin(); 
            @endphp
            @if($hasSalaryPermission)
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.salary*') ? 'active' : '' }}" href="{{ route('admin.salary.index') }}" data-title="Salary">
                    <i class="fas fa-wallet me-3"></i>
                    <span class="sidebar-text">Salary</span>
                </a>
            </li>
            @endif
            
            <!-- 13. Leads Section -->
            @if(auth()->user()->hasPermission('viewAny_lead') || 
                auth()->user()->hasPermission('create_lead') || 
                auth()->user()->hasPermission('update_lead') || 
                auth()->user()->hasPermission('delete_lead'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.leads*') ? 'active' : '' }}" href="{{ route('admin.leads.index') }}" data-title="Leads">
                        <i class="fas fa-bullseye me-3"></i>
                        <span class="sidebar-text">Leads</span>
                    </a>
                </li>
            @endif
            
            <!-- 14. Pages Section -->
            @if(auth()->user()->hasPermission('viewAny_page') || 
                auth()->user()->hasPermission('create_page') || 
                auth()->user()->hasPermission('update_page') || 
                auth()->user()->hasPermission('delete_page'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.pages*') ? 'active' : '' }}" href="{{ route('admin.pages.index') }}" data-title="Pages">
                        <i class="fas fa-file-alt me-3"></i>
                        <span class="sidebar-text">Pages</span>
                    </a>
                </li>
            @endif
            
            <!-- 15. Media Library -->
            @if(auth()->user()->hasPermission('viewAny_media'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.media*') ? 'active' : '' }}" href="{{ route('admin.media.index') }}" data-title="Media Library">
                        <i class="fas fa-photo-video me-3"></i>
                        <span class="sidebar-text">Media Library</span>
                    </a>
                </li>
            @endif
            
            <!-- 16. Notifications -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.firebase.notifications*') ? 'active' : '' }}" href="{{ route('admin.firebase.notifications') }}" data-title="Notifications">
                    <i class="fas fa-bell me-3"></i>
                    <span class="sidebar-text">Notifications</span>
                </a>
            </li>
            
            <!-- 17. User Role and Permission Section -->
            @if(auth()->user()->hasPermission('manage_roles'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.roles*') || request()->routeIs('admin.permissions*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}" data-title="User Role & Permission">
                        <i class="fas fa-user-shield me-3"></i>
                        <span class="sidebar-text">User Role & Permission</span>
                    </a>
                </li>
            @endif
            
            <!-- 18. Settings (Last) -->
            @if(auth()->user()->hasPermission('manage_settings'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" href="{{ route('admin.settings') }}" data-title="Settings">
                        <i class="fas fa-cog me-3"></i>
                        <span class="sidebar-text">Settings</span>
                    </a>
                </li>
            @endif

            </ul>
            
            <div class="px-3 py-3 border-top border-default mt-auto sidebar-footer">
                <div class="d-flex align-items-center mb-3 sidebar-status">
                    <div class="bg-success rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                    <div class="small sidebar-text">
                        <span class="text-secondary">System Online</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100 rounded py-2" data-title="Logout">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        <span class="sidebar-text">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>