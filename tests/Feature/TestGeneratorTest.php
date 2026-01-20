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

it('prefills true false question text', function () {
    $user = User::factory()->create();
    $test = Test::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['test' => $test])
        ->set('questionForm.text', '')
        ->set('questionForm.type', 'true_false')
        ->assertSet('questionForm.text', 'Pravda/neprada? (V tabulce označte křížkem (X), zda je tvrzení pravdivé nebo nepravdivé.)');
});

it('does not overwrite existing question text when switching to true false', function () {
    $user = User::factory()->create();
    $test = Test::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['test' => $test])
        ->set('questionForm.text', 'Existing text')
        ->set('questionForm.type', 'true_false')
        ->assertSet('questionForm.text', 'Existing text');
});

it('dispatches option-added event when adding option', function () {
    $user = User::factory()->create();
    $test = Test::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['test' => $test])
        ->call('addOption')
    ->assertDispatched('option-added');
});

it('calculates integer points correctly', function () {
    Pdf::shouldReceive('loadView')
        ->once()
        ->with('pdf.test', \Mockery::on(function ($data) {
            $questions = $data['generatedTests'][0];
            $sum = $questions->sum('calculated_points');
            $isInteger = $questions->every(fn ($q) => is_int($q->calculated_points));
            
            return $sum === 100 && $isInteger;
        }))
        ->andReturnSelf();

    Pdf::shouldReceive('output')->andReturn('PDF CONTENT');

    $user = User::factory()->create();
    $test = Test::factory()->create(['user_id' => $user->id, 'max_points' => 100]);
    
    // Create 3 questions with equal weight. 100 / 3 = 33.33
    // Should be distributed as 34, 33, 33 (or similar) summing to 100.
    $questions = \App\Models\Question::factory()->count(3)->create([
        'test_id' => $test->id,
        'weight' => 1,
        'is_mandatory' => true,
        'type' => 'open',
    ]);

    Livewire::actingAs($user)
        ->test(Generate::class, ['test' => $test])
        ->set('config.ungrouped', 3)
        ->call('generate');
});
