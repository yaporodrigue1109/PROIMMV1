<?php

namespace Tests\Feature;

use Tests\TestCase;

class MobileApiRoutesTest extends TestCase
{
    public function test_mobile_registration_validates_the_payload(): void
    {
        $this->postJson('/api/mobile/auth/locataire/register', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'phone',
                'type_piece_id',
                'num_piece',
                'password',
            ]);
    }

    public function test_mobile_login_validates_the_payload(): void
    {
        $this->postJson('/api/mobile/auth/proprietaire/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone', 'password']);
    }

    public function test_protected_mobile_route_requires_a_bearer_token(): void
    {
        $this->getJson('/api/mobile/locataire/agencies')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Jeton absent, invalide ou expiré.');
    }

    public function test_unknown_mobile_role_is_not_routable(): void
    {
        $this->postJson('/api/mobile/auth/agence/login', [])
            ->assertNotFound();
    }
}
