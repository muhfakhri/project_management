<div class="kanban-card" data-card-id="{{ $card->card_id }}" draggable="true">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div class="kanban-card-title flex-grow-1">
            <a href="{{ route('tasks.show', $card) }}" class="text-decoration-none text-dark">
                {{ $card->card_title }}
            </a>
        </div>
        @if($card->priority)
            <span class="badge priority-badge 
                @if($card->priority == 'high') bg-danger
                @elseif($card->priority == 'medium') bg-warning
                @else bg-info
                @endif">
                {{ ucfirst($card->priority) }}
            </span>
        @endif
    </div>
    
    @if($card->description)
        <p class="text-muted small mb-2" style="font-size: 11px;">
            {{ Str::limit($card->description, 60) }}
        </p>
    @endif
    
    <div class="kanban-card-meta">
        @if($card->due_date)
            <div class="mb-1">
                <i class="fas fa-calendar-alt me-1"></i>
                <small class="{{ $card->due_date < now() ? 'text-danger fw-bold' : '' }}">
                    {{ \Carbon\Carbon::parse($card->due_date)->format('M d, Y') }}
                </small>
            </div>
        @endif
    </div>
    
    <div class="kanban-card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <!-- Assigned users -->
            <div class="d-flex align-items-center">
                @if($card->assignments && $card->assignments->count() > 0)
                    <div class="d-flex" style="gap: 2px;">
                        @foreach($card->assignments->take(3) as $assignment)
                            <div class="avatar-sm" 
                                 title="{{ $assignment->user->full_name ?? $assignment->user->username }}"
                                 data-bs-toggle="tooltip">
                                <span class="badge bg-secondary rounded-circle" style="width: 24px; height: 24px; padding: 4px; font-size: 10px;">
                                    {{ substr($assignment->user->username, 0, 2) }}
                                </span>
                            </div>
                        @endforeach
                        @if($card->assignments->count() > 3)
                            <span class="badge bg-light text-dark" style="font-size: 10px;">
                                +{{ $card->assignments->count() - 3 }}
                            </span>
                        @endif
                    </div>
                @else
                    <small class="text-muted">Unassigned</small>
                @endif
            </div>
            
            <!-- Task info -->
            <div class="d-flex align-items-center" style="gap: 8px;">
                @if($card->subtasks && $card->subtasks->count() > 0)
                    <small class="text-muted">
                        <i class="fas fa-tasks"></i> {{ $card->subtasks->where('status', 'done')->count() }}/{{ $card->subtasks->count() }}
                    </small>
                @endif
                
                @if($card->comments && $card->comments->count() > 0)
                    <small class="text-muted">
                        <i class="fas fa-comment"></i> {{ $card->comments->count() }}
                    </small>
                @endif
            </div>
        </div>
    </div>
</div>
