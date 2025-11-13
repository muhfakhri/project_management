<!-- Assign Member Modal -->
<div class="modal fade" id="assignMemberModal" tabindex="-1" aria-labelledby="assignMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tasks.assign', $task) }}" method="POST">
                @csrf
                
                <div class="modal-header">
                    <h5 class="modal-title" id="assignMemberModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Assign Team Member
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="user_id" class="form-label">
                            Team Member <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('user_id') is-invalid @enderror" 
                                id="user_id" 
                                name="user_id" 
                                required>
                            <option value="">Select a team member...</option>
                            @foreach($task->board->project->members as $member)
                                {{-- Exclude Team Lead and Project Admin roles --}}
                                @if($member->role !== 'Team Lead' && $member->role !== 'Project Admin')
                                    @if(!$task->assignments->pluck('user_id')->contains($member->user_id))
                                        <option value="{{ $member->user_id }}" {{ old('user_id') == $member->user_id ? 'selected' : '' }}>
                                            {{ $member->user->name ?? $member->user->full_name ?? $member->user->username }}
                                            @if(!empty($member->user->username) && ($member->user->name ?? null) && $member->user->username !== $member->user->name)
                                                ({{ $member->user->username }})
                                            @endif
                                            @if(!empty($member->user->email))
                                                - {{ $member->user->email }}
                                            @endif
                                            [{{ $member->role }}]
                                        </option>
                                    @endif
                                @endif
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>Assign
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

