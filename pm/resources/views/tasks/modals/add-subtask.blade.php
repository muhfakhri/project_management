<!-- Add Subtask Modal -->
<div class="modal fade" id="addSubtaskModal" tabindex="-1" aria-labelledby="addSubtaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('subtasks.store') }}" method="POST">
                @csrf
                <input type="hidden" name="card_id" value="{{ $task->card_id }}">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="addSubtaskModalLabel">
                        <i class="fas fa-plus-circle me-2"></i>Add Subtask
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
                        <label for="subtask_title" class="form-label">
                            Subtask Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('title') is-invalid @enderror" 
                               id="subtask_title" 
                               name="title" 
                               value="{{ old('title') }}"
                               placeholder="Enter subtask title"
                               maxlength="255"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Break down your task into smaller, manageable steps
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subtask_description" class="form-label">
                            Description (Optional)
                        </label>
                        <textarea class="form-control" 
                                  id="subtask_description" 
                                  name="description" 
                                  rows="3" 
                                  placeholder="Add any additional details or requirements...">{{ old('description') }}</textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Provide context or specific requirements for this subtask
                        </div>
                    </div>
                    
                    <!-- Approval Requirement - ALWAYS REQUIRED -->
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="fas fa-shield-check fs-4 me-3"></i>
                        <div>
                            <strong class="d-block mb-1">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                Approval Required
                            </strong>
                            <small>
                                All subtasks require approval from Team Lead or Project Admin before completion.
                                This ensures quality control and proper task verification.
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add Subtask
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

