<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UserTest extends TestCase
{

    /**
     * This test is interacting with the API endpoint directly using Laravel's testing utilities. 
     * It checks the response for validation errors when no data is provided, which is typical for a feature test 
     * where we are testing how the application behaves under expected user interactions through the endpoints.
     */
    public function test_user_registration_with_empty_request_data_error_checking_structure_and_validation_with_messages_from_response_also_will_show_if_authentication_service_is_available(): void
    {
        $response = $this->postJson('/api/register');
        $response
            ->assertStatus(422)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->hasAll(['status', 'source', 'message', 'error', 'error.type', 'error.details'])
                    ->where('error.details.username', ['The username field is required.'])
                    ->where('error.details.full_name', ['The full name field is required.'])
                    ->where('error.details.email', ['The email field is required.'])
                    ->where('error.details.password', ['The password field is required.']);
            });
    }

    /**
     * Similar to the first, this test interacts directly with the API endpoint and validates the response against specific validation rules. 
     * It check how the application handles and responds to incorrect inputs.
     */
    public function test_registration_invalid_email_invalid_password_validation_failed(): void
    {
        $this->postJson('/api/register', [
            'email' => 'milosgoogle.com',
            'username' => 'MilosKec',
            'full_name' => 'Milos Kecman',
            'is_admin' => true, // 'is_admin' is optional and defaults to 'false
            'password' => 'ABCdef'
        ])
            ->assertStatus(422)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->hasAll(['status', 'source', 'message', 'error', 'error.type', 'error.details'])
                    ->where('error.details.password', [
                        'The password field must be at least 8 characters.',
                        'The password field confirmation does not match.',
                        'The password field format is invalid.',
                    ])
                    ->where('error.details.email', ['The email field must be a valid email address.']);
            });
    }
}
