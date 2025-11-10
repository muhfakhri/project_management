<!-- Log Time Modal -->
<div class="modal fade" id="logTimeModal" tabindex="-1" aria-labelledby="logTimeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('time.store') }}" method="POST">
                @csrf
                <input type="hidden" name="card_id" value="{{ $task->card_id }}">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="logTimeModalLabel">
                        <i class="fas fa-clock me-2"></i>Log Time
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
                        <label for="date" class="form-label">
                            Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" 
                               class="form-control @error('date') is-invalid @enderror" 
                               id="date" 
                               name="date" 
                               value="{{ old('date', date('Y-m-d')) }}"
                               max="{{ date('Y-m-d') }}"
                               required>
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="hours" class="form-label">
                                Hours <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control @error('hours') is-invalid @enderror" 
                                   id="hours" 
                                   name="hours" 
                                   value="{{ old('hours', 0) }}"
                                   min="0"
                                   max="23"
                                   required>
                            @error('hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="minutes" class="form-label">
                                Minutes <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control @error('minutes') is-invalid @enderror" 
                                   id="minutes" 
                                   name="minutes" 
                                   value="{{ old('minutes', 0) }}"
                                   min="0"
                                   max="59"
                                   required>
                            @error('minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">
                            Description
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="3"
                                  placeholder="What did you work on?">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Log Time
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

