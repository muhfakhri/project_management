@extends('layouts.app')

@section('title', $board->board_name . ' - Cards')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1>{{ $board->board_name }} - Cards</h1>
            <p class="text-muted">{{ $board->description }}</p>

            @if($board->cards->count() > 0)
                <div class="list-group">
                    @foreach($board->cards as $card)
                        <a href="{{ route('tasks.show', $card) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">{{ $card->card_title }}</h5>
                                <small>{{ $card->created_at->format('M d, Y') }}</small>
                            </div>
                            <p class="mb-1">{{ $card->description }}</p>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-muted">No cards found.</p>
            @endif
        </div>
    </div>
</div>
@endsection
