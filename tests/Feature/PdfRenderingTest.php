<?php

use App\Enums\QuestionType;
use App\Models\Test;
use App\Models\User;

it('renders radio buttons for single choice questions', function () {
    $user = User::factory()->create();
    $test = Test::factory()->create(['user_id' => $user->id]);
    $question = $test->questions()->create([
        'type' => QuestionType::CLOSED,
        'text' => 'Single Choice Question',
    ]);

    // Create one correct option
    $question->options()->create(['text' => 'Correct', 'is_correct' => true]);
    $question->options()->create(['text' => 'Wrong', 'is_correct' => false]);

    // Load relationships as they are used in the view
    $test->load('questions.options');

    // Helper to simulate the structure passed to the view
    $generatedTests = collect([
        // Test instance 1
        collect([$question]),
    ]);

    $view = (string) view('pdf.test', [
        'test' => $test,
        'generatedTests' => $generatedTests,
    ]);

    expect($view)->toContain('class="radio"');
    expect($view)->not->toContain('class="checkbox"');
});

it('renders checkboxes for multiple choice questions', function () {
    $user = User::factory()->create();
    $test = Test::factory()->create(['user_id' => $user->id]);
    $question = $test->questions()->create([
        'type' => QuestionType::CLOSED,
        'text' => 'Multiple Choice Question',
    ]);

    // Create two correct options
    $question->options()->create(['text' => 'Correct 1', 'is_correct' => true]);
    $question->options()->create(['text' => 'Correct 2', 'is_correct' => true]);
    $question->options()->create(['text' => 'Wrong', 'is_correct' => false]);

    $test->load('questions.options');

    $generatedTests = collect([
        collect([$question]),
    ]);

    $view = (string) view('pdf.test', [
        'test' => $test,
        'generatedTests' => $generatedTests,
    ]);

    expect($view)->toContain('class="checkbox"');
    expect($view)->not->toContain('class="radio"');
});
