<?php

use App\Models\Test;
use App\Models\User;

test('user cannot view another users test', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $test = Test::factory()->create(['user_id' => $userA->id]);

    $this->actingAs($userB)
        ->get(route('tests.generate', $test))
        ->assertForbidden();
});

test('user cannot edit another users test', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $test = Test::factory()->create(['user_id' => $userA->id]);

    $this->actingAs($userB)
        ->get(route('tests.edit', $test))
        ->assertForbidden();
});

test('user can view own test', function () {
    $user = User::factory()->create();
    $test = Test::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('tests.generate', $test))
        ->assertSuccessful();
});

test('user can edit own test', function () {
    $user = User::factory()->create();
    $test = Test::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('tests.edit', $test))
        ->assertSuccessful();
});
