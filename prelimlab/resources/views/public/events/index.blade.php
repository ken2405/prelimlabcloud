@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Upcoming Events</h1>

    @if ($events->count() > 0)
        @foreach ($events as $event)
            <div class="card mb-3">
                <div class="card-body">
                    <h3>{{ $event->title }}</h3>
                    <p>{{ Str::limit($event->description, 150) }}</p>
                    <p><strong>Date:</strong> {{ date('F j, Y, g:i A', strtotime($event->event_date)) }}</p>
                    <p><strong>Location:</strong> {{ $event->location }}</p>

                    @php
                        $remaining = $event->capacity - $event->participants_count;
                    @endphp

                    @if ($remaining > 0)
                        <span class="badge bg-success">Slots Left: {{ $remaining }}</span>
                    @else
                        <span class="badge bg-danger">Fully Booked</span>
                    @endif

                    <br><br>
                    <a href="{{ route('events.show', $event->id) }}" class="btn btn-primary btn-sm">View</a>
                </div>
            </div>
        @endforeach

        {{ $events->links() }}
    @else
        <p>No events found.</p>
    @endif
</div>
@endsection