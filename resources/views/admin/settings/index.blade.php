@extends('admin.layouts.app')

@section('title', 'Settings - ' . config('app.name', 'Laravel'))

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Settings'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                            <h2 class="card-title mb-0 fw-semibold h5 h4-md">Settings</h2>
                            <form action="{{ route('admin.settings.reset') }}" method="POST" id="resetForm">
                                @csrf
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" id="resetButton">
                                    <i class="fas fa-sync-alt me-1"></i><span class="d-none d-sm-inline">Reset to Default</span><span class="d-sm-none">Reset</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Success!</strong> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Error!</strong> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
                            @csrf
                            @method('POST')
                            <input type="hidden" name="active_tab" id="activeTabInput" value="general">
                            
                            <!-- Tabs Navigation -->
                            <ul class="nav nav-tabs mb-4 flex-nowrap overflow-auto" id="settingsTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active text-nowrap" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                                        <i class="fas fa-cog me-1"></i><span class="d-none d-sm-inline">General</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link text-nowrap" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab">
                                        <i class="fas fa-hashtag me-1"></i><span class="d-none d-sm-inline">Social Media</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link text-nowrap" id="appearance-tab" data-bs-toggle="tab" data-bs-target="#appearance" type="button" role="tab">
                                        <i class="fas fa-palette me-1"></i><span class="d-none d-sm-inline">Appearance</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link text-nowrap" id="site-management-tab" data-bs-toggle="tab" data-bs-target="#site-management" type="button" role="tab">
                                        <i class="fas fa-server me-1"></i><span class="d-none d-sm-inline">Site</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link text-nowrap" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab">
                                        <i class="fas fa-credit-card me-1"></i><span class="d-none d-sm-inline">Payment</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link text-nowrap" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">
                                        <i class="fas fa-bell me-1"></i><span class="d-none d-sm-inline">Notifications</span>
                                    </button>
                                </li>
                                <!-- <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                                        <i class="fas fa-lock me-1"></i> Password
                                    </button>
                                </li> -->
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="application-tab" data-bs-toggle="tab" data-bs-target="#application" type="button" role="tab">
                                        <i class="fas fa-mobile-alt me-1"></i> Application Links
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="database-tab" data-bs-toggle="tab" data-bs-target="#database" type="button" role="tab">
                                        <i class="fas fa-database me-1"></i> Database Management
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="frontend-tab" data-bs-toggle="tab" data-bs-target="#frontend" type="button" role="tab">
                                        <i class="fas fa-window-maximize me-1"></i> Frontend Settings
                                    </button>
                                </li>
                            </ul>
                            <!-- Tab Content -->
                            <div class="tab-content" id="settingsTabsContent">
                                <!-- General Settings Tab -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel">
                                    <div class="row g-4">
                                        <!-- Site Title -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Site Title</label>
                                            <input type="text" name="site_title" value="{{ old('site_title', $setting->site_title) }}" class="form-control" placeholder="Enter site title">
                                        </div>
                                        
                                        <!-- Site Description -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Site Description</label>
                                            <textarea name="site_description" rows="3" class="form-control" placeholder="Enter site description">{{ old('site_description', $setting->site_description) }}</textarea>
                                        </div>
                                        
                                        <!-- Tagline -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Tagline</label>
                                            <input type="text" name="tagline" value="{{ old('tagline', $setting->tagline) }}" class="form-control" placeholder="Enter tagline">
                                            <div class="form-text">A short, memorable phrase that captures your brand essence</div>
                                        </div>
                                        
                                        <!-- Header Logo -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Header Logo</label>
                                            <input type="file" name="header_logo" class="form-control">
                                            @if($setting->header_logo)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/' . $setting->header_logo) }}" alt="Header Logo" class="img-fluid rounded" style="max-height: 80px;">
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="remove_header_logo" id="removeHeaderLogo">
                                                        <label class="form-check-label" for="removeHeaderLogo">
                                                            Remove header logo
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Footer Logo -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Footer Logo</label>
                                            <input type="file" name="footer_logo" class="form-control">
                                            @if($setting->footer_logo)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/' . $setting->footer_logo) }}" alt="Footer Logo" class="img-fluid rounded" style="max-height: 80px;">
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="remove_footer_logo" id="removeFooterLogo">
                                                        <label class="form-check-label" for="removeFooterLogo">
                                                            Remove footer logo
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Favicon -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Favicon</label>
                                            <input type="file" name="favicon" class="form-control">
                                            @if($setting->favicon)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/' . $setting->favicon) }}" alt="Favicon" class="img-fluid rounded" style="max-height: 50px;">
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="remove_favicon" id="removeFavicon">
                                                        <label class="form-check-label" for="removeFavicon">
                                                            Remove favicon
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Footer Text -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Footer Text</label>
                                            <textarea name="footer_text" rows="3" class="form-control" placeholder="Enter footer text">{{ old('footer_text', $setting->footer_text) }}</textarea>
                                        </div>
                                        
                                        <!-- Address -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Address</label>
                                            <textarea name="address" rows="3" class="form-control" placeholder="Enter company address">{{ old('address', $setting->address) }}</textarea>
                                        </div>
                                        
                                        <!-- GST Number -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">GST Number</label>
                                            <input type="text" name="gst_number" value="{{ old('gst_number', $setting->gst_number) }}" class="form-control" placeholder="Enter GST number">
                                        </div>
                                        
                                        <!-- Authorized Signatory -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Authorized Signatory</label>
                                            <input type="file" name="authorized_signatory" class="form-control">
                                            @if($setting->authorized_signatory)
                                                <div class="mt-2">
                                                    <a href="{{ asset('storage/' . $setting->authorized_signatory) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-file-download me-1"></i> View Current Signatory
                                                    </a>
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="remove_authorized_signatory" id="removeAuthorizedSignatory">
                                                        <label class="form-check-label" for="removeAuthorizedSignatory">
                                                            Remove authorized signatory
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Company Email -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Company Email</label>
                                            <input type="email" name="company_email" value="{{ old('company_email', $setting->company_email) }}" class="form-control" placeholder="Enter company email">
                                        </div>
                                        
                                        <!-- Company Phone -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Company Phone</label>
                                            <input type="number" name="company_phone" value="{{ old('company_phone', $setting->company_phone) }}" class="form-control" placeholder="Enter company phone">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Social Media Tab -->
                                <div class="tab-pane fade" id="social" role="tabpanel">
                                    <div class="row g-4">
                                        <!-- Facebook URL -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Facebook URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-surface border-default">
                                                    <i class="fab fa-facebook-f text-primary"></i>
                                                </span>
                                                <input type="url" name="facebook_url" value="{{ old('facebook_url', $setting->facebook_url) }}" class="form-control" placeholder="https://facebook.com/yourpage">
                                            </div>
                                        </div>
                                        
                                        <!-- Twitter URL -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Twitter URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-surface border-default">
                                                    <i class="fab fa-twitter text-info"></i>
                                                </span>
                                                <input type="url" name="twitter_url" value="{{ old('twitter_url', $setting->twitter_url) }}" class="form-control" placeholder="https://twitter.com/yourhandle">
                                            </div>
                                        </div>
                                        
                                        <!-- Instagram URL -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Instagram URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-surface border-default">
                                                    <i class="fab fa-instagram text-danger"></i>
                                                </span>
                                                <input type="url" name="instagram_url" value="{{ old('instagram_url', $setting->instagram_url) }}" class="form-control" placeholder="https://instagram.com/yourhandle">
                                            </div>
                                        </div>
                                        
                                        <!-- LinkedIn URL -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">LinkedIn URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-surface border-default">
                                                    <i class="fab fa-linkedin-in text-primary"></i>
                                                </span>
                                                <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $setting->linkedin_url) }}" class="form-control" placeholder="https://linkedin.com/company/yourcompany">
                                            </div>
                                        </div>
                                        
                                        <!-- YouTube URL -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">YouTube URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-surface border-default">
                                                    <i class="fab fa-youtube text-danger"></i>
                                                </span>
                                                <input type="url" name="youtube_url" value="{{ old('youtube_url', $setting->youtube_url) }}" class="form-control" placeholder="https://youtube.com/yourchannel">
                                            </div>
                                        </div>
                                        
                                        <!-- WhatsApp URL -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">WhatsApp URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-surface border-default">
                                                    <i class="fab fa-whatsapp text-success"></i>
                                                </span>
                                                <input type="url" name="whatsapp_url" value="{{ old('whatsapp_url', $setting->whatsapp_url) }}" class="form-control" placeholder="https://wa.me/yourphonenumber">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Appearance Tab -->
                                <div class="tab-pane fade" id="appearance" role="tabpanel">
                                    <div class="row g-4">
                                        <!-- Theme Color -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Primary Theme Color</label>
                                            <input type="color" name="theme_color" value="{{ old('theme_color', $setting->theme_color) }}" class="form-control form-control-color">
                                            <div class="form-text">This color will be used for primary buttons and links.</div>
                                        </div>
                                        
                                        <!-- Background Color -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Background Color</label>
                                            <input type="color" name="background_color" value="{{ old('background_color', $setting->background_color) }}" class="form-control form-control-color">
                                            <div class="form-text">This color will be used for the main background.</div>
                                        </div>
                                        
                                        <!-- Font Color -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Font Color</label>
                                            <input type="color" name="font_color" value="{{ old('font_color', $setting->font_color) }}" class="form-control form-control-color">
                                            <div class="form-text">This color will be used for the main text.</div>
                                        </div>
                                        
                                        <!-- Sidebar Text Color -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Sidebar Text Color</label>
                                            <input type="color" name="sidebar_text_color" value="{{ old('sidebar_text_color', $setting->sidebar_text_color) }}" class="form-control form-control-color">
                                            <div class="form-text">This color will be used for sidebar text.</div>
                                        </div>
                                        
                                        <!-- Heading Text Color -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Heading Text Color</label>
                                            <input type="color" name="heading_text_color" value="{{ old('heading_text_color', $setting->heading_text_color) }}" class="form-control form-control-color">
                                            <div class="form-text">This color will be used for headings (H1, H2, etc.).</div>
                                        </div>
                                        
                                        <!-- Label Text Color -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Label Text Color</label>
                                            <input type="color" name="label_text_color" value="{{ old('label_text_color', $setting->label_text_color) }}" class="form-control form-control-color">
                                            <div class="form-text">This color will be used for form labels.</div>
                                        </div>
                                        
                                        <!-- General Text Color -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">General Text Color</label>
                                            <input type="color" name="general_text_color" value="{{ old('general_text_color', $setting->general_text_color) }}" class="form-control form-control-color">
                                            <div class="form-text">This color will be used for general body text.</div>
                                        </div>
                                        
                                        <!-- Link Color -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Link Color</label>
                                            <input type="color" name="link_color" value="{{ old('link_color', $setting->link_color ?? '#333333') }}" class="form-control form-control-color">
                                            <div class="form-text">This color will be used for links.</div>
                                        </div>
                                        
                                        <!-- Link Hover Color -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Link Hover Color</label>
                                            <input type="color" name="link_hover_color" value="{{ old('link_hover_color', $setting->link_hover_color ?? '#FF6B00') }}" class="form-control form-control-color">
                                            <div class="form-text">This color will be used for links on hover.</div>
                                        </div>
                                        
                                        <!-- Font Style -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Font Style</label>
                                            <select class="form-select" name="font_style">
                                                <option value="Arial, sans-serif" {{ old('font_style', $setting->font_style ?? 'Arial, sans-serif') == 'Arial, sans-serif' ? 'selected' : '' }}>Arial</option>
                                                <option value="'Times New Roman', serif" {{ old('font_style', $setting->font_style ?? 'Arial, sans-serif') == "'Times New Roman', serif" ? 'selected' : '' }}>Times New Roman</option>
                                                <option value="'Courier New', monospace" {{ old('font_style', $setting->font_style ?? 'Arial, sans-serif') == "'Courier New', monospace" ? 'selected' : '' }}>Courier New</option>
                                                <option value="Georgia, serif" {{ old('font_style', $setting->font_style ?? 'Arial, sans-serif') == 'Georgia, serif' ? 'selected' : '' }}>Georgia</option>
                                                <option value="Verdana, sans-serif" {{ old('font_style', $setting->font_style ?? 'Arial, sans-serif') == 'Verdana, sans-serif' ? 'selected' : '' }}>Verdana</option>
                                            </select>
                                            <div class="form-text">Select the default font style for the website.</div>
                                        </div>
                                        
                                        <!-- Responsive Font Size Matrix -->
                                        <div class="col-12">
                                            <hr class="my-4">
                                            <h4 class="mb-4">Responsive Font Size Matrix</h4>
                                            
                                            @php
                                                $fontOptions = [
                                                    'Arial, sans-serif' => 'Arial',
                                                    'Times New Roman, serif' => 'Times New Roman',
                                                    'Courier New, monospace' => 'Courier New',
                                                    'Georgia, serif' => 'Georgia',
                                                    'Verdana, sans-serif' => 'Verdana',
                                                    'Roboto, sans-serif' => 'Roboto',
                                                    'Open Sans, sans-serif' => 'Open Sans',
                                                    'Lato, sans-serif' => 'Lato',
                                                    'Poppins, sans-serif' => 'Poppins',
                                                    'Montserrat, sans-serif' => 'Montserrat',
                                                ];
                                                $defaultFont = 'Arial, sans-serif';
                                            @endphp
                                            
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Element</th>
                                                            <th>Font Type</th>
                                                            <th>Desktop (px)</th>
                                                            <th>Tablet (px)</th>
                                                            <th>Mobile (px)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>H1</td>
                                                            <td>
                                                                <select name="h1_font_family" class="form-select form-select-sm">
                                                                    @foreach($fontOptions as $value => $label)
                                                                        <option value="{{ $value }}" {{ old('h1_font_family', $setting->h1_font_family ?? $defaultFont) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td><input type="number" name="desktop_h1_size" value="{{ old('desktop_h1_size', $setting->desktop_h1_size ?? 36) }}" class="form-control form-control-sm" min="1" placeholder="36"></td>
                                                            <td><input type="number" name="tablet_h1_size" value="{{ old('tablet_h1_size', $setting->tablet_h1_size ?? 32) }}" class="form-control form-control-sm" min="1" placeholder="32"></td>
                                                            <td><input type="number" name="mobile_h1_size" value="{{ old('mobile_h1_size', $setting->mobile_h1_size ?? 28) }}" class="form-control form-control-sm" min="1" placeholder="28"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>H2</td>
                                                            <td>
                                                                <select name="h2_font_family" class="form-select form-select-sm">
                                                                    @foreach($fontOptions as $value => $label)
                                                                        <option value="{{ $value }}" {{ old('h2_font_family', $setting->h2_font_family ?? $defaultFont) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td><input type="number" name="desktop_h2_size" value="{{ old('desktop_h2_size', $setting->desktop_h2_size ?? 30) }}" class="form-control form-control-sm" min="1" placeholder="30"></td>
                                                            <td><input type="number" name="tablet_h2_size" value="{{ old('tablet_h2_size', $setting->tablet_h2_size ?? 28) }}" class="form-control form-control-sm" min="1" placeholder="28"></td>
                                                            <td><input type="number" name="mobile_h2_size" value="{{ old('mobile_h2_size', $setting->mobile_h2_size ?? 24) }}" class="form-control form-control-sm" min="1" placeholder="24"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>H3</td>
                                                            <td>
                                                                <select name="h3_font_family" class="form-select form-select-sm">
                                                                    @foreach($fontOptions as $value => $label)
                                                                        <option value="{{ $value }}" {{ old('h3_font_family', $setting->h3_font_family ?? $defaultFont) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td><input type="number" name="desktop_h3_size" value="{{ old('desktop_h3_size', $setting->desktop_h3_size ?? 24) }}" class="form-control form-control-sm" min="1" placeholder="24"></td>
                                                            <td><input type="number" name="tablet_h3_size" value="{{ old('tablet_h3_size', $setting->tablet_h3_size ?? 22) }}" class="form-control form-control-sm" min="1" placeholder="22"></td>
                                                            <td><input type="number" name="mobile_h3_size" value="{{ old('mobile_h3_size', $setting->mobile_h3_size ?? 20) }}" class="form-control form-control-sm" min="1" placeholder="20"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>H4</td>
                                                            <td>
                                                                <select name="h4_font_family" class="form-select form-select-sm">
                                                                    @foreach($fontOptions as $value => $label)
                                                                        <option value="{{ $value }}" {{ old('h4_font_family', $setting->h4_font_family ?? $defaultFont) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td><input type="number" name="desktop_h4_size" value="{{ old('desktop_h4_size', $setting->desktop_h4_size ?? 20) }}" class="form-control form-control-sm" min="1" placeholder="20"></td>
                                                            <td><input type="number" name="tablet_h4_size" value="{{ old('tablet_h4_size', $setting->tablet_h4_size ?? 18) }}" class="form-control form-control-sm" min="1" placeholder="18"></td>
                                                            <td><input type="number" name="mobile_h4_size" value="{{ old('mobile_h4_size', $setting->mobile_h4_size ?? 16) }}" class="form-control form-control-sm" min="1" placeholder="16"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>H5</td>
                                                            <td>
                                                                <select name="h5_font_family" class="form-select form-select-sm">
                                                                    @foreach($fontOptions as $value => $label)
                                                                        <option value="{{ $value }}" {{ old('h5_font_family', $setting->h5_font_family ?? $defaultFont) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td><input type="number" name="desktop_h5_size" value="{{ old('desktop_h5_size', $setting->desktop_h5_size ?? 18) }}" class="form-control form-control-sm" min="1" placeholder="18"></td>
                                                            <td><input type="number" name="tablet_h5_size" value="{{ old('tablet_h5_size', $setting->tablet_h5_size ?? 16) }}" class="form-control form-control-sm" min="1" placeholder="16"></td>
                                                            <td><input type="number" name="mobile_h5_size" value="{{ old('mobile_h5_size', $setting->mobile_h5_size ?? 14) }}" class="form-control form-control-sm" min="1" placeholder="14"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>H6</td>
                                                            <td>
                                                                <select name="h6_font_family" class="form-select form-select-sm">
                                                                    @foreach($fontOptions as $value => $label)
                                                                        <option value="{{ $value }}" {{ old('h6_font_family', $setting->h6_font_family ?? $defaultFont) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td><input type="number" name="desktop_h6_size" value="{{ old('desktop_h6_size', $setting->desktop_h6_size ?? 16) }}" class="form-control form-control-sm" min="1" placeholder="16"></td>
                                                            <td><input type="number" name="tablet_h6_size" value="{{ old('tablet_h6_size', $setting->tablet_h6_size ?? 14) }}" class="form-control form-control-sm" min="1" placeholder="14"></td>
                                                            <td><input type="number" name="mobile_h6_size" value="{{ old('mobile_h6_size', $setting->mobile_h6_size ?? 12) }}" class="form-control form-control-sm" min="1" placeholder="12"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Body Text</td>
                                                            <td>
                                                                <select name="body_font_family" class="form-select form-select-sm">
                                                                    @foreach($fontOptions as $value => $label)
                                                                        <option value="{{ $value }}" {{ old('body_font_family', $setting->body_font_family ?? $defaultFont) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td><input type="number" name="desktop_body_size" value="{{ old('desktop_body_size', $setting->desktop_body_size ?? 16) }}" class="form-control form-control-sm" min="1" placeholder="16"></td>
                                                            <td><input type="number" name="tablet_body_size" value="{{ old('tablet_body_size', $setting->tablet_body_size ?? 14) }}" class="form-control form-control-sm" min="1" placeholder="14"></td>
                                                            <td><input type="number" name="mobile_body_size" value="{{ old('mobile_body_size', $setting->mobile_body_size ?? 12) }}" class="form-control form-control-sm" min="1" placeholder="12"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Site Management Tab -->
                                <div class="tab-pane fade" id="site-management" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <h4 class="mb-4">Maintenance Mode</h4>
                                            
                                            <div class="row g-4">
                                                <!-- Enable Maintenance Mode -->
                                                <div class="col-md-12">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenanceMode" {{ old('maintenance_mode', $setting->maintenance_mode) ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-medium" for="maintenanceMode">Enable Maintenance Mode</label>
                                                        <div class="form-text">When enabled, only admin users can access the site</div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Maintenance End Time -->
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Maintenance End Time (Optional)</label>
                                                    <input type="text" name="maintenance_end_time" value="{{ old('maintenance_end_time', $setting->maintenance_end_time ? \Carbon\Carbon::parse($setting->maintenance_end_time)->format('d/m/Y H:i') : '') }}" class="form-control" placeholder="dd/mm/yyyy, --:-- --">
                                                    <div class="form-text">Maintenance mode will auto-disable at this time</div>
                                                </div>
                                                
                                                <!-- Maintenance Message -->
                                                <div class="col-md-12">
                                                    <label class="form-label fw-medium">Maintenance Message</label>
                                                    <textarea name="maintenance_message" rows="3" class="form-control" placeholder="We are currently under maintenance. The website will be back online approximately at {end_time}.">{{ old('maintenance_message', $setting->maintenance_message ?? 'We are currently under maintenance. The website will be back online approximately at {end_time}.') }}</textarea>
                                                    <div class="form-text">Use {end_time} placeholder to show maintenance end time</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <hr class="my-4">
                                        
                                        <div class="col-12">
                                            <h4 class="mb-4">Coming Soon Mode</h4>
                                            
                                            <div class="row g-4">
                                                <!-- Enable Coming Soon Mode -->
                                                <div class="col-md-12">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="coming_soon_mode" id="comingSoonMode" {{ old('coming_soon_mode', $setting->coming_soon_mode) ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-medium" for="comingSoonMode">Enable Coming Soon Mode</label>
                                                        <div class="form-text">When enabled, shows a coming soon page to visitors</div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Launch Time -->
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Launch Time (Optional)</label>
                                                    <input type="text" name="launch_time" value="{{ old('launch_time', $setting->launch_time ? \Carbon\Carbon::parse($setting->launch_time)->format('d/m/Y H:i') : '') }}" class="form-control" placeholder="dd/mm/yyyy, --:-- --">
                                                    <div class="form-text">Coming soon mode will auto-disable at this time</div>
                                                </div>
                                                
                                                <!-- Coming Soon Message -->
                                                <div class="col-md-12">
                                                    <label class="form-label fw-medium">Coming Soon Message</label>
                                                    <textarea name="coming_soon_message" rows="3" class="form-control" placeholder="We're launching soon! Our amazing platform will be available at {launch_time}.">{{ old('coming_soon_message', $setting->coming_soon_message ?? "We're launching soon! Our amazing platform will be available at {launch_time}.") }}</textarea>
                                                    <div class="form-text">Use {launch_time} placeholder to show launch time</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Payment Settings Tab -->
                                <div class="tab-pane fade" id="payment" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <h4 class="mb-4">Payment Settings</h4>
                                            <p class="text-muted">Configure Razorpay integration and payment processing options.</p>
                                            
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>Setup Required</strong> - Please ensure you have your Razorpay credentials ready.
                                            </div>
                                            
                                            <div class="card border-0 shadow-sm mb-4">
                                                <div class="card-header bg-light">
                                                    <h5 class="card-title mb-0">Razorpay Configuration</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row g-4">
                                                        <!-- Razorpay Key ID -->
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-medium">Razorpay Key ID</label>
                                                            <input type="text" name="razorpay_key_id" value="{{ old('razorpay_key_id', $setting->razorpay_key_id) }}" class="form-control" placeholder="rzp_test_...">
                                                            <div class="form-text">Your Razorpay Key ID for test or live mode</div>
                                                        </div>
                                                        
                                                        <!-- Razorpay Key Secret -->
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-medium">Razorpay Key Secret</label>
                                                            <input type="password" name="razorpay_key_secret" value="{{ old('razorpay_key_secret', $setting->razorpay_key_secret) }}" class="form-control" placeholder="Enter secret key">
                                                            <div class="form-text">Your Razorpay Key Secret (keep this secure)</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Notification Settings Tab -->
                                <div class="tab-pane fade" id="notifications" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <h4 class="mb-4">Notification Settings</h4>
                                            <p class="text-muted">Configure Firebase Cloud Messaging for push notifications.</p>
                                            
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>Setup Required</strong> - Firebase Cloud Messaging Configuration
                                            </div>
                                            
                                            <div class="card border-0 shadow-sm mb-4">
                                                <div class="card-header bg-light">
                                                    <h5 class="card-title mb-0">Firebase Cloud Messaging Configuration</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row g-4">
                                                        <!-- Firebase Project ID -->
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-medium">Firebase Project ID</label>
                                                            <input type="text" name="firebase_project_id" value="{{ old('firebase_project_id', $setting->firebase_project_id) }}" class="form-control" placeholder="your-project-id">
                                                            <div class="form-text">Project ID from Firebase Console</div>
                                                        </div>
                                                        
                                                        <!-- Firebase Client Email -->
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-medium">Firebase Client Email</label>
                                                            <input type="email" name="firebase_client_email" value="{{ old('firebase_client_email', $setting->firebase_client_email) }}" class="form-control" placeholder="firebase-adminsdk-xxxxx@your-project.iam.gserviceaccount.com">
                                                            <div class="form-text">Service account email from Firebase</div>
                                                        </div>
                                                        
                                                        <!-- Firebase Private Key -->
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-medium">Firebase Private Key</label>
                                                            <textarea name="firebase_private_key" rows="6" class="form-control font-monospace" placeholder="-----BEGIN PRIVATE KEY-----&#10;...&#10;-----END PRIVATE KEY-----">{{ old('firebase_private_key', $setting->firebase_private_key) }}</textarea>
                                                            <div class="form-text">Private key from Firebase service account JSON file</div>
                                                        </div>
                                                        
                                                        <!-- Action Buttons -->
                                                        <div class="col-12">
                                                            <div class="d-flex gap-2">
                                                                <button type="button" class="btn btn-outline-primary" id="testFirebaseConfig">
                                                                    <i class="fas fa-vial me-1"></i> Test Configuration
                                                                </button>
                                                                <button type="button" class="btn btn-outline-secondary" id="viewFirebaseStats">
                                                                    <i class="fas fa-chart-bar me-1"></i> View Statistics
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Test Results Modal -->
                                            <div class="modal fade" id="firebaseTestModal" tabindex="-1" aria-labelledby="firebaseTestModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="firebaseTestModalLabel">Firebase Configuration Test</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div id="testResults">
                                                                <div class="text-center py-5">
                                                                    <div class="spinner-border text-primary" role="status">
                                                                        <span class="visually-hidden">Loading...</span>
                                                                    </div>
                                                                    <p class="mt-2">Testing configuration...</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Statistics Modal -->
                                            <div class="modal fade" id="firebaseStatsModal" tabindex="-1" aria-labelledby="firebaseStatsModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="firebaseStatsModalLabel">Firebase Notification Statistics</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div id="statsContent">
                                                                <div class="text-center py-5">
                                                                    <div class="spinner-border text-primary" role="status">
                                                                        <span class="visually-hidden">Loading...</span>
                                                                    </div>
                                                                    <p class="mt-2">Loading statistics...</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Password Tab -->
                                <div class="tab-pane fade" id="password" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <h4 class="mb-4">Change Password</h4>
                                            
                                            <div class="row g-4">
                                                <div class="col-md-12">
                                                    <label class="form-label fw-medium">Current Password</label>
                                                    <div class="input-group">
                                                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Enter current password" id="current_password">
                                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    @error('current_password')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                
                                                <div class="col-md-12">
                                                    <label class="form-label fw-medium">New Password</label>
                                                    <div class="input-group">
                                                        <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" placeholder="Enter new password" id="new_password">
                                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    @error('new_password')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                
                                                <div class="col-md-12">
                                                    <label class="form-label fw-medium">Confirm New Password</label>
                                                    <div class="input-group">
                                                        <input type="password" name="new_password_confirmation" class="form-control @error('new_password_confirmation') is-invalid @enderror" placeholder="Confirm new password" id="new_password_confirmation">
                                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password_confirmation">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    @error('new_password_confirmation')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                
                                                <div class="col-12">
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        Password must be at least 8 characters long.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Application Links Tab -->
                                <div class="tab-pane fade" id="application" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <h4 class="mb-4">Application Store Links</h4>
                                            <p class="text-muted">Configure links to your mobile applications in app stores.</p>
                                            
                                            <div class="row g-4">
                                                <!-- App Store Link -->
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">App Store Link</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-surface border-default">
                                                            <i class="fab fa-apple text-dark"></i>
                                                        </span>
                                                        <input type="url" name="app_store_link" value="{{ old('app_store_link', $setting->app_store_link) }}" class="form-control" placeholder="https://apps.apple.com/app/...">
                                                    </div>
                                                    <div class="form-text">Link to your application in the Apple App Store</div>
                                                </div>
                                                
                                                <!-- Play Store Link -->
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Play Store Link</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-surface border-default">
                                                            <i class="fab fa-google-play text-success"></i>
                                                        </span>
                                                        <input type="url" name="play_store_link" value="{{ old('play_store_link', $setting->play_store_link) }}" class="form-control" placeholder="https://play.google.com/store/apps/...">
                                                    </div>
                                                    <div class="form-text">Link to your application in the Google Play Store</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Database Management Tab -->
                                <div class="tab-pane fade" id="database" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <h4 class="mb-4">Database Operations</h4>
                                            
                                            <div class="row g-4">
                                                <!-- Clean Database Section -->
                                                <div class="col-md-6">
                                                    <div class="card border-0 shadow-sm h-100">
                                                        <div class="card-body">
                                                            <h5 class="card-title mb-3">
                                                                <i class="fas fa-broom text-danger me-2"></i>Clean Database
                                                            </h5>
                                                            <p class="card-text">
                                                                Remove all user data while preserving essential records.
                                                                This operation will permanently delete all user data, bookings, transactions, and notifications. 
                                                                Subscription plans, features, and settings will be preserved.
                                                            </p>
                                                            
                                                            <div class="alert alert-warning">
                                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                                <strong>Warning:</strong> This action cannot be undone!
                                                            </div>
                                                            
                                                            <button type="button" class="btn btn-danger w-100" data-action="clean-database" onclick="return confirm('Are you sure you want to clean the database? This action cannot be undone!')">
                                                                <i class="fas fa-trash-alt me-1"></i> Clean Database
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Export Database Section -->
                                                <div class="col-md-6">
                                                    <div class="card border-0 shadow-sm h-100">
                                                        <div class="card-body">
                                                            <h5 class="card-title mb-3">
                                                                <i class="fas fa-file-export text-primary me-2"></i>Export Full Database
                                                            </h5>
                                                            <p class="card-text">
                                                                Download a complete backup of your entire database. 
                                                                Export your full database to a downloadable SQL file. 
                                                                This backup includes both the database structure and all data.
                                                            </p>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label fw-medium">Export Format</label>
                                                                <select class="form-select" disabled>
                                                                    <option>SQL Dump (.sql)</option>
                                                                </select>
                                                                <div class="form-text">Complete SQL backup of your database</div>
                                                            </div>
                                                            
                                                            <button type="button" class="btn btn-theme w-100" data-action="export-database">
                                                                <i class="fas fa-download me-1"></i> Export Full Database
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Frontend Settings Tab -->
                                <div class="tab-pane fade" id="frontend" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <h4 class="mb-4">Frontend Access Permissions</h4>
                                            <p class="text-muted">Configure who can access your website/application.</p>
                                            
                                            <div class="card border-0 shadow-sm mb-4">
                                                <div class="card-body">
                                                    <div class="row g-4">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-medium">Website Access Permission</label>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="radio" name="frontend_access_permission" id="openForAll" value="open_for_all" {{ old('frontend_access_permission', $setting->frontend_access_permission ?? 'open_for_all') == 'open_for_all' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="openForAll">
                                                                    Open for all
                                                                    <div class="form-text">Anyone can view the website/application without restrictions</div>
                                                                </label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="radio" name="frontend_access_permission" id="registeredUsersOnly" value="registered_users_only" {{ old('frontend_access_permission', $setting->frontend_access_permission ?? 'open_for_all') == 'registered_users_only' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="registeredUsersOnly">
                                                                    Only registered users
                                                                    <div class="form-text">Only users with valid credentials can log in and access the site</div>
                                                                </label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="radio" name="frontend_access_permission" id="adminApprovalRequired" value="admin_approval_required" {{ old('frontend_access_permission', $setting->frontend_access_permission ?? 'open_for_all') == 'admin_approval_required' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="adminApprovalRequired">
                                                                    User registration requires admin approval
                                                                    <div class="form-text">Users can register but must be approved by an admin before they can log in</div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-medium">Pending Approval Message</label>
                                                            <textarea name="pending_approval_message" rows="3" class="form-control" placeholder="Your account is pending approval. Please wait for admin approval before accessing the site.">{{ old('pending_approval_message', $setting->pending_approval_message ?? 'Your account is pending approval. Please wait for admin approval before accessing the site.') }}</textarea>
                                                            <div class="form-text">Message shown to users whose accounts are pending admin approval</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="card border-0 shadow-sm mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title mb-4">Payment Method Visibility</h5>
                                                    <p class="text-muted">Configure which payment methods are visible to users on the checkout page.</p>
                                                    
                                                    <div class="row g-4">
                                                        <div class="col-md-12">
                                                            <div class="form-check form-switch mb-3">
                                                                <input class="form-check-input" type="checkbox" name="show_online_payment" id="showOnlinePayment" value="1" {{ old('show_online_payment', $setting->show_online_payment ?? true) ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-medium" for="showOnlinePayment">Show Online Payment Option</label>
                                                                <div class="form-text">When enabled, the "Online Payment" button will be visible on the checkout page</div>
                                                            </div>
                                                            
                                                            <div class="form-check form-switch mb-3">
                                                                <input class="form-check-input" type="checkbox" name="show_cod_payment" id="showCodPayment" value="1" {{ old('show_cod_payment', $setting->show_cod_payment ?? true) ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-medium" for="showCodPayment">Show Cash on Delivery (COD) Option</label>
                                                                <div class="form-text">When enabled, the "Cash on Delivery" button will be visible on the checkout page</div>
                                                            </div>
                                                            
                                                            <div class="form-check form-switch mb-3">
                                                                <input class="form-check-input" type="checkbox" name="show_invoice_payment" id="showInvoicePayment" value="1" {{ old('show_invoice_payment', $setting->show_invoice_payment ?? true) ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-medium" for="showInvoicePayment">Show Send Proforma Invoice Option</label>
                                                                <div class="form-text">When enabled, the "Send Proforma Invoice" button will be visible on the checkout page</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 d-flex justify-content-between">
                                <span></span>
                                <button type="submit" class="btn btn-theme rounded-pill px-4">
                                    <i class="fas fa-save me-1"></i> Save Settings
                                </button>
                            </div>
                        </form>
                        
                        <!-- Hidden forms for database management (outside the main form) -->
                        <div style="display: none;">
                            <form id="cleanDatabaseForm" action="{{ route('admin.settings.database.clean') }}" method="POST">
                                @csrf
                            </form>
                            <form id="exportDatabaseForm" action="{{ route('admin.settings.database.export') }}" method="POST">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>

<script>
    document.getElementById('resetButton').addEventListener('click', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to reset all settings to their default values? This action cannot be undone.')) {
            document.getElementById('resetForm').submit();
        }
    });
    
    // Add mutual exclusivity logic for site management modes
    document.addEventListener('DOMContentLoaded', function() {
        const maintenanceModeCheckbox = document.getElementById('maintenanceMode');
        const comingSoonModeCheckbox = document.getElementById('comingSoonMode');
        
        // When maintenance mode is enabled, disable coming soon mode
        maintenanceModeCheckbox.addEventListener('change', function() {
            if (this.checked) {
                comingSoonModeCheckbox.checked = false;
            }
        });
        
        // When coming soon mode is enabled, disable maintenance mode
        comingSoonModeCheckbox.addEventListener('change', function() {
            if (this.checked) {
                maintenanceModeCheckbox.checked = false;
            }
        });
        
        // Check for password-related errors or fragment
        const urlFragment = window.location.hash;
        const hasPasswordErrors = document.querySelectorAll('#password .is-invalid, #password .invalid-feedback').length > 0;
        
        if (urlFragment === '#password' || hasPasswordErrors) {
            // Switch to password tab using Bootstrap tab functionality
            const passwordTab = new bootstrap.Tab(document.getElementById('password-tab'));
            passwordTab.show();
            document.getElementById('activeTabInput').value = 'password';
        }
        
        // Handle tab switching
        const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabButtons.forEach(function(tab) {
            tab.addEventListener('shown.bs.tab', function(e) {
                const tabId = e.target.id.replace('-tab', '');
                document.getElementById('activeTabInput').value = tabId;
            });
        });
        
        // Restore active tab from session
        const activeTab = "{{ session('tab', '') }}";
        if (activeTab) {
            const tab = document.getElementById(activeTab + '-tab');
            if (tab) {
                const tabInstance = new bootstrap.Tab(tab);
                tabInstance.show();
                document.getElementById('activeTabInput').value = activeTab;
            }
        }
        
        // Add event listeners for database management buttons
        const cleanDatabaseButton = document.querySelector('[data-action="clean-database"]');
        const exportDatabaseButton = document.querySelector('[data-action="export-database"]');
        
        if (cleanDatabaseButton) {
            cleanDatabaseButton.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to clean the database? This action cannot be undone!')) {
                    document.getElementById('cleanDatabaseForm').submit();
                }
            });
        }
        
        if (exportDatabaseButton) {
            exportDatabaseButton.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('exportDatabaseForm').submit();
            });
        }
        
        // Password visibility toggle functionality
        const togglePasswordButtons = document.querySelectorAll('.toggle-password');
        togglePasswordButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    });
    
    // Firebase Configuration Test
    document.getElementById('testFirebaseConfig')?.addEventListener('click', function() {
        // Show the modal
        var testModal = new bootstrap.Modal(document.getElementById('firebaseTestModal'));
        testModal.show();
        
        // Make AJAX request to test Firebase configuration
        fetch('{{ route("admin.firebase.test") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            let resultHtml = `
                <div class="alert alert-${data.success ? 'success' : 'danger'}">
                    <h5><i class="fas ${data.success ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>${data.success ? 'Test Successful' : 'Test Failed'}</h5>
                    <p>${data.message}</p>
                </div>
            `;
            
            if (data.details) {
                resultHtml += `
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">Configuration Details</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Project ID
                                    <span class="badge bg-${data.details.project_id ? 'success' : 'danger'} rounded-pill">
                                        ${data.details.project_id ? 'Configured' : 'Missing'}
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Client Email
                                    <span class="badge bg-${data.details.client_email ? 'success' : 'danger'} rounded-pill">
                                        ${data.details.client_email ? 'Configured' : 'Missing'}
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Private Key
                                    <span class="badge bg-${data.details.private_key ? 'success' : 'danger'} rounded-pill">
                                        ${data.details.private_key ? 'Configured' : 'Missing'}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                `;
            }
            
            document.getElementById('testResults').innerHTML = resultHtml;
        })
        .catch(error => {
            document.getElementById('testResults').innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-circle me-2"></i>Error</h5>
                    <p>Failed to test configuration: ${error.message}</p>
                </div>
            `;
        });
    });
    
    // View Firebase Statistics
    document.getElementById('viewFirebaseStats')?.addEventListener('click', function() {
        // Show the modal
        var statsModal = new bootstrap.Modal(document.getElementById('firebaseStatsModal'));
        statsModal.show();
        
        // Make AJAX request to get Firebase statistics
        fetch('{{ route("admin.firebase.stats") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            let statsHtml = `
                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="card-title">${data.total_sent || 0}</h3>
                                <p class="card-text">Notifications Sent</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="card-title">${data.total_delivered || 0}</h3>
                                <p class="card-text">Delivered</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="card-title">${data.total_failed || 0}</h3>
                                <p class="card-text">Failed</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">Recent Activity</h6>
                    </div>
                    <div class="card-body">
                        ${data.recent_activity && data.recent_activity.length > 0 ? 
                            `<div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.recent_activity.map(activity => `
                                            <tr>
                                                <td>${activity.date}</td>
                                                <td>${activity.type}</td>
                                                <td>
                                                    <span class="badge bg-${activity.status === 'delivered' ? 'success' : activity.status === 'failed' ? 'danger' : 'warning'}">
                                                        ${activity.status}
                                                    </span>
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>` : 
                            '<p class="text-muted text-center">No recent activity</p>'
                        }
                    </div>
                </div>
            `;
            
            document.getElementById('statsContent').innerHTML = statsHtml;
        })
        .catch(error => {
            document.getElementById('statsContent').innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-circle me-2"></i>Error</h5>
                    <p>Failed to load statistics: ${error.message}</p>
                </div>
            `;
        });
    });
</script>
@endsection