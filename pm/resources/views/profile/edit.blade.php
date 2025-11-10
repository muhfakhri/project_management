@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-user-edit me-2"></i>Edit Profile</h1>
                <a href="{{ route('profile.show', auth()->id()) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-eye me-1"></i>View Public Profile
                </a>
            </div>

            <div class="row">
                <!-- Profile Information -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="username" class="form-label">
                                        Username <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control @error('username') is-invalid @enderror" 
                                        id="username" 
                                        name="username" 
                                        value="{{ old('username', $user->username) }}" 
                                        required
                                    >
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        Email <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        type="email" 
                                        class="form-control @error('email') is-invalid @enderror" 
                                        id="email" 
                                        name="email" 
                                        value="{{ old('email', $user->email) }}" 
                                        required
                                    >
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input 
                                        type="text" 
                                        class="form-control @error('full_name') is-invalid @enderror" 
                                        id="full_name" 
                                        name="full_name" 
                                        value="{{ old('full_name', $user->full_name) }}"
                                    >
                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input 
                                        type="text" 
                                        class="form-control @error('phone') is-invalid @enderror" 
                                        id="phone" 
                                        name="phone" 
                                        value="{{ old('phone', $user->phone) }}"
                                    >
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="bio" class="form-label">Bio</label>
                                    <textarea 
                                        class="form-control @error('bio') is-invalid @enderror" 
                                        id="bio" 
                                        name="bio" 
                                        rows="4"
                                        placeholder="Tell us about yourself..."
                                    >{{ old('bio', $user->bio) }}</textarea>
                                    @error('bio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Maximum 500 characters</small>
                                </div>

                                <div class="mb-3">
                                    <label for="profile_picture" class="form-label">Profile Picture</label>
                                    <input 
                                        type="file" 
                                        class="form-control @error('profile_picture') is-invalid @enderror" 
                                        id="profile_picture" 
                                        name="profile_picture"
                                        accept="image/*"
                                    >
                                    @error('profile_picture')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Maximum 2MB. Supported formats: JPEG, PNG, JPG, GIF</small>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Current Profile Picture -->
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            @if($user->profile_picture)
                                <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                     alt="Profile Picture" 
                                     class="rounded-circle mb-3"
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3"
                                     style="width: 150px; height: 150px; font-size: 3rem;">
                                    {{ strtoupper(substr($user->username, 0, 1)) }}
                                </div>
                            @endif
                            <h5>{{ $user->full_name ?? $user->username }}</h5>
                            <p class="text-muted">{{ '@' . $user->username }}</p>
                        </div>
                    </div>

                    <!-- Account Info -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Account Info</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted">Member Since</small>
                                <div>{{ $user->created_at->format('M d, Y') }}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Last Updated</small>
                                <div>{{ $user->updated_at->diffForHumans() }}</div>
                            </div>
                            <div>
                                <small class="text-muted">Account Status</small>
                                <div>
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password Section -->
            <div class="row mt-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('profile.updatePassword') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="current_password" class="form-label">
                                        Current Password <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        type="password" 
                                        class="form-control @error('current_password') is-invalid @enderror" 
                                        id="current_password" 
                                        name="current_password" 
                                        required
                                    >
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">
                                        New Password <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        type="password" 
                                        class="form-control @error('new_password') is-invalid @enderror" 
                                        id="new_password" 
                                        name="new_password" 
                                        required
                                    >
                                    @error('new_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Minimum 8 characters</small>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password_confirmation" class="form-label">
                                        Confirm New Password <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        id="new_password_confirmation" 
                                        name="new_password_confirmation" 
                                        required
                                    >
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Password Requirements:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Minimum 8 characters</li>
                                        <li>Mix of uppercase and lowercase letters recommended</li>
                                        <li>Include numbers and special characters for better security</li>
                                    </ul>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-key me-1"></i>Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview profile picture before upload
document.getElementById('profile_picture')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // You could add image preview here if needed
            console.log('File selected:', file.name);
        }
        reader.readAsDataURL(file);
    }
});
</script>

@endsection
