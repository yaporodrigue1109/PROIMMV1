<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicContactTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('contact_messages');
        Schema::create('contact_messages', function (Blueprint $table): void {
            $table->uuid('contact_message_id')->primary();
            $table->string('request_type');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('status')->default('new');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->string('processed_by')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('contact_messages');

        parent::tearDown();
    }

    public function test_public_contact_form_stores_a_message(): void
    {
        $response = $this->from('/contact')->post('/contact', [
            'request_type' => 'demo',
            'name' => 'Awa Koné',
            'email' => 'awa@example.com',
            'phone' => '+2250700000000',
            'subject' => 'Demande de démonstration',
            'message' => 'Je souhaite organiser une démonstration de la plateforme.',
        ]);

        $response->assertRedirect('/contact')->assertSessionHas('success');
        $this->assertDatabaseHas('contact_messages', [
            'request_type' => 'demo',
            'name' => 'Awa Koné',
            'email' => 'awa@example.com',
            'subject' => 'Demande de démonstration',
            'status' => 'new',
        ]);
    }

    public function test_public_contact_form_validates_required_fields(): void
    {
        $response = $this->from('/contact')->post('/contact', []);

        $response->assertRedirect('/contact');
        $response->assertSessionHasErrors(['request_type', 'name', 'email', 'subject', 'message']);
        $this->assertDatabaseCount('contact_messages', 0);
    }
}
