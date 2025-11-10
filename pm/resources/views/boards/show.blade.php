@extends('layouts.app')

@section('title', $board->board_name)

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-8">
            <h1>{{ $board->board_name }}</h1>
            <p class="text-muted">{{ $board->description }}</p>
        </div>
        <div class="col-md-4 text-end">
            @if($board->canManage(auth()->user()))
                <a href="{{ route('boards.edit', $board) }}" class="btn btn-outline-primary">
                    <i class="fas fa-edit me-1"></i>Edit Board
                </a>
                <form action="{{ route('boards.destroy', $board) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger" onclick="return confirm('Delete this board and all its cards?')">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between">
                    <strong>Cards</strong>
                    <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>Create New Task
                    </a>
                </div>
                <div class="card-body">
                    @if($board->cards->count() > 0)
                        <ul class="list-group">
                            @foreach($board->cards as $card)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $card->card_title }}</strong>
                                        <div class="small text-muted">{{ $card->description }}</div>
                                    </div>
                                    <div>
                                        <a href="{{ route('tasks.show', $card) }}" class="btn btn-sm btn-outline-primary">Open</a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No cards yet.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><strong>Board Info</strong></div>
                <div class="card-body">
                    <p><strong>Project:</strong> <a href="{{ route('projects.show', $board->project) }}">{{ $board->project->project_name }}</a></p>
                    <p><strong>Cards:</strong> {{ $board->cards->count() }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
