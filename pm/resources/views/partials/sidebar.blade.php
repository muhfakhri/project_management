<div class="sidebar bg-dark text-white p-3" style="width: 280px; height: 100vh; position: fixed; top: 0; left: 0; z-index: 1030; overflow-y: auto;">
    @php
        $user = auth()->user();
        // Ensure $menuItems is always defined for the template to iterate safely.
        $menuItems = isset($menuItems) ? $menuItems : [];
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
        <div class="d-flex justify-content-center align-items-center mb-3">
            <i class="bi bi-kanban-fill" style="font-size: 3rem; color: #0d6efd;"></i>
        </div>
        <h5 class="mb-0 fw-bold">PM System</h5>
        <small class="text-muted">Project Management</small>
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
        
        @php
            $currentGroup = null;
            // Deduplicate menu items by route or label to avoid duplicate entries like multiple "Approvals" buttons.
            $seenKeys = [];
            $filteredMenuItems = [];
            foreach ($menuItems as $mi) {
                $key = null;
                if (!empty($mi['route'])) {
                    $key = $mi['route'];
                } elseif (!empty($mi['label'])) {
                    $key = strtolower(trim($mi['label']));
                }
                if ($key === null) {
                    // fallback to JSON representation
                    $key = md5(json_encode($mi));
                }
                if (!isset($seenKeys[$key])) {
                    $seenKeys[$key] = true;
                    $filteredMenuItems[] = $mi;
                }
            }
        @endphp

        @foreach ($filteredMenuItems as $item)
            @if(isset($item['is_group']) && $item['is_group'])
                {{-- Display group header --}}
                <li class="nav-item mt-3 mb-2">
                    <div class="text-uppercase small fw-bold px-3" style="font-size: 0.7rem; letter-spacing: 1px; color: #94a3b8;">
                        {{ $item['label'] }}
                    </div>
                </li>
                @php
                    $currentGroup = $item['label'];
                @endphp
            @elseif(isset($item['route']))
                @php
                    $isActive = request()->routeIs($item['route']) 
                        || collect($item['children'] ?? [])->pluck('route')->contains(fn($r) => request()->routeIs($r));
                @endphp

                @if (!empty($item['children']))
                    <li class="nav-item">
                        <a class="nav-link text-white d-flex justify-content-between align-items-center {{ $isActive ? 'active' : 'collapsed' }}" 
                           data-bs-toggle="collapse" 
                           href="#menu-{{ \Illuminate\Support\Str::slug($item['label']) }}" 
                           role="button" 
                           aria-expanded="{{ $isActive ? 'true' : 'false' }}">
                            <span><i class="{{ $item['icon'] }} me-2"></i> {{ $item['label'] }}</span>
                            <i class="bi bi-chevron-down small"></i>
                        </a>
                        <div class="collapse {{ $isActive ? 'show' : '' }}" id="menu-{{ \Illuminate\Support\Str::slug($item['label']) }}">
                            <ul class="nav flex-column ms-3">
                                @foreach ($item['children'] as $child)
                                    <li class="nav-item">
                                        <a href="{{ route($child['route']) }}" 
                                           class="nav-link text-white-50 py-2 {{ request()->routeIs($child['route']) ? 'active text-white fw-bold' : '' }}"
                                           style="font-size: 0.9rem;">
                                            <i class="bi bi-dot"></i> {{ $child['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </li>
                @else
                    <li class="nav-item">
                        <a href="{{ route($item['route']) }}" 
                           class="nav-link text-white {{ $isActive ? 'active fw-bold' : '' }}">
                            <i class="{{ $item['icon'] }} me-2"></i> {{ $item['label'] }}
                            @if(isset($item['badge']) && $item['badge'] > 0)
                                <span class="badge {{ $item['badge_class'] ?? 'bg-primary' }} ms-2">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    </li>
                @endif
            @endif
        @endforeach
    </ul>
    
    <style>
        .sidebar {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background-color: rgba(255, 255, 255, 0.5);
        }
        
        .sidebar .nav-link {
            border-radius: 0.375rem;
            padding: 0.6rem 1rem;
            transition: all 0.2s ease;
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
        }
        
        .sidebar .text-muted {
            opacity: 0.6;
        }
    </style>
</div>
