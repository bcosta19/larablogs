<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

test('guests cannot access blogs, the reader or mail settings', function () {
    foreach (['/', '/blogs/1', '/posts/1/read', '/settings/mail/test/example'] as $url) {
        $this->get($url)->assertRedirect('/login');
    }
    $this->post('/settings/mail', [])->assertRedirect('/login');
    $this->post('/settings/mail/test', [])->assertRedirect('/login');
    $this->post('/blogs', [])->assertRedirect('/login');
    $this->get('/login')->assertOk();
});

test('only the private account can sign in', function () {
    User::factory()->create(['email' => 'demo@example.com', 'password' => 'demo-password']);
    $this->post('/login', ['email' => 'demo@example.com', 'password' => 'demo-password'])->assertSessionHasErrors('email');
    $this->assertGuest();
    $owner = User::factory()->create(['email' => 'owner@example.com', 'password' => 'owner-fixture-password', 'is_owner' => true]);
    $this->post('/login', ['email' => 'owner@example.com', 'password' => 'owner-fixture-password'])->assertRedirect('/');
    $this->assertAuthenticatedAs($owner);
});

test('temporary password only allows password change and logout', function () {
    $owner = User::factory()->create(['password' => 'temporary-fixture-password', 'is_owner' => true, 'must_change_password' => true]);
    $this->post('/login', ['email' => $owner->email, 'password' => 'temporary-fixture-password'])->assertRedirect('/account/password');
    $this->get('/')->assertRedirect('/account/password');
    $this->post('/settings/mail', [])->assertRedirect('/account/password');
    $this->put('/account/password', ['current_password' => 'wrong', 'password' => 'new-fixture-password', 'password_confirmation' => 'new-fixture-password'])->assertSessionHasErrors('current_password');
    $this->put('/account/password', ['current_password' => 'temporary-fixture-password', 'password' => 'new-fixture-password', 'password_confirmation' => 'new-fixture-password'])->assertRedirect('/');
    expect($owner->fresh()->must_change_password)->toBeFalse()
        ->and(Hash::check('new-fixture-password', $owner->fresh()->password))->toBeTrue();
    $this->get('/')->assertOk();
    $this->post('/logout')->assertRedirect('/login');
    $this->assertGuest();
});

test('login throttles repeated password guesses', function () {
    $email = 'throttle@example.com';
    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', ['email' => $email, 'password' => 'wrong-password']);
    }
    $this->post('/login', ['email' => $email, 'password' => 'wrong-password'])->assertSessionHasErrors(['email' => 'auth.throttled']);
});

test('an authenticated non-owner cannot access private data', function () {
    $this->actingAs(User::factory()->create())->get('/')->assertForbidden();
});

test('an owner can manage mail remotely after authentication', function () {
    $this->actingAs(User::factory()->create(['is_owner' => true]))
        ->withServerVariables(['REMOTE_ADDR' => '100.64.10.20'])
        ->post('/settings/mail', ['provider' => 'resend', 'api_key' => 'fixture-key', 'from_address' => 'sender@example.com'])->assertSessionHas('success');
    Auth::logout();
});

test('account bootstrap requires password change and cannot silently reset an owner', function () {
    $emailFile = tempnam(sys_get_temp_dir(), 'larablogs-email-');
    $passwordFile = tempnam(sys_get_temp_dir(), 'larablogs-password-');
    chmod($emailFile, 0600);
    chmod($passwordFile, 0600);
    file_put_contents($emailFile, 'bootstrap@example.com');
    file_put_contents($passwordFile, 'bootstrap-fixture-password');
    try {
        $this->artisan('account:bootstrap', ['--email-file' => $emailFile, '--password-file' => $passwordFile])->assertExitCode(0);
        $owner = User::where('email', 'bootstrap@example.com')->firstOrFail();
        expect($owner->is_owner)->toBeTrue()->and($owner->must_change_password)->toBeTrue();
        $this->artisan('account:bootstrap', ['--email-file' => $emailFile, '--password-file' => $passwordFile])->assertExitCode(1);
    } finally {
        unlink($emailFile);
        unlink($passwordFile);
    }
});
