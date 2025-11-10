<div class="sidebar bg-dark text-white p-3" style="width: 280px; height: 100vh; position: fixed; top: 0; left: 0; z-index: 1030;">
    @php
        $user = auth()->user();
        $sidebarTitle = 'PM System';
        $rolebadge = '<span class="badge bg-secondary ms-2">User</span>';

        if ($user) {
            // Determine user's highest role
            $userRoles = \App\Models\ProjectMember::where('user_id', $user->user_id)
                ->pluck('role')
                ->unique();
            
            if ($userRoles->contains('Project Admin')) {
                $sidebarTitle = 'Admin Panel';
                $rolebadge = '<span class="badge bg-danger ms-2">Admin</span>';
            } elseif ($userRoles->contains('Team Lead')) {
                $sidebarTitle = 'Team Lead Panel';
                $rolebadge = '<span class="badge bg-warning ms-2">Lead</span>';
            } elseif ($userRoles->contains('Developer')) {
                $sidebarTitle = 'Developer Panel';
                $rolebage = '<span class="badge bg-info ms-2">Dev</span>';
            } elseif ($userRoles->contains('Designer')) {
                $sidebarTitle = 'Designer Panel';
                $rolebage = '<span class="badge bg-purple ms-2">Design</span>';
            }
        }
    @endphp
    
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" style="width: 80px; height: 80px; object-fit: contain;" class="mb-2">
    </div>
    
    <ul class="nav flex-column">
        @if(!$user)
            <li class="nav-item mb-3">
                <div class="px-3">
                    <a href="{{ route('login') }}" class="btn btn-outline-light w-100 mb-2">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-light w-100">Sign up</a>
                </div>
            </li>
        @endif
        @foreach ($menuItems as $item)
            @php
                $isActive = request()->routeIs($item['route'] ?? '') 
                    || collect($item['children'] ?? [])->pluck('route')->contains(fn($r) => request()->routeIs($r));
            @endphp

            @if (!empty($item['children']))
                <li class="nav-item">
                    <a class="nav-link text-white d-flex justify-content-between {{ $isActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#menu-{{ \Illuminate\Support\Str::slug($item['label']) }}" role="button" aria-expanded="{{ $isActive ? 'true' : 'false' }}" aria-controls="menu-{{ \Illuminate\Support\Str::slug($item['label']) }}">
                        <span><i class="{{ $item['icon'] }}"></i> {{ $item['label'] }}</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse {{ $isActive ? 'show' : '' }}" id="menu-{{ \Illuminate\Support\Str::slug($item['label']) }}">
                        <ul class="nav flex-column ms-3">
                            @foreach ($item['children'] as $child)
                                <li>
                                    <a href="{{ route($child['route']) }}" class="nav-link text-white {{ request()->routeIs($child['route']) ? 'active fw-bold' : '' }}">
                                        {{ $child['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </li>
            @else
                <li>
                    <a href="{{ route($item['route']) }}" class="nav-link text-white {{ $isActive ? 'active fw-bold' : '' }}">
                        <i class="{{ $item['icon'] }}"></i> {{ $item['label'] }}
                        @if(isset($item['badge']) && $item['badge'] > 0)
                            <span class="badge {{ $item['badge_class'] ?? 'bg-primary' }} ms-2">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</div>
