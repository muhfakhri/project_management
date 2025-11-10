@extends('layouts.app')

@section('title', 'Edit Board - ' . $board->board_name)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Edit Board
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('boards.update', $board) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <input type="hidden" name="project_id" value="{{ $board->project_id }}">
                        
                        <div class="mb-3">
                            <label for="board_name" class="form-label">
                                Board Name <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control @error('board_name') is-invalid @enderror" 
                                id="board_name" 
                                name="board_name" 
                                value="{{ old('board_name', $board->board_name) }}" 
                                required
                                autofocus
                            >
                            @error('board_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea 
                                class="form-control @error('description') is-invalid @enderror" 
                                id="description" 
                                name="description" 
                                rows="4"
                            >{{ old('description', $board->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Optional: Add a description for this board</small>
                        </div>

                        <div class="mb-3">
                            <label for="position" class="form-label">
                                Position <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="number" 
                                class="form-control @error('position') is-invalid @enderror" 
                                id="position" 
                                name="position" 
                                value="{{ old('position', $board->position) }}" 
                                min="0"
                                required
                            >
                            @error('position')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Order of the board in the project (0 = first)</small>
                        </div>

                        <div class="mb-3">
                            <label for="status_mapping" class="form-label">Status Mapping</label>
                            <select name="status_mapping" id="status_mapping" class="form-select @error('status_mapping') is-invalid @enderror">
                                <option value="" {{ old('status_mapping', $board->status_mapping) == '' ? 'selected' : '' }}>-- No Status Mapping --</option>
                                <option value="todo" {{ old('status_mapping', $board->status_mapping) == 'todo' ? 'selected' : '' }}>To Do</option>
                                <option value="in_progress" {{ old('status_mapping', $board->status_mapping) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="review" {{ old('status_mapping', $board->status_mapping) == 'review' ? 'selected' : '' }}>Review</option>
                                <option value="done" {{ old('status_mapping', $board->status_mapping) == 'done' ? 'selected' : '' }}>Done</option>
                            </select>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Tasks moved to this board will automatically get this status. Leave empty for no automatic status change.
                            </div>
                            @error('status_mapping')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> This board has {{ $board->cards->count() }} task(s). 
                            Editing the board name will not affect existing tasks.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('projects.show', $board->project_id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Board
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Board Info Card -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Board Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Project:</strong> 
                                <a href="{{ route('projects.show', $board->project_id) }}" class="text-decoration-none">
                                    {{ $board->project->project_name }}
                                </a>
                            </p>
                            <p class="mb-2">
                                <strong>Total Tasks:</strong> 
                                <span class="badge bg-primary">{{ $board->cards->count() }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Created:</strong> 
                                {{ $board->created_at->format('M d, Y') }}
                            </p>
                            <p class="mb-2">
                                <strong>Last Updated:</strong> 
                                {{ $board->updated_at->format('M d, Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            @if($board->cards->count() > 0)
            <!-- Tasks in this Board -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks me-2"></i>Tasks in this Board
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($board->cards->take(5) as $card)
                            <a href="{{ route('tasks.show', $card) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $card->card_title }}</h6>
                                        <small class="text-muted">
                                            Priority: 
                                            <span class="badge badge-sm bg-{{ $card->priority === 'high' ? 'danger' : ($card->priority === 'medium' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($card->priority) }}
                                            </span>
                                        </small>
                                    </div>
                                    <span class="badge bg-info">{{ $card->status }}</span>
                                </div>
                            </a>
                        @endforeach
                        
                        @if($board->cards->count() > 5)
                            <div class="list-group-item text-center">
                                <small class="text-muted">
                                    And {{ $board->cards->count() - 5 }} more task(s)...
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
