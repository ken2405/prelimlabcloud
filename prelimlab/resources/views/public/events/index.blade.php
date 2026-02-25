@extends('layouts.app') {{-- Make sure you have a layout called app.blade.php --}}

@section('content')
<div class="container">
    <h1 class="mb-4">Upcoming Events</h1>

    @if($events->count() > 0)
        @foreach($events as $event)
            <div class="card mb-3">
                <div class="card-body">
                    <h3 class="card-title">{{ $event->title }}</h3>
                    <p class="card-text">{{ \Illuminate\Support\Str::limit($event->description, 150) }}</p>
                    <p class="card-text"><strong>Date:</strong> {{ \Carbon\Carbon::parse($event->event_date)->format('F j, Y, g:i A') }}</p>
                    <p class="card-text"><strong>Location:</strong> {{ $event->location }}</p>

                    @php
                        // calculate remaining slots safely
                        $remaining = $event->capacity - ($event->participants_count ?? 0);
                    @endphp

                    @if($remaining > 0)
                        <span class="badge bg-success">Slots Left: {{ $remaining }}</span>
                    @else
                        <span class="badge bg-danger">Fully Booked</span>
                    @endif

                    <br><br>
                    <a href="{{ route('events.show', $event->id) }}" class="btn btn-primary btn-sm">View Details</a>
                </div>
            </div>
        @endforeach

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $events->links() }}
        </div>
    @else
        <p>No upcoming events found.</p>
    @endif
</div>
@endsection