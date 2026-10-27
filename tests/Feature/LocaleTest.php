<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_owner' => true]));
});

test('saved language takes precedence over browser language in the initial response', function () {
    $this->withUnencryptedCookie('larablogs_locale', 'en')
        ->withHeader('Accept-Language', 'pt-BR,pt;q=0.9')
        ->get('/')
        ->assertOk()
        ->assertSee('<html lang="en"', false)
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'en'));
});

test('initial response uses a supported browser language when no language is saved', function () {
    $this->withHeader('Accept-Language', 'es-ES,es;q=0.9,en;q=0.8')
        ->get('/')
        ->assertOk()
        ->assertSee('<html lang="es"', false)
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'es'));
});

test('invalid saved languages fall back to the supported browser language', function () {
    $this->withUnencryptedCookie('larablogs_locale', 'invalid')
        ->withHeader('Accept-Language', 'pt-BR,pt;q=0.9')
        ->get('/')
        ->assertOk()
        ->assertSee('<html lang="pt-BR"', false)
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'pt-BR'));
});
