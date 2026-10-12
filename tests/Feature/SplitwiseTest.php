<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class SplitwiseTest extends TestCase
{
    public function test_user_registration()
    {
        $uniqueEmail = 'test_' . uniqid() . '@example.com';

        $response = $this->postJson('/api/register', [
            'name'                  => 'Test User',
            'email'                 => $uniqueEmail,
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
    }

    public function test_user_login()
    {
        $uniqueEmail = 'login_' . uniqid() . '@example.com';

        User::create([
            'name'      => 'Login User',
            'email'     => $uniqueEmail,
            'password'  => bcrypt('password123'),
            'api_token' => 'testtoken123'
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => $uniqueEmail,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
    }
}