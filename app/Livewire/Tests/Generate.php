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
        // Validation
        $rules = [
            'copies' => 'required|integer|min:1|max:50',
        ];

        foreach ($this->limits as $key => $limit) {
            $rules["config.$key"] = "required|integer|min:{$limit['min']}|max:{$limit['max']}";
        }
        $this->validate($rules);

        // Generate tests
        $generatedTests = [];

        for ($i = 0; $i < $this->copies; $i++) {
            $selectedQuestions = collect();

            // Ungrouped
            $ungrouped = $this->test->questions()->whereNull('group_id')->get();
            $this->pickQuestions($ungrouped, $this->config['ungrouped'], $selectedQuestions);

            // Groups
            foreach ($this->test->groups as $group) {
                $questions = $group->questions;
                $this->pickQuestions($questions, $this->config[$group->id], $selectedQuestions);
            }

            // Calculate Points
            $totalWeight = $selectedQuestions->sum('weight');
            $pointFactor = $totalWeight > 0 ? $this->test->max_points / $totalWeight : 0;

            $questionsWithPoints = $selectedQuestions->map(function ($q) use ($pointFactor) {
                // Clone needed to avoid modifying original instance if reused (stateless HTTP, probably fine, but safer)
                $q = clone $q;
                $q->calculated_points = round($q->weight * $pointFactor, 2);

                if ($q->type === \App\Enums\QuestionType::CLOSED) {
                    // Start shuffle options
                    $q->setRelation('options', $q->options->shuffle());
                }

                return $q;
            });

            // Shuffle questions for extra randomness? User didn't ask, but "different tests" might imply order too.
            // Let's stick to just picking logic for now, but maybe shuffle the final list?
            // The prompt says "random ones", and implicitly we pick randoms.
            // Let's just store this instance.
            $generatedTests[] = $questionsWithPoints;
        }

        $pdfName = Str::slug($this->test->name).'-'.now()->format('Y-m-d').'.pdf';

        $pdf = Pdf::loadView('pdf.test', [
            'test' => $this->test,
            'generatedTests' => $generatedTests,
            'totalPoints' => $this->test->max_points,
        ]);

        return response()->streamDownload(fn () => print ($pdf->output()), $pdfName);
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
