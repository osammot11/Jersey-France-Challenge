<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="referrer" content="no-referrer">
    <title>{{ $title ?? 'Jersey France Challenge — registration concept' }}</title>
    <meta name="description" content="A private registration concept for the Jersey France Challenge organisers.">
    <link rel="stylesheet" href="{{ asset('challenge.css') }}?v=1">
</head>
<body>
    <header class="site-header">
        <a class="wordmark" href="{{ route('home') }}" aria-label="Jersey France Challenge concept home">
            <span class="mark">JF<span>→</span>FR</span>
            <span class="wordmark-text">JERSEY <b>FRANCE</b><small>CHALLENGE · REGISTRATION CONCEPT</small></span>
        </a>
        <nav aria-label="Main navigation">
            <a href="{{ route('home') }}#how-it-works">How it works</a>
            <a href="{{ route('proposal') }}">Proposal & costs</a>
            <a class="nav-cta" href="{{ route('staff.dashboard') }}">Race office <span aria-hidden="true">↗</span></a>
        </nav>
    </header>
    @if ($errors->any())
        <div class="flash error" role="alert"><strong>Please check the following:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @if (session('notice'))<div class="flash success" role="status">{{ session('notice') }}</div>@endif
    @yield('content')
    <footer class="site-footer">
        <div><strong>JERSEY → FRANCE</strong><span>14 miles. One team. Every detail accounted for.</span></div>
        <p>Private concept prepared for the event organisers. 2027 date and entry terms to be confirmed.</p>
    </footer>
</body>
</html>
