<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

?>

<div class="py-6">
    <div class="flex justify-between items-center mb-6">
        <flux:heading size="xl" level="1">My Tests</flux:heading>
        
        <flux:modal.trigger name="create-test">
            <flux:button variant="primary" icon="plus">Create Test</flux:button>
        </flux:modal.trigger>

    </div>
    <flux:modal name="create-test" class="min-w-[70ch]">
        <form wire:submit="createTest" class="space-y-6">
            <div>
                <flux:heading size="lg">Create Test</flux:heading>
                <flux:text>Start a new test to add questions to.</flux:text>
            </div>

            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input wire:model="name" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Max Points</flux:label>
                <flux:input type="number" wire:model="max_points" />
                <flux:error name="max_points" />
            </flux:field>

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary">Create</flux:button>
            </div>
        </form>
    </flux:modal>

    @if($tests->isEmpty())
        <div class="text-center py-12">
            <flux:heading size="lg">No tests created yet.</flux:heading>
            <flux:text>Get started by creating your first test.</flux:text>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($tests as $test)
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div>
                            <flux:heading size="lg">
                                <a href="{{ route('tests.edit', $test) }}" wire:navigate class="hover:underline">
                                    {{ $test->name }}
                                </a>
                            </flux:heading>
                            <flux:text class="text-sm mt-1">Max Points: {{ $test->max_points }}</flux:text>
                        </div>
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                            <flux:menu>
                                <flux:menu.item icon="pencil-square" :href="route('tests.edit', $test)" wire:navigate>Edit</flux:menu.item>
                                <flux:menu.item icon="trash" variant="danger">Delete</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
