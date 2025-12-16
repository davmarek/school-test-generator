<div class="pt-4 pb-6">
    <flux:button icon="arrow-left" variant="ghost" :href="route('tests.index')" class="mb-6" wire:navigate>Back</flux:button>
    <div class="flex items-center gap-4 mb-6">
        <div class="flex-1">
            <h1 class="text-2xl font-bold">{{ $test->name }}</h1>
            <p class="text-sm text-zinc-500">Max Points: {{ $test->max_points }}</p>
        </div>
        <flux:modal.trigger name="edit-test">
            <flux:button icon="pencil-square">Edit Details</flux:button>
        </flux:modal.trigger>
        <flux:button variant="primary" :href="route('tests.generate', $test)" wire:navigate>Generate PDF</flux:button>
    </div>

    <!-- Edit Test Modal -->
    <flux:modal name="edit-test" class="min-w-[22rem]">
        <form wire:submit="updateTest" class="space-y-6">
            <flux:heading size="lg">Edit Test Details</flux:heading>
            <flux:input label="Name" wire:model="name" />
            <flux:input label="Max Points" type="number" wire:model="max_points" />
            <div class="flex justify-end">
                <flux:button type="submit" variant="primary">Save</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Create Group Modal -->
    <flux:modal name="create-group" class="min-w-[22rem]">
        <form wire:submit="createGroup" class="space-y-6">
            <flux:heading size="lg">Create Question Group</flux:heading>
            <flux:input label="Group Name" wire:model="newGroupName" />
            <div class="flex justify-end">
                <flux:button type="submit" variant="primary">Create Group</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Question Modal -->
    <flux:modal name="question-modal" class="min-w-[50ch] space-y-6">
        <flux:heading size="lg">{{ $questionForm['id'] ? 'Edit Question' : 'Add Question' }}</flux:heading>
        
        <form wire:submit="saveQuestion" class="space-y-4">
            <flux:radio.group label="Type" wire:model.live="questionForm.type" variant="segmented">
                <flux:radio value="open" label="Open Answer" icon="pencil"/>
                <flux:radio value="closed" label="Closed Options" icon="list-bullet" />
                <flux:radio value="true_false" label="True / False" icon="scale" />
            </flux:radio.group>

            <flux:textarea label="Question Text" wire:model="questionForm.text" rows="3" />
            
            
                <flux:input type="number" label="Weight" wire:model="questionForm.weight" min="1" max="5" />
                <flux:checkbox label="Mandatory" wire:model="questionForm.is_mandatory"  />
            

            @if(in_array($questionForm['type'], ['closed', 'true_false']))
                <flux:separator />
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <flux:heading size="sm">Options</flux:heading>
                        <flux:button size="sm" icon="plus" wire:click="addOption">Add Option</flux:button>
                    </div>

                    @foreach($questionForm['options'] as $index => $option)
                         <div class="flex gap-2 items-center" wire:key="option-{{ $index }}">
                            <flux:checkbox wire:model="questionForm.options.{{ $index }}.is_correct" />
                            <flux:input class="flex-1" wire:model="questionForm.options.{{ $index }}.text" placeholder="Option text..." />
                            <flux:button icon="trash" variant="ghost" size="sm" class="text-red-500" wire:click="removeOption({{ $index }})" />
                        </div>
                    @endforeach
                     <flux:error name="questionForm.options.*.text" />
                </div>
            @endif

             <div class="flex justify-end mt-4">
                <flux:button type="submit" variant="primary">Save Question</flux:button>
            </div>
        </form>
    </flux:modal>

    <div class="space-y-8">
        <!-- Ungrouped Questions -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 shadow-sm space-y-4">
            <div class="flex justify-between items-center">
                <flux:heading size="lg">Ungrouped Questions</flux:heading>
                <flux:button size="sm" icon="plus" wire:click="openQuestionModal(null)">Add Question</flux:button>
            </div>
            
            @if($ungroupedQuestions->isEmpty())
                <p class="text-zinc-500 text-sm">No questions in this section.</p>
            @else
                <div class="space-y-2">
                    @foreach($ungroupedQuestions as $question)
                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg" wire:key="q-{{ $question->id }}">
                            <div class="flex-1 grid grid-cols-3 place-items-start items-center gap-4">
                                <p class="font-medium ">{{ $question->text }}</p>
                                <div>
                                    <flux:badge size="sm">{{ $question->type->label() }}</flux:badge>
                                    @if($question->is_mandatory)
                                    <flux:badge size="sm" color="amber">Mandatory</flux:badge>
                                    @endif
                                </div>
                                <span class="text-xs text-zinc-500">Weight: {{ $question->weight }}</span>
                            </div>
                            <div class="flex gap-2">
                                <flux:button icon="pencil" size="sm" variant="ghost" wire:click="openQuestionModal(null, {{ $question->id }})" />
                                <flux:button icon="trash" size="sm" variant="ghost" class="text-red-500" wire:click="deleteQuestion({{ $question->id }})" wire:confirm="Are you sure?" />
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Groups -->
        @foreach($groups as $group)
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 shadow-sm space-y-4" wire:key="group-{{ $group->id }}">
                <div class="flex justify-between items-center">
                    <div>
                        <flux:heading size="lg">{{ $group->name }}</flux:heading>
                    </div>
                    <div class="flex gap-2">
                        <flux:button size="sm" icon="plus" wire:click="openQuestionModal({{ $group->id }})">Add Question</flux:button>
                        <flux:button size="sm" icon="trash" variant="ghost" class="text-red-500" wire:click="deleteGroup({{ $group->id }})" wire:confirm="Delete group and its questions?" />
                    </div>
                </div>

                @if($group->questions->isEmpty())
                    <p class="text-zinc-500 text-sm">No questions in this group.</p>
                @else
                    <div class="space-y-2">
                        @foreach($group->questions as $question)
                            <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg" wire:key="q-{{ $question->id }}">
                                <div class="flex-1 grid grid-cols-3 place-items-start items-center gap-4">
                                    <p class="font-medium">{{ $question->text }}</p>
                                    <div>
                                        <flux:badge size="sm">{{ $question->type->label() }}</flux:badge>
                                        @if($question->is_mandatory)
                                            <flux:badge size="sm" color="amber">Mandatory</flux:badge>
                                        @endif
                                    </div>
                                    <span class="text-xs text-zinc-500">Weight: {{ $question->weight }}</span>
                                </div>
                                <div class="flex gap-2">
                                    <flux:button icon="pencil" size="sm" variant="ghost" wire:click="openQuestionModal({{ $group->id }}, {{ $question->id }})" />
                                    <flux:button icon="trash" size="sm" variant="ghost" class="text-red-500" wire:click="deleteQuestion({{ $question->id }})" wire:confirm="Are you sure?" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

        <div class="flex justify-center">
            <flux:modal.trigger name="create-group">
                <flux:button variant="subtle" icon="plus">Add Question Group</flux:button>
            </flux:modal.trigger>
        </div>
    </div>
</div>
