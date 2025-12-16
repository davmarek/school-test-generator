<?php

namespace App\Livewire\Tests;

use Livewire\Component;

class Edit extends Component
{
    public \App\Models\Test $test;

    // Test Details
    public $name = '';

    public $max_points = 0;

    // Group Management
    public $newGroupName = '';

    // Question Modal
    public $editingQuestion = null;

    public $questionForm = [
        'id' => null,
        'group_id' => null,
        'type' => 'open',
        'text' => '',
        'weight' => 1,
        'is_mandatory' => false,
        'options' => [], // For closed questions
    ];

    public function mount(\App\Models\Test $test)
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $test);

        $this->test = $test;
        $this->name = $test->name;
        $this->max_points = $test->max_points;
    }

    public function updateTest()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'max_points' => 'required|integer|min:1',
        ]);

        $this->test->update([
            'name' => $this->name,
            'max_points' => $this->max_points,
        ]);

        $this->dispatch('start-update-test', false); // Close edit state if applicable
        // Or just notify
    }

    public function createGroup()
    {
        $this->validate(['newGroupName' => 'required|string|max:255']);

        $this->test->groups()->create(['name' => $this->newGroupName]);
        $this->reset('newGroupName');
        $this->modal('create-group')->close();
    }

    public function deleteGroup($id)
    {
        $this->test->groups()->where('id', $id)->delete();
    }

    public function deleteQuestion($id)
    {
        $this->test->questions()->where('id', $id)->delete();
    }

    public function openQuestionModal($groupId = null, $questionId = null)
    {
        $this->resetValidation();

        // Handle null/empty string group_id for strict typing if needed,
        // but let's assume null is fine for ungrouped.
        // Also ensure groupId is passed as null if empty string.
        $groupId = $groupId === '' ? null : $groupId;

        if ($questionId) {
            $question = $this->test->questions()->with('options')->findOrFail($questionId);
            $this->questionForm = [
                'id' => $question->id,
                'group_id' => $question->group_id,
                'type' => $question->type->value,
                'text' => $question->text,
                'weight' => $question->weight,
                'is_mandatory' => $question->is_mandatory,
                'options' => $question->options->map(fn ($o) => ['id' => $o->id, 'text' => $o->text, 'is_correct' => $o->is_correct])->toArray(),
            ];
        } else {
            $this->questionForm = [
                'id' => null,
                'group_id' => $groupId,
                'type' => 'open',
                'text' => '',
                'weight' => 1,
                'is_mandatory' => false,
                'options' => [],
            ];
        }
        $this->modal('question-modal')->show();
    }

    public function addOption()
    {
        $this->questionForm['options'][] = ['id' => null, 'text' => '', 'is_correct' => false];
    }

    public function removeOption($index)
    {
        unset($this->questionForm['options'][$index]);
        $this->questionForm['options'] = array_values($this->questionForm['options']);
    }

    public function saveQuestion()
    {
        $this->validate([
            'questionForm.text' => 'required|string',
            'questionForm.weight' => 'required|integer|min:1',
            'questionForm.type' => 'required|in:open,closed,true_false',
            'questionForm.options.*.text' => 'required_if:questionForm.type,closed,true_false|string',
        ]);

        $data = $this->questionForm;

        if ($data['id']) {
            $question = $this->test->questions()->findOrFail($data['id']);
            $question->update([
                'group_id' => $data['group_id'],
                'type' => $data['type'],
                'text' => $data['text'],
                'weight' => $data['weight'],
                'is_mandatory' => $data['is_mandatory'],
            ]);
        } else {
            $question = $this->test->questions()->create([
                'group_id' => $data['group_id'],
                'type' => $data['type'],
                'text' => $data['text'],
                'weight' => $data['weight'],
                'is_mandatory' => $data['is_mandatory'],
            ]);
        }

        // Sync Options
        if (in_array($data['type'], ['closed', 'true_false'])) {
            $inputOptions = collect($data['options']);
            $existingIds = $inputOptions->pluck('id')->filter();

            // Delete removed options
            $question->options()->whereNotIn('id', $existingIds)->delete();

            // Update or Create
            foreach ($data['options'] as $opt) {
                if (isset($opt['id']) && $opt['id']) {
                    $question->options()->where('id', $opt['id'])->update([
                        'text' => $opt['text'],
                        'is_correct' => $opt['is_correct'],
                    ]);
                } else {
                    $question->options()->create([
                        'text' => $opt['text'],
                        'is_correct' => $opt['is_correct'],
                    ]);
                }
            }
        } else {
            $question->options()->delete();
        }

        $this->modal('question-modal')->close();
    }

    public function render()
    {
        return view('livewire.tests.edit', [
            'groups' => $this->test->groups()->with('questions')->get(),
            'ungroupedQuestions' => $this->test->questions()->whereNull('group_id')->get(),
        ]);
    }
}
