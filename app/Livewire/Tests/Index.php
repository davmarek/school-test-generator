<?php

namespace App\Livewire\Tests;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Livewire component for viewing and creating tests.
 */
class Index extends Component
{
    #[Validate('required|string|min:1|max:255')]
    public $name = '';

    #[Validate('required|integer|min:1')]
    public $max_points = 100;

    public function createTest()
    {
        $this->validate();

        Auth::user()->tests()->create([
            'name' => $this->name,
            'max_points' => $this->max_points,
        ]);

        $this->reset(['name', 'max_points']);
        $this->modal('create-test')->close();
    }

    public function render()
    {
        return view('livewire.tests.index', [
            'tests' => Auth::user()->tests()->latest()->get(),
        ]);
    }
}
