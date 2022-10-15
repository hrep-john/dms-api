<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Lang;
use Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_accessing_the_user_list_page_should_successfully_display_the_page()
    {
        $authUser = User::factory()->make();

        $response = $this->actingAs($authUser)->get('/api/users');

        $response->assertOk();
    }

    public function test_storing_an_admin_user_with_a_required_input_should_save_the_data_in_database_and_return_the_the_details_of_the_saved_user()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertCreated();
        $this->assertRequiredPayload($response, $payload);
    }

    public function test_storing_an_encoder_user_with_a_required_input_should_save_the_data_in_database_and_return_the_the_details_of_the_saved_user()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['roles'] = ['Encoder'];

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertCreated();
        $this->assertRequiredPayload($response, $payload);
    }

    public function test_storing_a_user_with_a_middle_name_input_should_save_the_data_in_database_and_return_the_the_details_of_the_saved_user()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['user_info']['middle_name'] = 'johndoe';

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertCreated();
        $this->assertEquals($response['result']['user_info']['middle_name'], $payload['user_info']['middle_name']);
    }

    public function test_storing_a_user_with_a_mobile_number_input_should_save_the_data_in_database_and_return_the_the_details_of_the_saved_user()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['user_info']['mobile_number'] = '09123456789';

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertCreated();
        $this->assertEquals($response['result']['user_info']['mobile_number'], $payload['user_info']['mobile_number']);
    }

    public function test_storing_a_user_with_a_birthday_input_should_save_the_data_in_database_and_return_the_the_details_of_the_saved_user()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['user_info']['birthday'] = '2000-01-01';

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertCreated();
        $this->assertEquals($response['result']['user_info']['birthday'], $payload['user_info']['birthday']);
    }

    public function test_storing_a_user_with_a_male_gender_input_should_save_the_data_in_database_and_return_the_the_details_of_the_saved_user()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['user_info']['sex'] = 'male';

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertCreated();
        $this->assertEquals($response['result']['user_info']['sex'], $payload['user_info']['sex']);
    }

    public function test_storing_a_user_with_a_female_gender_input_should_save_the_data_in_database_and_return_the_the_details_of_the_saved_user()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['user_info']['sex'] = 'female';

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertCreated();
        $this->assertEquals($response['result']['user_info']['sex'], $payload['user_info']['sex']);
    }

    public function test_storing_a_user_with_a_home_address_input_should_save_the_data_in_database_and_return_the_the_details_of_the_saved_user()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['user_info']['home_address'] = 'loremlorem lorem lorem';

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertCreated();
        $this->assertEquals($response['result']['user_info']['home_address'], $payload['user_info']['home_address']);
    }

    public function test_storing_a_user_with_a_barangay_input_should_save_the_data_in_database_and_return_the_the_details_of_the_saved_user()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['user_info']['barangay'] = 'loremlorem lorem lorem';

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertCreated();
        $this->assertEquals($response['result']['user_info']['barangay'], $payload['user_info']['barangay']);
    }

    public function test_storing_a_user_with_a_city_input_should_save_the_data_in_database_and_return_the_the_details_of_the_saved_user()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['user_info']['city'] = 'loremlorem lorem lorem';

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertCreated();
        $this->assertEquals($response['result']['user_info']['city'], $payload['user_info']['city']);
    }

    public function test_storing_a_user_with_a_region_input_should_save_the_data_in_database_and_return_the_the_details_of_the_saved_user()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['user_info']['region'] = 'loremlorem lorem lorem';

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertCreated();
        $this->assertEquals($response['result']['user_info']['region'], $payload['user_info']['region']);
    }

    public function test_storing_a_user_with_a_more_than_fifty_characters_username_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['username'] = Str::random(51);

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_an_existing_username_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['username'] = User::first()->username;

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_an_integer_username_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['username'] = 123123123;

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_an_undefined_username_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        unset($payload['username']);

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_an_existing_email_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['email'] = User::first()->email;

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_an_integer_email_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['email'] = 123123123;

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_a_string_email_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['email'] = '123123123';

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_an_undefined_email_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        unset($payload['email']);

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_a_more_than_255_characters_email_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['email'] = Str::random(256);

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_a_more_than_25_characters_password_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['password'] = Str::random(26);

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_a_less_than_6_characters_password_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['password'] = Str::random(5);

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_an_undefined_password_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        unset($payload['password']);

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_invalid_role_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['roles'] = ['random'];

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_an_undefined_roles_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        unset($payload['roles']);

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_an_undefined_user_info_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        unset($payload['user_info']);

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_an_undefined_tenant_id_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        unset($payload['user_info']['tenant_id']);

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_invalid_tenant_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['user_info']['tenant_id'] = 'asd';

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_an_undefined_first_name_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        unset($payload['user_info']['first_name']);

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_an_integer_first_name_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['user_info']['first_name'] = 123123123;

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_a_more_than_255_characters_first_name_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['user_info']['first_name'] = Str::random(256);

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_an_undefined_last_name_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        unset($payload['user_info']['last_name']);

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_an_integer_last_name_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['user_info']['last_name'] = 123123123;

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_storing_a_user_with_a_more_than_255_characters_last_name_input_should_not_save_the_data_in_database_and_return_a_http_302_found()
    {
        $authUser = User::factory()->make();

        $payload = $this->getRequiredPayload();
        $payload['user_info']['last_name'] = Str::random(256);

        $response = $this->actingAs($authUser)->post('/api/users', $payload);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    protected function getRequiredPayload() 
    {
        $user = User::factory()->make();
        $tenant = Tenant::first();

        return [
            'email' => $user->email,
            'username' => $user->username,
            'password' => $user->password,
            'roles' => [
                'admin'
            ],
            'user_info' => [
                'tenant_id' => $tenant->id,
                'first_name' => 'first dummy',
                'last_name' => 'test last',
            ]
        ];
    }

    protected function assertRequiredPayload($response, $payload)
    {
        $this->assertEquals($response['message'], Lang::get('success.created'));
        $this->assertEquals($response['result']['email'], $payload['email']);
        $this->assertEquals($response['result']['username'], $payload['username']);
        $this->assertEquals($response['result']['roles'], $payload['roles']);
        $this->assertEquals($response['result']['user_info']['tenant_id'], $payload['user_info']['tenant_id']);
        $this->assertEquals($response['result']['user_info']['first_name'], $payload['user_info']['first_name']);
        $this->assertEquals($response['result']['user_info']['last_name'], $payload['user_info']['last_name']);
    }
}
