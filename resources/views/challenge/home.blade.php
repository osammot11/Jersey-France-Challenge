@extends('challenge.layout')

@section('content')
<main>
    <section class="hero">
        <div class="hero-copy">
            <div class="eyebrow"><span class="live-dot"></span> 2027 REGISTRATION CONCEPT <span class="line"></span> JERSEY → CARTERET</div>
            <h1>One crossing.<br><em>One complete team.</em></h1>
            <p class="hero-lede">A clearer way to register for the Jersey France Challenge. The captain sets up the team; every swimmer completes their own details and accepts the event conditions; the race office sees what is still outstanding.</p>
            <div class="hero-actions"><a class="button primary" href="#create-team">Try the team flow <span>↗</span></a><a class="button ghost" href="{{ route('proposal') }}">View proposal</a></div>
            <p class="demo-note">Interactive demo. Please use fictional personal and passport information.</p>
        </div>
        <div class="hero-art" aria-hidden="true">
            <div class="sea-grid"></div><div class="route-line"></div>
            <div class="point jersey"><span>01</span><strong>LA COUPE</strong><small>JERSEY</small></div>
            <div class="point france"><span>02</span><strong>CARTERET</strong><small>FRANCE</small></div>
            <div class="distance-pill">14 MILES <i></i> TEAM SWIM RELAY</div>
            <div class="hero-art-bottom"><span>49° 12′ N</span><span>CHANNEL CROSSING</span><span>02° 03′ W</span></div>
        </div>
    </section>

    <div class="stat-band"><div><strong>2–6</strong><span>swimmers per team</span></div><div><strong>16</strong><span>teams took part in 2026</span></div><div><strong>1</strong><span>personal form per swimmer</span></div><div><strong>2027</strong><span>date to be announced</span></div></div>

    <section id="how-it-works" class="section how-section">
        <div class="section-heading"><div><span class="kicker">01 / THE EXPERIENCE</span><h2>Built around the way<br>this event actually works.</h2></div><p>Team places are easy to start. The detail is making sure every person is ready before the boat leaves Jersey.</p></div>
        <div class="step-grid">
            <article><span class="step-number">01</span><div class="step-icon">↗</div><h3>Captain creates a team</h3><p>Choose a team name and 2–6 places. A private captain link keeps the roster together.</p></article>
            <article><span class="step-number">02</span><div class="step-icon">✳</div><h3>Each swimmer signs up</h3><p>Every teammate gets a unique link to provide their own details and accept the draft conditions.</p></article>
            <article><span class="step-number">03</span><div class="step-icon">◎</div><h3>Race office sees readiness</h3><p>Staff can identify incomplete teams, replace a swimmer and export a practical roster.</p></article>
        </div>
    </section>

    <section class="section roster-section"><div class="roster-copy"><span class="kicker">02 / THE RACE OFFICE</span><h2>No more chasing a mystery roster.</h2><p>One view shows which team places are unfilled, who has an invitation and who has supplied all required details. Passport numbers stay out of the roster and export.</p><a class="text-link" href="{{ route('staff.dashboard') }}">Open the staff dashboard <span>↗</span></a></div><div class="roster-preview" aria-label="Illustration of the staff dashboard"><div class="preview-head"><span>RACE OFFICE / TEAM READINESS</span><span>● LIVE VIEW</span></div><div class="preview-title">Swimmer check-in <small>Concept preview</small></div><div class="preview-row"><span class="avatar">AT</span><div><strong>Atlantic Tides</strong><small>6 team places</small></div><div class="progress"><i style="width:66%"></i></div><b>4 / 6</b></div><div class="preview-row"><span class="avatar teal">CS</span><div><strong>Channel Six</strong><small>5 team places</small></div><div class="progress"><i style="width:80%"></i></div><b>4 / 5</b></div><div class="preview-row"><span class="avatar orange">BW</span><div><strong>Bluewater Crew</strong><small>4 team places</small></div><div class="progress"><i style="width:100%"></i></div><b>4 / 4</b></div><div class="preview-foot"><span>● Complete</span><span>○ Awaiting a swimmer</span></div></div></section>

    <section id="create-team" class="section signup-section"><div><span class="kicker">03 / INTERACTIVE DEMO</span><h2>Start a team.<br>See the flow.</h2><p>Create a fictional team, add teammate invitations, then open each personal link to complete the registration. No payment is taken.</p><div class="note-card"><strong>For this demonstration</strong><p>Use made-up information only. The real event conditions, pricing, payment flow and data retention policy require organiser approval before launch.</p></div></div><form class="form-card" method="post" action="{{ route('teams.create') }}">@csrf<h3>Create a demo team</h3><p class="form-subtitle">Step 1 of 3 · Team details</p><label>Team name<input name="team_name" required maxlength="100" value="{{ old('team_name') }}" placeholder="e.g. Channel Crew"></label><div class="form-row"><label>Captain's name<input name="captain_name" required maxlength="100" value="{{ old('captain_name') }}" placeholder="Alex Morgan"></label><label>Team size<select name="team_size" required>@foreach(range(2,6) as $size)<option value="{{ $size }}" @selected(old('team_size', 4) == $size)>{{ $size }} swimmers</option>@endforeach</select></label></div><label>Captain's email<input type="email" name="captain_email" required value="{{ old('captain_email') }}" placeholder="alex@example.com"></label><button class="button primary full" type="submit">Create team <span>↗</span></button><small>Your private captain link will appear on the next screen.</small></form></section>
</main>
@endsection
