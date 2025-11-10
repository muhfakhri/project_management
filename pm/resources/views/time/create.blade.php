@extends('layouts.app')

@section('title', 'Log Time')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1>Log Time</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('time.index') }}">Time Tracking</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Log Time</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('time.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Time Logs
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Time Entry Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('time.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="card_id" class="form-label">Task <span class="text-danger">*</span></label>
                                    <select class="form-select @error('card_id') is-invalid @enderror" 
                                            id="card_id" 
                                            name="card_id" 
                                            required>
                                        <option value="">Select a task...</option>
                                        @foreach($cards as $card)
                                            <option value="{{ $card->card_id }}" {{ old('card_id') == $card->card_id ? 'selected' : '' }}>
                                                {{ $card->board->project->project_name }} - {{ $card->card_title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('card_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($cards->count() === 0)
                                        <div class="form-text text-warning">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            You don't have any assigned tasks. Please contact your project manager to assign tasks.
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
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
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hours_spent" class="form-label">Time Spent (hours) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control @error('hours_spent') is-invalid @enderror" 
                                               id="hours_spent" 
                                               name="hours_spent" 
                                               value="{{ old('hours_spent') }}" 
                                               min="0.1" 
                                               max="24" 
                                               step="0.1" 
                                               placeholder="e.g., 2.5" 
                                               required>
                                        <span class="input-group-text">hours</span>
                                        @error('hours_spent')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-text">
                                        <small>Quick add: 
                                            <button type="button" class="btn btn-sm btn-outline-secondary quick-time" data-time="0.5">30min</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary quick-time" data-time="1">1h</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary quick-time" data-time="2">2h</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary quick-time" data-time="4">4h</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary quick-time" data-time="8">8h</button>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Time Tracker</label>
                                    <div class="card border-secondary">
                                        <div class="card-body py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span id="timer-display" class="h5 mb-0">00:00:00</span>
                                                    <small class="text-muted d-block">Live Timer</small>
                                                </div>
                                                <div class="btn-group" role="group">
                                                    <button type="button" id="start-timer" class="btn btn-sm btn-success">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                    <button type="button" id="pause-timer" class="btn btn-sm btn-warning" disabled>
                                                        <i class="fas fa-pause"></i>
                                                    </button>
                                                    <button type="button" id="stop-timer" class="btn btn-sm btn-danger" disabled>
                                                        <i class="fas fa-stop"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Work Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4" 
                                      placeholder="Describe what you worked on..."
                                      required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Be specific about what you accomplished. Examples:
                                <ul class="small mb-0 mt-1">
                                    <li>Implemented user authentication feature</li>
                                    <li>Fixed bug in payment processing module</li>
                                    <li>Designed wireframes for dashboard</li>
                                    <li>Code review and testing</li>
                                </ul>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('time.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" {{ $cards->count() === 0 ? 'disabled' : '' }}>
                                <i class="fas fa-save me-1"></i>Log Time
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Time Logs -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Recent Entries</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">Today: <strong>{{ number_format($todayHours ?? 0, 1) }}h</strong></small>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">This Week: <strong>{{ number_format($weekHours ?? 0, 1) }}h</strong></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quick time buttons
    document.querySelectorAll('.quick-time').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('hours_spent').value = this.dataset.time;
        });
    });

    // Timer functionality
    let timerInterval;
    let startTime;
    let elapsedTime = 0;
    let isRunning = false;

    const timerDisplay = document.getElementById('timer-display');
    const startBtn = document.getElementById('start-timer');
    const pauseBtn = document.getElementById('pause-timer');
    const stopBtn = document.getElementById('stop-timer');
    const hoursInput = document.getElementById('hours_spent');

    function updateDisplay() {
        const hours = Math.floor(elapsedTime / 3600000);
        const minutes = Math.floor((elapsedTime % 3600000) / 60000);
        const seconds = Math.floor((elapsedTime % 60000) / 1000);
        
        timerDisplay.textContent = 
            `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }

    startBtn.addEventListener('click', function() {
        if (!isRunning) {
            startTime = Date.now() - elapsedTime;
            timerInterval = setInterval(function() {
                elapsedTime = Date.now() - startTime;
                updateDisplay();
            }, 1000);
            
            isRunning = true;
            startBtn.disabled = true;
            pauseBtn.disabled = false;
            stopBtn.disabled = false;
        }
    });

    pauseBtn.addEventListener('click', function() {
        if (isRunning) {
            clearInterval(timerInterval);
            isRunning = false;
            startBtn.disabled = false;
            pauseBtn.disabled = true;
        }
    });

    stopBtn.addEventListener('click', function() {
        clearInterval(timerInterval);
        
        // Update hours input with timer value
        const hours = elapsedTime / 3600000;
        hoursInput.value = Math.round(hours * 10) / 10; // Round to 1 decimal place
        
        // Reset timer
        elapsedTime = 0;
        isRunning = false;
        updateDisplay();
        
        startBtn.disabled = false;
        pauseBtn.disabled = true;
        stopBtn.disabled = true;
    });
});
</script>
@endsection
