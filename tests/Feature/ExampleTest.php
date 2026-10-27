<?php

use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_owner' => true]));
});

it('returns a successful response for the home page', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});
