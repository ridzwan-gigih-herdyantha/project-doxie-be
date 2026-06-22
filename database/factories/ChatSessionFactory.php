<?php

namespace Database\Factories;

use App\Models\ChatSession;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatSession>
 */
class ChatSessionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'document_id' => Document::factory(),
            'title' => fake()->sentence(3),
        ];
    }
}
