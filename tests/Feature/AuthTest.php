<?php

namespace Tests\Feature;

use App\Models\AuditEvent;
use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Sueldos');
        $response->assertSee('Iniciar Sesión');
        $response->assertDontSee('Password123!');
        $response->assertDontSee('Acceso Rápido de Prueba');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/employers');
        $response->assertRedirect('/login');
    }

    public function test_operator_can_authenticate_and_triggers_audit_event(): void
    {
        $operator = User::where('email', 'admin@estudio.test')->first();
        $this->assertNotNull($operator);

        $response = $this->post('/login', [
            'email' => 'admin@estudio.test',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/employers');
        $this->assertAuthenticatedAs($operator);

        $this->assertDatabaseHas('audit_events', [
            'user_id' => $operator->id,
            'action' => 'LOGIN',
            'entity_type' => 'User',
        ]);
    }

    public function test_operator_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@estudio.test',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_authenticated_operator_can_logout(): void
    {
        $operator = User::first();
        $this->actingAs($operator);

        $response = $this->post('/logout');
        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_inactive_operator_cannot_authenticate(): void
    {
        $operator = User::where('email', 'operador1@estudio.test')->firstOrFail();
        $operator->update(['status' => 'INACTIVE']);

        $response = $this->post('/login', [
            'email' => $operator->email,
            'password' => 'Password123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }
}
