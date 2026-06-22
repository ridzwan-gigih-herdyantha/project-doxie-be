<?php

namespace Tests\Feature;

use App\Models\ChatSession;
use App\Models\Document;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UuidExposureTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_public_model_generates_a_uuid_while_keeping_the_integer_id(): void
    {
        $user = User::factory()->create();
        $document = Document::factory()->for($user)->create();
        $chatSession = ChatSession::factory()->for($user)->for($document)->create();
        $message = Message::factory()->for($chatSession)->create();

        foreach ([$user, $document, $chatSession, $message] as $model) {
            $this->assertTrue(Str::isUuid($model->uuid), $model::class.' should have a valid uuid');
            $this->assertIsInt($model->id, $model::class.' should keep an integer primary key');
            $this->assertSame('uuid', $model->getRouteKeyName());
        }
    }

    public function test_id_and_foreign_keys_are_hidden_in_serialized_output(): void
    {
        $document = Document::factory()->create();
        $message = Message::factory()->create();

        $documentArray = $document->toArray();
        $this->assertArrayHasKey('uuid', $documentArray);
        $this->assertArrayNotHasKey('id', $documentArray);
        $this->assertArrayNotHasKey('user_id', $documentArray);

        $messageArray = $message->toArray();
        $this->assertArrayHasKey('uuid', $messageArray);
        $this->assertArrayNotHasKey('id', $messageArray);
        $this->assertArrayNotHasKey('chat_session_id', $messageArray);
    }

    public function test_document_is_resolved_by_uuid_in_the_api(): void
    {
        $user = User::factory()->create();
        $document = Document::factory()->for($user)->withoutFile()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/documents/{$document->uuid}");

        $response->assertOk()
            ->assertJsonPath('data.uuid', $document->uuid)
            ->assertJsonMissingPath('data.id')
            ->assertJsonMissingPath('data.user_id');
    }

    public function test_a_non_uuid_document_route_key_returns_404(): void
    {
        $user = User::factory()->create();
        Document::factory()->for($user)->withoutFile()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/documents/1')->assertNotFound();
    }

    public function test_chat_sessions_expose_the_document_uuid_not_internal_ids(): void
    {
        $user = User::factory()->create();
        $document = Document::factory()->for($user)->create();
        $chatSession = ChatSession::factory()->for($user)->for($document)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/sessions');

        $response->assertOk()
            ->assertJsonPath('data.data.0.uuid', $chatSession->uuid)
            ->assertJsonPath('data.data.0.document_uuid', $document->uuid)
            ->assertJsonMissingPath('data.data.0.id')
            ->assertJsonMissingPath('data.data.0.user_id')
            ->assertJsonMissingPath('data.data.0.document_id');
    }

    public function test_messages_expose_uuid_not_id(): void
    {
        $user = User::factory()->create();
        $document = Document::factory()->for($user)->create();
        $chatSession = ChatSession::factory()->for($user)->for($document)->create();
        $message = Message::factory()->for($chatSession)->create(['role' => 'user']);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/session/{$chatSession->uuid}/messages");

        $response->assertOk()
            ->assertJsonPath('data.0.uuid', $message->uuid)
            ->assertJsonMissingPath('data.0.id')
            ->assertJsonMissingPath('data.0.chat_session_id');
    }
}
