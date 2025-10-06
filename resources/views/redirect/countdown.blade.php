@extends('quvel::layout')

@section('title', 'Redirecting to App...')

@section('content')
    <div class="message">
        <h2 style="margin-bottom: 1rem;">Success!</h2>
        <p>Redirecting you back to the app...</p>
    </div>

    <div class="countdown" id="countdown">{{ $timeout }}</div>

    <div style="margin-top: 1.5rem;">
        <a href="{{ $appUrl }}" class="btn" style="margin-bottom: 10px" id="openApp">
            Open App Now
        </a>

        <a href="{{ $webUrl }}" class="btn btn-secondary">
            Continue in Browser
        </a>
    </div>

    <p style="margin-top: 1rem; font-size: 0.875rem; color: #9ca3af;">
        If the app doesn't open automatically, click "Open App Now" or continue in your browser.
    </p>
@endsection

@push('scripts')
<script>
    let timeLeft = {{ $timeout }};
    const countdownEl = document.getElementById('countdown');
    const appUrl = @json($appUrl);

    function updateCountdown() {
        countdownEl.textContent = timeLeft;

        if (timeLeft <= 0) {
            // Try to open the app
            window.location.href = appUrl;
            countdownEl.textContent = 'Opening app...';
            return;
        }

        timeLeft--;
        setTimeout(updateCountdown, 1000);
    }

    // Start countdown
    updateCountdown();

    // Handle manual app opening
    document.getElementById('openApp').addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = appUrl;
    });
</script>
@endpush