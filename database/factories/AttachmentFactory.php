<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attachment>
 */
class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extension = fake()->randomElement(['jpg', 'png', 'pdf', 'docx']);

        return [
            'file_path' => 'attachments/' . fake()->uuid() . '.' . $extension,
            'file_name' => fake()->word() . '.' . $extension,
            'mime_type' => match ($extension) {
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'pdf' => 'application/pdf',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            },
            'size' => fake()->numberBetween(10240, 5242880), // 10KB to 5MB
            'attachable_type' => Article::class,
            'attachable_id' => Article::factory(),
        ];
    }
}
