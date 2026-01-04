<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            General Settings
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">General Settings</h3>
                        </div>
                        
                        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="site_name" class="form-label">Site Name *</label>
                                            <input type="text" class="form-control @error('site_name') is-invalid @enderror" 
                                                   id="site_name" name="site_name" 
                                                   value="{{ old('site_name', $siteName ?? 'My E-commerce') }}" 
                                                   required>
                                            @error('site_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="site_email" class="form-label">Site Email *</label>
                                            <input type="email" class="form-control @error('site_email') is-invalid @enderror" 
                                                   id="site_email" name="site_email" 
                                                   value="{{ old('site_email', $siteEmail ?? 'admin@example.com') }}" 
                                                   required>
                                            @error('site_email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="site_phone" class="form-label">Site Phone</label>
                                            <input type="text" class="form-control @error('site_phone') is-invalid @enderror" 
                                                   id="site_phone" name="site_phone" 
                                                   value="{{ old('site_phone', $sitePhone ?? '') }}">
                                            @error('site_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="site_address" class="form-label">Site Address</label>
                                            <input type="text" class="form-control @error('site_address') is-invalid @enderror" 
                                                   id="site_address" name="site_address" 
                                                   value="{{ old('site_address', $siteAddress ?? '') }}">
                                            @error('site_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="site_logo" class="form-label">Site Logo</label>
                                            @if(!empty($siteLogo))
                                                <div class="mb-2">
                                                    <img src="{{ asset('storage/' . $siteLogo) }}" 
                                                         alt="Logo" style="max-height: 100px;">
                                                </div>
                                            @endif
                                            <input type="file" class="form-control @error('site_logo') is-invalid @enderror" 
                                                   id="site_logo" name="site_logo" accept="image/*">
                                            <small class="text-muted">Recommended size: 200x60px</small>
                                            @error('site_logo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="site_favicon" class="form-label">Site Favicon</label>
                                            @if(!empty($siteFavicon))
                                                <div class="mb-2">
                                                    <img src="{{ asset('storage/' . $siteFavicon) }}" 
                                                         alt="Favicon" style="max-height: 32px;">
                                                </div>
                                            @endif
                                            <input type="file" class="form-control @error('site_favicon') is-invalid @enderror" 
                                                   id="site_favicon" name="site_favicon" accept="image/x-icon,image/png">
                                            <small class="text-muted">Recommended size: 32x32px (.ico or .png)</small>
                                            @error('site_favicon')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>