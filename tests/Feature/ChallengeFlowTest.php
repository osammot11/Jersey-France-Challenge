<?php

namespace Tests\Feature;

use App\Models\ChallengeMember;
use App\Models\ChallengeTeam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChallengeFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_captain_invites_a_swimmer_who_completes_their_own_form(): void
    {
        $this->post('/teams', [
            'team_name' => 'Channel Crew',
            'captain_name' => 'Alex Morgan',
            'captain_email' => 'alex@example.com',
            'team_size' => 2,
        ])->assertRedirect();

        $team = ChallengeTeam::with('members')->firstOrFail();
        $this->assertCount(1, $team->members);
        $this->post('/captain/'.$team->access_token.'/invite', [
            'name' => 'Sam Taylor', 'email' => 'sam@example.com',
        ])->assertRedirect();
        $this->post('/captain/'.$team->access_token.'/invite', [
            'name' => 'Extra Swimmer', 'email' => 'extra@example.com',
        ])->assertSessionHasErrors('email');

        $member = ChallengeMember::where('email', 'sam@example.com')->firstOrFail();
        $this->post('/join/'.$member->access_token, [
            'phone' => '+44 0000 000000',
            'date_of_birth' => '1995-04-12',
            'nationality' => 'British',
            'passport_number' => 'DEMO12345',
            'passport_expiry' => '2030-01-01',
            'terms' => '1',
        ])->assertRedirect();

        $member->refresh();
        $this->assertNotNull($member->completed_at);
        $this->assertNotNull($member->accepted_at);
        $this->assertSame('DEMO12345', $member->passport_number);
        $this->assertNotSame('DEMO12345', DB::table('challenge_members')->where('id', $member->id)->value('passport_number'));
    }

    public function test_real_passport_like_input_is_rejected_and_staff_requires_login(): void
    {
        $team = ChallengeTeam::create(['name' => 'Test', 'size' => 2, 'captain_email' => 'a@example.com', 'access_token' => str_repeat('a', 48)]);
        $member = $team->members()->create(['name' => 'A', 'email' => 'a@example.com', 'role' => 'captain', 'access_token' => str_repeat('b', 48)]);

        $this->post('/join/'.$member->access_token, [
            'phone' => '123456', 'date_of_birth' => '1990-01-01', 'nationality' => 'British',
            'passport_number' => '123456789', 'passport_expiry' => '2030-01-01', 'terms' => '1',
        ])->assertSessionHasErrors('passport_number');
        $this->get('/staff')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/staff')->assertOk()->assertSee('Test');
    }

    public function test_staff_replacement_invalidates_old_link_and_clears_previous_details(): void
    {
        $team = ChallengeTeam::create(['name' => 'Test', 'size' => 2, 'captain_email' => 'a@example.com', 'access_token' => str_repeat('a', 48)]);
        $member = $team->members()->create([
            'name' => 'Old Swimmer', 'email' => 'old@example.com', 'role' => 'swimmer',
            'access_token' => str_repeat('b', 48), 'passport_number' => 'DEMO12345',
            'accepted_at' => now(), 'completed_at' => now(),
        ]);
        $oldToken = $member->access_token;
        $this->actingAs(User::factory()->create())->post('/staff/members/'.$member->id.'/replace', [
            'name' => 'New Swimmer', 'email' => 'new@example.com',
        ])->assertRedirect('/staff');

        $member->refresh();
        $this->assertNull($member->passport_number);
        $this->assertNull($member->accepted_at);
        $this->assertNull($member->completed_at);
        $this->get('/join/'.$oldToken)->assertNotFound();
        $this->assertDatabaseHas('challenge_member_replacements', ['old_email' => 'old@example.com', 'new_email' => 'new@example.com']);
    }
}
