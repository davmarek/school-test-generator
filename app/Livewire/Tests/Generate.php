<?php

namespace App\Livewire\Tests;

use App\Models\Test;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;

class Generate extends Component
{
    // use Spatie\LaravelPdf\Facades\Pdf; // Removed from here

    public Test $test;

    // Config: groupId => count. Key 'ungrouped' for ungrouped.
    public $config = [];

    public $limits = []; // groupId => [min, max, name]

    public $copies = 1;

    public function mount(Test $test)
    {
        Gate::authorize('view', $test);

        $this->test = $test;

        // Eager load relationships to prevent N+1
        $this->test->load(['groups.questions']);

        // Ungrouped
        $ungrouped = $test->questions()->whereNull('group_id')->get();
        $this->limits['ungrouped'] = [
            'min' => $ungrouped->where('is_mandatory', true)->count(),
            'max' => $ungrouped->count(),
            'name' => 'Ungrouped Questions',
        ];
        $this->config['ungrouped'] = $this->limits['ungrouped']['min'];

        // Groups
        foreach ($test->groups as $group) {
            $questions = $group->questions;
            $this->limits[$group->id] = [
                'min' => $questions->where('is_mandatory', true)->count(),
                'max' => $questions->count(),
                'name' => $group->name,
            ];
            $this->config[$group->id] = $this->limits[$group->id]['min'];
        }
    }

    public function generate()
    {
        $this->validateConfiguration();

        $generatedTests = $this->generateTests();

        $pdfName = Str::slug($this->test->name).'-'.now()->format('Y-m-d').'.pdf';

        $pdf = Pdf::loadView('pdf.test', [
            'test' => $this->test,
            'generatedTests' => $generatedTests,
            'totalPoints' => $this->test->max_points,
        ]);

        return response()->streamDownload(fn () => print ($pdf->output()), $pdfName);
    }

    private function validateConfiguration()
    {
        $rules = [
            'copies' => 'required|integer|min:1|max:50',
        ];

        foreach ($this->limits as $key => $limit) {
            $rules["config.$key"] = "required|integer|min:{$limit['min']}|max:{$limit['max']}";
        }
        $this->validate($rules);
    }

    private function generateTests()
    {
        $generatedTests = [];

        for ($i = 0; $i < $this->copies; $i++) {
            $selectedQuestions = $this->selectQuestions();
            $generatedTests[] = $this->calculatePoints($selectedQuestions);
        }

        return $generatedTests;
    }

    private function selectQuestions()
    {
        $selectedQuestions = collect();

        // Ungrouped
        $ungrouped = $this->test->questions()->whereNull('group_id')->get();
        $this->pickQuestions($ungrouped, $this->config['ungrouped'], $selectedQuestions);

        // Groups
        foreach ($this->test->groups as $group) {
            $questions = $group->questions;
            $this->pickQuestions($questions, $this->config[$group->id], $selectedQuestions);
        }

        return $selectedQuestions;
    }

    private function calculatePoints($selectedQuestions)
    {
        // Calculate Points - Integer Distribution (Largest Remainder Method)
        $totalWeight = $selectedQuestions->sum('weight');
        $maxPoints = $this->test->max_points;

        if ($totalWeight > 0) {
            // 1. Calculate raw points and initial integer allocation
            $questionsData = $selectedQuestions->map(function ($q) use ($totalWeight, $maxPoints) {
                $rawPoints = ($q->weight / $totalWeight) * $maxPoints;
                return [
                    'question' => $q,
                    'integer_points' => floor($rawPoints),
                    'fraction' => $rawPoints - floor($rawPoints),
                    'original_weight' => $q->weight
                ];
            });

            // 2. data structure is now a collection of arrays.
            // Calculate used points
            $usedPoints = $questionsData->sum('integer_points');
            $remainder = $maxPoints - $usedPoints;

            // 3. Sort by fraction descending to distribute remainder
            // Let's use weight descending as tie-breaker for fairness (bigger questions get the extra point)
            $sortedKeys = $questionsData->sortByDesc(function ($data) {
                return $data['fraction'] * 100000 + $data['original_weight'];
            })->keys();

            // Convert to array to allow modification
            $questionsDataArray = $questionsData->all();

            // 4. Distribute remainder
            foreach ($sortedKeys as $index => $key) {
                if ($remainder > 0) {
                    $questionsDataArray[$key]['integer_points']++;
                    $remainder--;
                } else {
                    break;
                }
            }

            // 5. Map back to question objects
            return collect($questionsDataArray)->map(function ($data) {
                $q = clone $data['question'];
                $q->calculated_points = (int) $data['integer_points'];

                if ($q->type === \App\Enums\QuestionType::CLOSED) {
                    $q->setRelation('options', $q->options->shuffle());
                }
                return $q;
            })->values();
        }

        return collect();
    }

    private function pickQuestions($pool, $count, &$collection)
    {
        if ($count <= 0) {
            return;
        }

        // Get all mandatory questions
        $mandatory = $pool->where('is_mandatory', true);

        // Add all mandatory (assuming validation ensured count >= mandatory)
        foreach ($mandatory as $q) {
            $collection->push($q);
        }

        // How many more questions are needed?
        $needed = $count - $mandatory->count();
        if ($needed > 0) {
            $not_mandatory = $pool->where('is_mandatory', false);
            if ($not_mandatory->count() >= $needed) {
                // Pick random
                $picked = $not_mandatory->random($needed);
                foreach ($picked as $q) {
                    $collection->push($q);
                }
            } else {
                // Should not happen if max validation passed
                foreach ($not_mandatory as $q) {
                    $collection->push($q);
                }
            }
        }
    }

    public function includeAll()
    {
        foreach ($this->limits as $key => $limit) {
            $this->config[$key] = $limit['max'];
        }
    }

    public function render()
    {
        return view('livewire.tests.generate');
    }
}
