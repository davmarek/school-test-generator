<?php

use App\Livewire\Tests\Edit;
use App\Livewire\Tests\Generate;
use App\Livewire\Tests\Index;
use App\Models\Test;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Livewire;

it('displays tests on index', function () {
    $user = User::factory()->create();
    $test = Test::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('tests.index'))
        ->assertSee($test->name)
        ->assertSee(route('tests.edit', $test));
});

it('can create a test', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('name', 'New Math Test')
        ->set('max_points', 100)
        ->call('createTest');

    expect(Test::where('name', 'New Math Test')->exists())->toBeTrue();
});

it('can create a question group', function () {
    $user = User::factory()->create();
    $test = Test::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['test' => $test])
        ->set('newGroupName', 'Algebra')
        ->call('createGroup');

    expect($test->groups()->where('name', 'Algebra')->exists())->toBeTrue();
});

it('can create a question', function () {
    $user = User::factory()->create();
    $test = Test::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['test' => $test])
        ->set('questionForm', [
            'id' => null,
            'group_id' => null,
            'type' => 'open',
            'text' => 'What is 2+2?',
            'weight' => 5,
            'is_mandatory' => true,
            'options' => [],
        ])
        ->call('saveQuestion');

    expect($test->questions()->where('text', 'What is 2+2?')->exists())->toBeTrue();
});

it('generates pdf', function () {
    Pdf::shouldReceive('loadView')
        ->once()
        ->andReturnSelf();

    Pdf::shouldReceive('output')->andReturn('PDF CONTENT');

    $user = User::factory()->create();
    $test = Test::factory()->create(['user_id' => $user->id]);
    $question = $test->questions()->create([
        'type' => 'open',
        'text' => 'Q1',
        'weight' => 10,
        'is_mandatory' => true,
    ]);

    Livewire::actingAs($user)
        ->test(Generate::class, ['test' => $test])
        ->set('config.ungrouped', 1)
        ->call('generate');
});
