<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'file_name' => $title.'.pdf',
            'file_path' => 'documents/'.fake()->uuid().'.pdf',
            'file_size' => fake()->numberBetween(1000, 500000),
            'page_count' => fake()->numberBetween(1, 50),
            'status' => 'ready',
        ];
    }

    /**
     * Indicate that the document has no stored file, so the `file_url`
     * accessor resolves to null without reaching the storage disk.
     */
    public function withoutFile(): static
    {
        return $this->state(fn (array $attributes): array => [
            'file_path' => null,
        ]);
    }
}
