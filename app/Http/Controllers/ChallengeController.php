<?php

namespace App\Http\Controllers;

use App\Models\ChallengeMember;
use App\Models\ChallengeTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChallengeController extends Controller
{
    private const TERMS_VERSION = 'demo-2027-v1';

    public function home()
    {
        return view('challenge.home');
    }

    public function proposal()
    {
        return view('challenge.proposal');
    }

    public function createTeam(Request $request)
    {
        $data = $request->validate([
            'team_name' => ['required', 'string', 'max:100'],
            'captain_name' => ['required', 'string', 'max:100'],
            'captain_email' => ['required', 'email', 'max:255'],
            'team_size' => ['required', 'integer', 'between:2,6'],
        ]);

        $team = DB::transaction(function () use ($data) {
            $team = ChallengeTeam::create([
                'name' => trim($data['team_name']),
                'size' => (int) $data['team_size'],
                'captain_email' => Str::lower(trim($data['captain_email'])),
                'access_token' => Str::random(48),
            ]);
            $team->members()->create([
                'name' => trim($data['captain_name']),
                'email' => $team->captain_email,
                'role' => 'captain',
                'access_token' => Str::random(48),
            ]);
            return $team;
        });

        return redirect()->route('captain.show', $team->access_token);
    }

    public function captain(string $token)
    {
        $team = ChallengeTeam::with('members')->where('access_token', $token)->firstOrFail();
        return response()->view('challenge.captain', compact('team'))
            ->header('Cache-Control', 'private, no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function invite(Request $request, string $token)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
        ]);
        $email = Str::lower(trim($data['email']));

        DB::transaction(function () use ($token, $data, $email) {
            $team = ChallengeTeam::where('access_token', $token)->lockForUpdate()->firstOrFail();
            if ($team->members()->count() >= $team->size) {
                throw ValidationException::withMessages(['email' => 'All team places already have an invitation.']);
            }
            if ($team->members()->where('email', $email)->exists()) {
                throw ValidationException::withMessages(['email' => 'This person already has an invitation for this team.']);
            }
            $team->members()->create([
                'name' => trim($data['name']),
                'email' => $email,
                'role' => 'swimmer',
                'access_token' => Str::random(48),
            ]);
        });

        return back()->with('notice', 'Invitation link created. Copy it from the roster below.');
    }

    public function join(string $token)
    {
        $member = ChallengeMember::with('team')->where('access_token', $token)->firstOrFail();
        return response()->view('challenge.join', compact('member'))
            ->header('Cache-Control', 'private, no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function registerMember(Request $request, string $token)
    {
        $member = ChallengeMember::where('access_token', $token)->firstOrFail();
        if ($member->completed_at) {
            return redirect()->route('join.show', $token);
        }

        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'nationality' => ['required', 'string', 'max:80'],
            'passport_number' => ['required', 'string', 'min:5', 'max:40', 'regex:/^DEMO[A-Z0-9-]+$/i'],
            'passport_expiry' => ['required', 'date', 'after:today'],
            'terms' => ['accepted'],
        ]);

        $member->update([
            'phone' => trim($data['phone']),
            'date_of_birth' => $data['date_of_birth'],
            'nationality' => trim($data['nationality']),
            'passport_number' => trim($data['passport_number']),
            'passport_expiry' => $data['passport_expiry'],
            'terms_version' => self::TERMS_VERSION,
            'accepted_at' => now(),
            'completed_at' => now(),
        ]);

        return redirect()->route('join.show', $token)->with('notice', 'Your details have been submitted. Thank you.');
    }

    public function staff(Request $request)
    {
        $teams = ChallengeTeam::with('members')->latest()->get();
        $registered = $teams->sum(fn ($team) => $team->members->whereNotNull('completed_at')->count());
        $invited = $teams->sum(fn ($team) => $team->members->whereNull('completed_at')->count());
        return view('challenge.staff', compact('teams', 'registered', 'invited'));
    }

    public function replaceMember(Request $request, ChallengeMember $member)
    {
        abort_if($member->role === 'captain', 422, 'The captain cannot be replaced in this demo.');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
        ]);
        $email = Str::lower(trim($data['email']));
        if ($member->team->members()->where('email', $email)->whereKeyNot($member->id)->exists()) {
            throw ValidationException::withMessages(['email' => 'This email already belongs to the team.']);
        }

        DB::transaction(function () use ($member, $data, $email, $request) {
            DB::table('challenge_member_replacements')->insert([
                'team_id' => $member->team_id,
                'staff_user_id' => $request->user()->id,
                'old_name' => $member->name,
                'old_email' => $member->email,
                'new_name' => trim($data['name']),
                'new_email' => $email,
                'created_at' => now(),
            ]);
            $member->update([
                'name' => trim($data['name']),
                'email' => $email,
                'access_token' => Str::random(48),
                'phone' => null,
                'date_of_birth' => null,
                'nationality' => null,
                'passport_number' => null,
                'passport_expiry' => null,
                'terms_version' => null,
                'accepted_at' => null,
                'completed_at' => null,
            ]);
        });

        return redirect()->route('staff.dashboard')->with('notice', 'Replacement invitation created. The previous registration and link are no longer valid.');
    }

    public function export(): StreamedResponse
    {
        $teams = ChallengeTeam::with('members')->orderBy('name')->get();
        return response()->streamDownload(function () use ($teams) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Team', 'Team size', 'Name', 'Role', 'Email', 'Status', 'Conditions accepted at', 'Passport supplied', 'Passport expiry']);
            foreach ($teams as $team) {
                foreach ($team->members as $member) {
                    fputcsv($out, [
                        $team->name, $team->size, $member->name, $member->role, $member->email,
                        $member->completed_at ? 'Complete' : 'Awaiting member',
                        $member->accepted_at?->toIso8601String() ?? '',
                        $member->passport_number ? 'Yes' : 'No',
                        $member->passport_expiry?->format('Y-m-d') ?? '',
                    ]);
                }
            }
            fclose($out);
        }, 'jersey-france-roster.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
