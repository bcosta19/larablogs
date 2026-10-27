<?php

use App\Jobs\SendMailTest;
use App\Models\MailSetting;
use App\Services\MailSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

test('local setup encrypts the key and does not expose it to the page', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])->post('/settings/mail', [
        'provider' => 'resend',
        'api_key' => 'test-secret-key',
        'from_address' => 'reader@example.com',
    ])->assertRedirect();

    expect(DB::table('mail_settings')->value('api_key'))->not->toBe('test-secret-key');
    expect(MailSetting::first()->api_key)->toBe('test-secret-key');
    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('mailSettings.provider', 'resend')
        ->where('mailSettings.from_address', 'reader@example.com')
        ->missing('mailSettings.api_key'));

    app(MailSettings::class)->configure();
    expect(config('mail.default'))->toBe('larablogs_resend')
        ->and(config('mail.mailers.larablogs_resend.host'))->toBe('smtp.resend.com')
        ->and(config('mail.from.address'))->toBe('reader@example.com');
});

test('remote requests cannot change mail settings', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.2'])->post('/settings/mail', [
        'provider' => 'resend', 'api_key' => 'test-secret-key', 'from_address' => 'reader@example.com',
    ])->assertForbidden();
    expect(MailSetting::count())->toBe(0);
});

test('invalid input never flashes the api key', function () {
    $this->post('/settings/mail', ['provider' => 'resend', 'api_key' => 'test-secret-key', 'from_address' => 'invalid'])
        ->assertSessionHasErrorsIn('mailSettings', ['from_address'])
        ->assertSessionMissing('_old_input.api_key');
});

test('a saved configuration queues a test for the specified recipient', function () {
    MailSetting::create(['provider' => 'resend', 'api_key' => 'test-secret-key', 'from_address' => 'onboarding@resend.dev']);
    Queue::fake();

    $this->post('/settings/mail/test', ['recipient' => 'recipient@example.com'])
        ->assertSessionHas('mail_test_id');

    Queue::assertPushed(SendMailTest::class, fn (SendMailTest $job) => $job->recipient === 'recipient@example.com' && $job->connection === 'database');
});

test('the queued test sends and sanitizes failures', function () {
    $service = Mockery::mock(MailSettings::class);
    $service->shouldReceive('sendTest')->once()->with('recipient@example.com', 'en');
    (new SendMailTest('recipient@example.com', 'test-id'))->handle($service);

    $service = Mockery::mock(MailSettings::class);
    $service->shouldReceive('sendTest')->once()->andThrow(new RuntimeException('test-secret-key'));
    expect(fn () => (new SendMailTest('recipient@example.com', 'test-id'))->handle($service))
        ->toThrow(RuntimeException::class, 'Falha ao enviar o e-mail de teste.');
});

test('test email requires a saved configuration and a local request', function () {
    $this->post('/settings/mail/test', ['recipient' => 'recipient@example.com'])
        ->assertSessionHasErrorsIn('mailTest', ['recipient']);
    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.2'])
        ->post('/settings/mail/test', ['recipient' => 'recipient@example.com'])->assertForbidden();
});

test('database worker processes the encrypted queued test', function () {
    MailSetting::create(['provider' => 'resend', 'api_key' => 'test-secret-key', 'from_address' => 'onboarding@resend.dev']);
    $service = Mockery::mock(MailSettings::class);
    $service->shouldReceive('sendTest')->once()->with('recipient@example.com', 'en');
    $this->app->instance(MailSettings::class, $service);

    $this->post('/settings/mail/test', ['recipient' => 'recipient@example.com'])->assertSessionHas('mail_test_id');
    $payload = DB::table('jobs')->value('payload');
    expect($payload)->not->toContain('recipient@example.com')->not->toContain('test-secret-key');
    $this->artisan('queue:work', ['connection' => 'database', '--once' => true, '--sleep' => 0])->assertExitCode(0);
    expect(DB::table('jobs')->count())->toBe(0)
        ->and(DB::table('mail_test_results')->value('status'))->toBe('sent');
});

test('test status is accessible locally and reports worker results', function () {
    DB::table('mail_test_results')->insert(['id' => 'test-result', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
    $this->getJson('/settings/mail/test/test-result')->assertOk()->assertJson(['status' => 'pending']);
    (new SendMailTest('recipient@example.com', 'test-result'))->failed(null);
    $this->getJson('/settings/mail/test/test-result')->assertOk()->assertJson(['status' => 'failed']);
    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.2'])->getJson('/settings/mail/test/test-result')->assertForbidden();
});

test('test request queues the selected language', function () {
    MailSetting::create(['provider' => 'resend', 'api_key' => 'test-secret-key', 'from_address' => 'onboarding@resend.dev']);
    Queue::fake();
    $this->post('/settings/mail/test', ['recipient' => 'recipient@example.com', 'locale' => 'en'])->assertSessionHas('mail_test_id');
    Queue::assertPushed(SendMailTest::class, fn (SendMailTest $job) => $job->locale === 'en');
});
