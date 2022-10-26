<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Lang;
use Storage;
use Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class DocumentControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $domain = 'demo.uat-dms.localhost:3000';

    public function test_accessing_the_document_list_page_should_successfully_display_the_page()
    {
        $authUser = User::factory()->make();

        $response = $this->actingAs($authUser)->get('/api/documents');

        $response->assertOk();
    }

    public function test_uploading_new_document_should_save_the_data_in_database_and_return_the_details_of_the_uploaded_document()
    {
        $authUser = User::where('username', 'superadmin')->first();

        Storage::fake('avatars');

        $filename = 'avatar.zip';

        $payload = [
            'document' => UploadedFile::fake()->create($filename, 100, 'application/zip')
        ];

        $response = $this->actingAs($authUser)->post('/api/documents', $payload, [
            'domain' => $this->domain,
            'Content-Type' => 'multipart/form-data'
        ]);

        $response->assertCreated();

        $this->assertEquals($response['message'], Lang::get('success.uploaded'));
        $this->assertEquals($response['result']['filename'], $filename);
    }
}
