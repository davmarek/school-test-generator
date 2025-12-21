<div class="py-6 max-w-2xl mx-auto">
    <div class="flex items-center gap-4 mb-8">
        <flux:button icon="arrow-left" variant="ghost" :href="route('tests.edit', $test)" wire:navigate>Back</flux:button>
        <flux:heading size="xl">{{ $test->name }}: Generate PDF</flux:heading>
    </div>

    <div
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 shadow-sm space-y-6">
        <flux:heading size="lg" class="mb-4">Configuration</flux:heading>
        <flux:text class="mb-6">Choose how many questions to include from each group. Mandatory questions are
            automatically included.</flux:text>

        <form wire:submit="generate" class="space-y-6">
            <flux:field>
                <flux:label>Number of Copies</flux:label>
                <flux:input type="number" wire:model="copies" min="1" max="50" />
                <flux:error name="copies" />
            </flux:field>

            <flux:separator />

            @foreach ($limits as $key => $limit)
                <flux:field>
                    <flux:label>
                        {{ $limit['name'] }}
                        <span class="text-xs text-zinc-500 font-normal ml-2">
                            (Available: {{ $limit['max'] }}, Mandatory: {{ $limit['min'] }})
                        </span>
                    </flux:label>
                    <flux:input type="number" wire:model="config.{{ $key }}" min="{{ $limit['min'] }}"
                        max="{{ $limit['max'] }}" />
                    <flux:error name="config.{{ $key }}" />
                </flux:field>
            @endforeach

            @if (empty($limits))
                <p class="text-zinc-500">No questions available to generate a test.</p>
            @else
                <div class="pt-4 flex justify-end gap-2">
                    <flux:button type="button" variant="subtle" icon="plus" wire:click="includeAll">Include All
                        Questions</flux:button>
                    <flux:button type="submit" variant="primary" icon="document-arrow-down">Generate & Download PDF
                    </flux:button>
                </div>
            @endif
        </form>
    </div>
</div>
