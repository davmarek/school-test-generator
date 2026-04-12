<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\QuestionGroup;
use App\Models\Test;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'test_id' => Test::factory(),
            'group_id' => null,
            'type' => \App\Enums\QuestionType::OPEN_LONG,
            'text' => $this->faker->sentence(),
            'weight' => $this->faker->numberBetween(1, 10),
            'is_mandatory' => $this->faker->boolean(),
        ];
    }
}
