<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_all_registration_fields_are_required(): void
    {
        $response = $this->post('/register', []);

        $response->assertSessionHasErrors([
            'name',
            'phone',
            'email',
            'password',
            'password_confirmation',
            'personal_data_consent',
        ]);

        $this->assertGuest();
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Тестовый пользователь',
            'phone' => '+7 (999) 123-45-67',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'personal_data_consent' => true,
        ]);

        $this->assertAuthenticated();

        $response->assertRedirect('/verify-email');

        $this->assertDatabaseHas('users', [
            'name' => 'Тестовый пользователь',
            'phone' => '+79991234567',
            'email' => 'test@example.com',
        ]);
    }
}
