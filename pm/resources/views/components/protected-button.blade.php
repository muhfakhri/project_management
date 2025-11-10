{{-- 
    Protected Action Button Component
    Displays button only if user has permission, shows tooltip if not
    
    Usage:
    <x-protected-button 
        permission="create-project"
        user-role="{{ $userRole }}"
        allowed-roles="Project Admin"
        action-name="create projects"
        route="projects.create"
        class="btn-primary"
        icon="fa-plus"
    >
        Create Project
    </x-protected-button>
--}}

@props([
    'permission' => '',
    'userRole' => '',
    'allowedRoles' => '',
    'actionName' => 'perform this action',
    'route' => '#',
    'class' => 'btn-primary',
    'icon' => '',
    'method' => 'GET',
    'confirm' => false,
    'confirmMessage' => 'Are you sure?'
])

@php
    $allowed = collect(explode(',', $allowedRoles))->map(fn($r) => trim($r));
    $hasPermission = $allowed->contains($userRole);
@endphp

@if($hasPermission)
    @if($method === 'POST' || $method === 'DELETE')
        <form action="{{ $route }}" method="POST" style="display: inline;" @if($confirm) onsubmit="return confirm('{{ $confirmMessage }}')" @endif>
            @csrf
            @if($method === 'DELETE')
                @method('DELETE')
            @endif
            <button type="submit" class="btn {{ $class }}">
                @if($icon)
                    <i class="{{ $icon }} me-1"></i>
                @endif
                {{ $slot }}
            </button>
        </form>
    @else
        <a href="{{ $route }}" class="btn {{ $class }}">
            @if($icon)
                <i class="{{ $icon }} me-1"></i>
            @endif
            {{ $slot }}
        </a>
    @endif
@else
    <button 
        type="button" 
        class="btn {{ $class }} disabled opacity-50" 
        data-permission-required="{{ $permission }}"
        data-user-role="{{ $userRole }}"
        data-allowed-roles="{{ $allowedRoles }}"
        data-action-name="{{ $actionName }}"
        title="Only {{ $allowedRoles }} can {{ $actionName }}"
        style="cursor: not-allowed;"
    >
        @if($icon)
            <i class="{{ $icon }} me-1"></i>
        @endif
        {{ $slot }}
    </button>
@endif
