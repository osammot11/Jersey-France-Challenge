@extends('challenge.layout')
@section('content')
<main class="inner-page narrow"><div class="inner-intro"><span class="kicker">RACE OFFICE</span><h1>Staff sign in.</h1><p>Access the live team readiness dashboard with an organiser account.</p></div><form class="panel personal-form" method="post" action="{{ route('login.post') }}">@csrf<label>Email address<input type="email" name="email" required autofocus value="{{ old('email') }}"></label><label>Password<input type="password" name="password" required></label><button class="button primary full" type="submit">Open race office <span>↗</span></button></form></main>
@endsection
