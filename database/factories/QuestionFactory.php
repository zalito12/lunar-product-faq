<?php

namespace factories;

use Gongarce\ProductFaq\Models\Question;
use Lunar\Database\Factories\BaseFactory;

/**
 * @extends \Lunar\Database\Factories\BaseFactory<\App\Models\Question>
 */
class QuestionFactory extends BaseFactory
{
    protected $model = Question::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'text' => collect([
                'en' => $this->faker->name(),
            ]),
            'answer' => collect([
                'en' => $this->faker->realText(),
            ]),
        ];
    }
}
