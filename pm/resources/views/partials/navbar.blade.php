<style>
    /* Navbar Responsive Styles */
    .navbar {
        margin: 0 !important;
        padding: 0.75rem 1rem !important;
        background-color: #fff !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
        border-left: none !important;
        border-bottom: 1px solid #e2e8f0 !important;
        width: 100% !important;
        max-width: 100% !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 1050 !important;
        min-height: 70px !important;
        height: 70px !important;
        display: flex !important;
        align-items: center !important;
        box-sizing: border-box !important;
        overflow: visible !important;
    }
    
    .navbar .container-fluid {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }

    .navbar-brand {
        margin-right: auto;
        font-size: 0.9rem;
        padding: 0 !important;
        margin-left: 0 !important;
        white-space: nowrap;
    }

    .navbar-brand .fw-bold {
        font-size: 1rem;
        color: #1e293b;
    }

    .navbar-brand small {
        font-size: 0.7rem !important;
        color: #64748b;
    }

    /* Mobile Menu Hamburger Dropdown */
    .mobile-menu-dropdown {
        display: inline-block;
        margin-right: 1rem;
    }

    .hamburger-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #1e293b;
        cursor: pointer;
        padding: 0.5rem;
        min-width: 44px;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.375rem;
        transition: background-color 0.2s;
    }
    
    .hamburger-btn:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .mobile-menu-dropdown .dropdown-menu {
        width: 90vw;
        max-width: 350px;
        max-height: 70vh;
        overflow-y: auto;
        margin-top: 0.5rem !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }

    .mobile-menu-dropdown .dropdown-item {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-height: 44px;
    }

    .mobile-menu-dropdown .dropdown-submenu {
        padding-left: 2.5rem;
        background-color: #f8f9fa;
        font-size: 0.85rem;
    }

    /* Navbar Actions Container */
    .navbar-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-left: auto;
    }

    /* Notification Icon */
    .notification-icon {
        font-size: 1.25rem;
        color: #1e293b;
        position: relative;
        padding: 0.5rem;
        min-width: 44px;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border-radius: 0.375rem;
        transition: background-color 0.2s;
        border: none;
        background: none;
    }

    .notification-icon:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .notification-badge {
        position: absolute;
        top: 0.25rem;
        right: 0.25rem;
        font-size: 0.65rem;
        min-width: 1.25rem;
        height: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .notification-dropdown {
        position: absolute !important;
        right: 0 !important;
        left: auto !important;
        top: 100% !important;
        width: 90vw !important;
        max-width: 350px !important;
        max-height: 60vh !important;
        margin-top: 0.5rem !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        overflow-y: auto !important;
    }

    /* User Profile Dropdown */
    .user-profile-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        background-color: transparent;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        min-height: 44px;
        transition: all 0.2s;
        white-space: nowrap;
    }
    
    .user-profile-btn:hover {
        background-color: rgba(0, 0, 0, 0.02);
        border-color: #cbd5e1;
    }
    
    .user-profile-dropdown {
        position: absolute !important;
        right: 0 !important;
        left: auto !important;
        top: 100% !important;
        min-width: 200px !important;
        margin-top: 0.5rem !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Dropdown menu positioning */
    .dropdown-menu {
        z-index: 1051 !important;
    }

    .navbar-nav {
        margin-right: 0 !important;
        flex-direction: row !important;
        align-items: center !important;
    }

    .nav-item.dropdown {
        margin-right: 0 !important;
        position: relative;
    }

    /* Tablet and Desktop */
    @media (min-width: 576px) {
        .user-profile-btn {
            max-width: 200px;
            font-size: 0.9375rem;
        }

        .notification-dropdown {
            width: 350px !important;
        }
    }

    @media (min-width: 768px) {
        .navbar {
            position: fixed !important;
            top: 0 !important;
            left: 280px !important;
            margin-left: -1px !important;
            right: 0 !important;
            width: calc(100vw - 249px) !important;
            max-width: calc(100vw - 249px) !important;
            padding: 0 !important;
            z-index: 1020 !important;
            border-left: none !important;
            box-sizing: border-box !important;
        }
        
        .navbar .container-fluid {
            padding: 1rem 1.5rem !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        .mobile-menu-dropdown {
            display: none !important;
        }

        .navbar-brand {
            font-size: 1.1rem;
            padding: 0 !important;
            margin-left: 0 !important;
        }
        
        /* Adjust content to account for fixed navbar */
        body {
            padding-top: 70px;
        }
        
        .content {
            margin-top: 0;
        }

        .navbar-brand .fw-bold {
            font-size: 1.15rem;
        }

        .navbar-actions {
            gap: 1rem;
        }

        .user-profile-btn {
            max-width: 250px;
            padding: 0.5rem 1rem;
            font-size: 1rem;
        }

        .notification-icon {
            font-size: 1.35rem;
        }

        .navbar-nav {
            margin-right: 1rem !important;
        }

        .nav-item.dropdown {
            margin-right: 1.5rem !important;
        }

        .notification-dropdown {
            width: 350px !important;
        }
    }

    @media (min-width: 992px) {
        .navbar {
            left: 280px !important;
            width: calc(100% - 280px) !important;
            max-width: calc(100% - 280px) !important;
            padding: 0.75rem 1rem !important;
        }
        
        .navbar .container-fluid {
            padding: 0 1.75rem !important;
        }
    }

    @media (min-width: 1200px) {
        .navbar {
            left: 280px !important;
            width: calc(100% - 280px) !important;
            max-width: calc(100% - 280px) !important;
            padding: 0.75rem 1rem !important;
        }
        
        .navbar .container-fluid {
            padding: 0 2rem !important;
        }
    }
    
    /* Mobile Hamburger Menu Styles */
    .mobile-menu-dropdown .dropdown-menu {
        max-height: 80vh;
        overflow-y: auto;
        width: 280px;
    }

    .mobile-menu-dropdown .dropdown-item {
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
    }

    .mobile-menu-dropdown .dropdown-item:hover {
        background-color: #f8f9fa;
        border-left-color: #0d6efd;
        padding-left: 1.25rem;
    }

    .mobile-menu-dropdown .dropdown-item.active {
        background-color: #e7f1ff;
        color: #0d6efd;
        border-left-color: #0d6efd;
        font-weight: 600;
    }

    .mobile-menu-dropdown .dropdown-item[data-bs-toggle="collapse"] {
        cursor: pointer;
        position: relative;
    }

    .mobile-menu-dropdown .dropdown-item[data-bs-toggle="collapse"] .bi-chevron-down {
        transition: transform 0.3s ease;
    }

    .mobile-menu-dropdown .dropdown-item[data-bs-toggle="collapse"][aria-expanded="true"] .bi-chevron-down {
        transform: rotate(180deg);
    }

    .mobile-menu-dropdown .dropdown-item.ps-5 {
        background-color: #f8f9fa;
        font-size: 0.9rem;
    }

    .mobile-menu-dropdown .dropdown-item.ps-5:hover {
        background-color: #e9ecef;
    }

    .mobile-menu-dropdown .collapse {
        transition: height 0.3s ease;
    }

    .mobile-menu-dropdown .dropdown-divider {
        margin: 0.25rem 0;
        opacity: 0.3;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-light bg-light" style="margin: 0 !important; padding: 0 !important;">
    <div class="container-fluid" style="margin: 0 !important;">
        @php
            // Pre-computed and cached in AppServiceProvider view composer
            $panelName = $panelName ?? 'Project Management';
            $subtitle = $subtitle ?? 'Workspace';
        @endphp

        <!-- Mobile Hamburger Menu Dropdown (Show on mobile only) -->
        <div class="mobile-menu-dropdown d-md-none dropdown">
            <button class="hamburger-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="mobileMenuButton">
                <i class="bi bi-list"></i>
            </button>
            <ul class="dropdown-menu" id="mobileMenuDropdown">
                @if(isset($menuItems) && is_array($menuItems))
                    @foreach ($menuItems as $index => $item)
                        @if(isset($item['is_group']) && $item['is_group'])
                            {{-- Group header - skip in mobile menu --}}
                            @continue
                        @endif
                        
                        @php
                            $isActive = isset($item['route']) && (request()->routeIs($item['route']) 
                                || collect($item['children'] ?? [])->pluck('route')->contains(fn($r) => request()->routeIs($r)));
                        @endphp
                        
                        @if (!empty($item['children']))
                            <!-- Menu dengan submenu (collapsible) -->
                            <li>
                                <a class="dropdown-item d-flex justify-content-between align-items-center {{ $isActive ? 'active' : '' }}" 
                                   data-bs-toggle="collapse" 
                                   href="#mobile-submenu-{{ $index }}" 
                                   role="button" 
                                   aria-expanded="{{ $isActive ? 'true' : 'false' }}"
                                   onclick="event.stopPropagation();">
                                    <span><i class="{{ $item['icon'] ?? '' }} me-2"></i>{{ $item['label'] }}</span>
                                    <i class="bi bi-chevron-down small ms-auto"></i>
                                </a>
                            </li>
                            <li>
                                <div class="collapse {{ $isActive ? 'show' : '' }}" id="mobile-submenu-{{ $index }}">
                                    @foreach ($item['children'] as $child)
                                        <a class="dropdown-item ps-5 {{ request()->routeIs($child['route']) ? 'active fw-bold' : '' }}" 
                                           href="{{ route($child['route']) }}">
                                            <i class="bi bi-arrow-return-right me-2"></i>{{ $child['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </li>
                            @if (!$loop->last)
                                <li><hr class="dropdown-divider my-1"></li>
                            @endif
                        @elseif(isset($item['route']))
                            <!-- Menu tanpa submenu -->
                            <li>
                                <a class="dropdown-item {{ $isActive ? 'active fw-bold' : '' }}" href="{{ route($item['route']) }}">
                                    <i class="{{ $item['icon'] ?? '' }} me-2"></i>{{ $item['label'] }}
                                    @if(isset($item['badge']) && $item['badge'] > 0)
                                        <span class="badge {{ $item['badge_class'] ?? 'bg-primary' }} ms-2">{{ $item['badge'] }}</span>
                                    @endif
                                </a>
                            </li>
                        @endif
                    @endforeach
                @else
                    <!-- Fallback menu jika $menuItems tidak tersedia -->
                    <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="fas fa-home me-2"></i>Dashboard</a></li>
                    @if(isset($isProjectAdmin) && $isProjectAdmin)
                        <li><a class="dropdown-item" href="{{ route('statistics.index') }}"><i class="fas fa-chart-bar me-2"></i>Statistics</a></li>
                    @endif
                    <li><a class="dropdown-item" href="{{ route('projects.index') }}"><i class="fas fa-project-diagram me-2"></i>Projects</a></li>
                    <li><a class="dropdown-item" href="{{ route('tasks.my') }}"><i class="fas fa-tasks me-2"></i>My Tasks</a></li>
                    <li><a class="dropdown-item" href="{{ route('time-logs.index') }}"><i class="fas fa-clock me-2"></i>Time Tracking</a></li>
                @endif
            </ul>
        </div>
        
        <div class="navbar-brand">
            <div class="d-flex flex-column">
                @php
                    // Check authenticated user and derive basic role info if available
                    $user = auth()->user();
                    $isProjectAdmin = false;
                    $panelName = 'Project Management';
                    $subtitle = 'Workspace';

                    if ($user) {
                        $userRoles = \App\Models\ProjectMember::where('user_id', $user->user_id)
                            ->pluck('role')
                            ->unique();
                        $isProjectAdmin = $userRoles->contains('Project Admin');
                        $isTeamLead = $userRoles->contains('Team Lead');
                        $panelName = $isProjectAdmin ? 'Admin Panel' : ($isTeamLead ? 'Team Lead Panel' : 'Project Management');
                        $subtitle = $isProjectAdmin ? 'Project Management' : ($isTeamLead ? 'Project Collaboration' : 'Workspace');
                    }
                @endphp

                @if(isset($isProjectAdmin) && $isProjectAdmin)
                    <span class="fw-bold">{{ $panelName }}</span>
                    <small class="text-muted d-none d-sm-block" style="font-size: 0.75rem; line-height: 1;">{{ $subtitle }}</small>
                @else
                    <span class="fw-bold">Project Management</span>
                    <small class="text-muted d-none d-sm-block" style="font-size: 0.75rem; line-height: 1;">Workspace</small>
                @endif
            </div>
        </div>

        <!-- Navbar Actions (Notifications + User Profile) -->
        <div class="navbar-actions ms-auto">
            <!-- Notifications Dropdown -->
            <div class="dropdown position-relative">
                <a class="notification-icon" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge badge bg-danger" id="notification-badge" style="display: none;">
                        0
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                    <div class="dropdown-header d-flex justify-content-between align-items-center" style="padding: 0.75rem 1rem; background-color: #f8f9fa;">
                        <span class="fw-bold" style="font-size: 0.95rem;">Notifications</span>
                        <button class="btn btn-sm btn-link text-primary p-0" id="mark-all-read" style="text-decoration: none; font-size: 0.8rem;">
                            Mark all read
                        </button>
                    </div>
                    <div class="dropdown-divider" style="margin: 0;"></div>
                    <div id="notifications-container" style="max-height: 300px; overflow-y: auto;">
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-bell-slash fa-2x mb-2"></i>
                            <p class="mb-0">No new notifications</p>
                        </div>
                    </div>
                    <div class="dropdown-divider" style="margin: 0;"></div>
                    <a class="dropdown-item text-center text-primary" href="{{ route('notifications.index') }}" style="padding: 0.5rem;">
                        <small>View all notifications</small>
                    </a>
                </div>
            </div>

            <!-- User Profile Dropdown -->
            <div class="dropdown position-relative">
                @if($user)
                    <button class="user-profile-btn" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        @if($user->profile_picture)
                            <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                 alt="{{ $user->full_name ?? $user->username }}"
                                 class="user-profile-avatar"
                                 style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; margin-right: 8px;">
                        @else
                            <i class="fas fa-user-circle"></i>
                        @endif
                        <span class="d-none d-sm-inline">{{ $user->full_name ?? $user->username ?? 'User' }}</span>
                        <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end user-profile-dropdown" aria-labelledby="userDropdown">
                @else
                    <div style="display:flex;gap:.5rem;align-items:center">
                        <a href="{{ route('login') }}" class="btn btn-link">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-primary">Sign up</a>
                    </div>
                @endif
                @if($user)
                    <li class="dropdown-header d-block d-sm-none">
                        <strong>{{ $user->full_name ?? $user->username ?? 'User' }}</strong>
                    </li>
                    <li class="d-block d-sm-none"><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="fas fa-user me-2"></i>My Profile
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('profile.show', auth()->id()) }}">
                        <i class="fas fa-eye me-2"></i>View Profile
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
                @endif
            </div>
        </div>
    </div>
</nav>

<style>
.notification-dropdown {
    overflow-y: auto;
    max-height: 400px !important;
}

.notification-dropdown .dropdown-item {
    white-space: normal;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f0f0f0;
    word-wrap: break-word;
    overflow-wrap: break-word;
    max-width: 350px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.notification-dropdown .dropdown-item:hover {
    background-color: #f8f9fa;
}

.notification-dropdown .dropdown-item.unread {
    background-color: #e7f3ff;
    border-left: 3px solid #0d6efd;
}

.notification-dropdown .dropdown-item .notification-content {
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    line-height: 1.4;
}

.notification-dropdown .dropdown-item .fw-bold {
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 280px;
}

.notification-dropdown .dropdown-item small {
    font-size: 0.8rem;
    line-height: 1.3;
    display: block;
    margin-top: 0.25rem;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.notification-dropdown .notification-icon {
    width: 36px;
    height: 36px;
    min-width: 36px;
    font-size: 0.9rem;
}

.notification-dropdown .flex-grow-1 {
    overflow: hidden;
    max-width: calc(100% - 50px);
}
</style>

<script>
// Fetch and update notifications
function loadNotifications() {
    fetch('{{ route("notifications.recent") }}')
        .then(response => response.json())
        .then(notifications => {
            updateNotificationBadge(notifications);
            updateNotificationDropdown(notifications);
        })
        .catch(error => console.error('Error loading notifications:', error));
}

function updateNotificationBadge(notifications) {
    const unreadCount = notifications.filter(n => !n.read_at).length;
    const badge = document.getElementById('notification-badge');
    
    if (unreadCount > 0) {
        badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
        badge.style.display = 'inline-block';
    } else {
        badge.style.display = 'none';
    }
}

function updateNotificationDropdown(notifications) {
    const container = document.getElementById('notifications-container');
    
    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="fas fa-bell-slash fa-2x mb-2"></i>
                <p class="mb-0">No new notifications</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = notifications.map(notification => `
        <div class="dropdown-item ${!notification.read_at ? 'unread' : ''}"
             data-notification-id="${notification.notification_id}"
             data-action-url="${notification.action_url || ''}"
             style="cursor: pointer;">
            <div class="d-flex align-items-start">
                <div class="notification-icon bg-${getNotificationColor(notification.type)} text-white me-2">
                    <i class="fas ${getNotificationIcon(notification.type)}"></i>
                </div>
                <div class="flex-grow-1 notification-content">
                    <div class="fw-bold" title="${escapeHtml(notification.title)}">${escapeHtml(notification.title)}</div>
                    <small class="text-muted">${escapeHtml(notification.message)}</small>
                    <div class="small text-muted mt-1">
                        <i class="fas fa-clock"></i> ${formatTimeAgo(notification.created_at)}
                    </div>
                </div>
                ${!notification.read_at ? '<span class="badge bg-primary ms-2" style="flex-shrink: 0;">New</span>' : ''}
            </div>
        </div>
    `).join('');
    
    // Add click handlers after rendering
    container.querySelectorAll('.dropdown-item[data-notification-id]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const notificationId = this.dataset.notificationId;
            const actionUrl = this.dataset.actionUrl;
            
            // Mark as read first
            fetch(`{{ url('/notifications') }}/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(response => {
                if (!response.ok) {
                    // log server error but continue to redirect
                    return response.text().then(t => { console.warn('Mark-as-read returned non-OK:', t || response.statusText); });
                }
                return response.json().catch(() => {});
            }).then(() => {
                // sanitize actionUrl to avoid redirecting to an external host (like 192.168.19.5)
                let target = null;
                if (actionUrl) {
                    try {
                        const parsed = new URL(actionUrl, window.location.origin);
                        if (parsed.host !== window.location.host) {
                            // Different host — redirect only to path+query+hash on current host
                            target = parsed.pathname + (parsed.search || '') + (parsed.hash || '');
                        } else {
                            target = parsed.href;
                        }
                    } catch (e) {
                        // Malformed URL — fallback to raw actionUrl
                        target = actionUrl;
                    }
                }

                // Then redirect to action URL or notification show page
                if (target) {
                    window.location.href = target;
                } else {
                    window.location.href = '/notifications/' + notificationId;
                }
            }).catch(error => {
                console.error('Error marking notification as read:', error);
                let target = null;
                if (actionUrl) {
                    try {
                        const parsed = new URL(actionUrl, window.location.origin);
                        if (parsed.host !== window.location.host) {
                            target = parsed.pathname + (parsed.search || '') + (parsed.hash || '');
                        } else {
                            target = parsed.href;
                        }
                    } catch (e) {
                        target = actionUrl;
                    }
                }
                if (target) {
                    window.location.href = target;
                } else {
                    window.location.href = '/notifications/' + notificationId;
                }
            });
        });
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getNotificationIcon(type) {
    const icons = {
        'task_assigned': 'fa-tasks',
        'comment_added': 'fa-comment',
        'deadline_approaching': 'fa-exclamation-triangle',
        'project_invitation': 'fa-envelope',
        'mention': 'fa-at',
        'time_logged': 'fa-clock',
        'board_created': 'fa-clipboard',
        'status_changed': 'fa-exchange-alt'
    };
    return icons[type] || 'fa-bell';
}

function getNotificationColor(type) {
    const colors = {
        'task_assigned': 'primary',
        'comment_added': 'info',
        'deadline_approaching': 'warning',
        'project_invitation': 'purple',
        'mention': 'danger',
        'time_logged': 'secondary',
        'board_created': 'dark',
        'status_changed': 'info'
    };
    return colors[type] || 'secondary';
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'just now';
    if (seconds < 3600) return Math.floor(seconds / 60) + ' minutes ago';
    if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
    if (seconds < 604800) return Math.floor(seconds / 86400) + ' days ago';
    return date.toLocaleDateString();
}

// Mark all as read
document.getElementById('mark-all-read')?.addEventListener('click', function(e) {
    e.preventDefault();
    fetch('{{ route("notifications.markAllAsRead") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications();
        }
    });
});

// Load notifications on page load
document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    
    // Reload notifications every 30 seconds
    setInterval(loadNotifications, 30000);

    // Mobile hamburger menu - prevent dropdown close on collapse toggle
    const mobileMenuDropdown = document.getElementById('mobileMenuDropdown');
    if (mobileMenuDropdown) {
        mobileMenuDropdown.addEventListener('click', function(e) {
            // Prevent dropdown from closing when clicking collapse toggles
            if (e.target.closest('[data-bs-toggle="collapse"]')) {
                e.stopPropagation();
            }
        });

        // Don't close dropdown when clicking inside collapsible areas
        mobileMenuDropdown.querySelectorAll('.collapse').forEach(collapse => {
            collapse.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    }
});
</script>
