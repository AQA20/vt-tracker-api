<?php

namespace Tests\Feature;

use App\Models\Unit;
use App\Models\User;
use App\Models\WIRUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WIRUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->user = User::factory()->create();
        $this->unit = Unit::factory()->create();
    }

    public function test_user_can_upload_installation_wir(): void
    {
        $file = UploadedFile::fake()->image('installation.jpg', 800, 600);

        $response = $this->actingAs($this->user)->postJson(
            "/api/units/{$this->unit->id}/wir-uploads",
            [
                'file' => $file,
                'progress_group' => 'installation',
            ]
        );

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'unit_id',
                    'progress_group',
                    'file_path',
                    'file_name',
                    'file_size',
                    'uploaded_by',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.progress_group', 'installation')
            ->assertJsonPath('data.file_name', 'installation.jpg');

        // Verify file was stored
        Storage::disk('public')->assertExists($response->json('data.file_path'));

        // Verify database record
        $this->assertDatabaseHas('wir_uploads', [
            'unit_id' => $this->unit->id,
            'progress_group' => 'installation',
            'file_name' => 'installation.jpg',
        ]);
    }

    public function test_user_can_upload_commissioning_wir(): void
    {
        $file = UploadedFile::fake()->image('commissioning.png', 800, 600);

        $response = $this->actingAs($this->user)->postJson(
            "/api/units/{$this->unit->id}/wir-uploads",
            [
                'file' => $file,
                'progress_group' => 'commissioning',
            ]
        );

        $response->assertStatus(201)
            ->assertJsonPath('data.progress_group', 'commissioning');

        $this->assertDatabaseHas('wir_uploads', [
            'unit_id' => $this->unit->id,
            'progress_group' => 'commissioning',
        ]);
    }

    public function test_upload_webp_file_is_accepted(): void
    {
        $file = UploadedFile::fake()->image('installation.webp', 800, 600, 'webp');

        $response = $this->actingAs($this->user)->postJson(
            "/api/units/{$this->unit->id}/wir-uploads",
            [
                'file' => $file,
                'progress_group' => 'installation',
            ]
        );

        $response->assertStatus(201);
    }

    public function test_upload_file_without_authentication_fails(): void
    {
        $file = UploadedFile::fake()->image('installation.jpg');

        $response = $this->postJson(
            "/api/units/{$this->unit->id}/wir-uploads",
            [
                'file' => $file,
                'progress_group' => 'installation',
            ]
        );

        $response->assertStatus(401);
    }

    public function test_upload_without_file_validation_fails(): void
    {
        $response = $this->actingAs($this->user)->postJson(
            "/api/units/{$this->unit->id}/wir-uploads",
            [
                'progress_group' => 'installation',
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors('file');
    }

    public function test_upload_without_progress_group_validation_fails(): void
    {
        $file = UploadedFile::fake()->image('installation.jpg');

        $response = $this->actingAs($this->user)->postJson(
            "/api/units/{$this->unit->id}/wir-uploads",
            [
                'file' => $file,
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors('progress_group');
    }

    public function test_upload_invalid_progress_group_fails(): void
    {
        $file = UploadedFile::fake()->image('installation.jpg');

        $response = $this->actingAs($this->user)->postJson(
            "/api/units/{$this->unit->id}/wir-uploads",
            [
                'file' => $file,
                'progress_group' => 'invalid_group',
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors('progress_group');
    }

    public function test_upload_invalid_file_type_fails(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->user)->postJson(
            "/api/units/{$this->unit->id}/wir-uploads",
            [
                'file' => $file,
                'progress_group' => 'installation',
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors('file');
    }

    public function test_upload_file_exceeding_size_limit_fails(): void
    {
        $file = UploadedFile::fake()->image('large.jpg')->size(6 * 1024); // 6MB

        $response = $this->actingAs($this->user)->postJson(
            "/api/units/{$this->unit->id}/wir-uploads",
            [
                'file' => $file,
                'progress_group' => 'installation',
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors('file');
    }

    public function test_replace_existing_wir_upload(): void
    {
        // Upload first file
        $file1 = UploadedFile::fake()->image('installation1.jpg');
        $this->actingAs($this->user)->postJson(
            "/api/units/{$this->unit->id}/wir-uploads",
            [
                'file' => $file1,
                'progress_group' => 'installation',
            ]
        );

        $this->assertDatabaseCount('wir_uploads', 1);

        // Upload second file for same progress_group
        $file2 = UploadedFile::fake()->image('installation2.jpg');
        $response = $this->actingAs($this->user)->postJson(
            "/api/units/{$this->unit->id}/wir-uploads",
            [
                'file' => $file2,
                'progress_group' => 'installation',
            ]
        );

        // Should still have only 1 record (updated)
        $this->assertDatabaseCount('wir_uploads', 1);
        $this->assertDatabaseHas('wir_uploads', [
            'unit_id' => $this->unit->id,
            'progress_group' => 'installation',
            'file_name' => 'installation2.jpg',
        ]);
    }

    public function test_user_can_list_wir_uploads(): void
    {
        // Create two WIR uploads
        WIRUpload::create([
            'unit_id' => $this->unit->id,
            'progress_group' => 'installation',
            'file_path' => 'wirs/test1.jpg',
            'file_name' => 'installation.jpg',
            'file_size' => 102400,
            'uploaded_by' => $this->user->id,
        ]);

        WIRUpload::create([
            'unit_id' => $this->unit->id,
            'progress_group' => 'commissioning',
            'file_path' => 'wirs/test2.jpg',
            'file_name' => 'commissioning.jpg',
            'file_size' => 204800,
            'uploaded_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->getJson(
            "/api/units/{$this->unit->id}/wir-uploads"
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.0.progress_group', 'installation')
            ->assertJsonPath('data.1.progress_group', 'commissioning')
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_get_wir_upload_by_progress_group(): void
    {
        WIRUpload::create([
            'unit_id' => $this->unit->id,
            'progress_group' => 'installation',
            'file_path' => 'wirs/test1.jpg',
            'file_name' => 'installation.jpg',
            'file_size' => 102400,
            'uploaded_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->getJson(
            "/api/units/{$this->unit->id}/wir-uploads/installation"
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.progress_group', 'installation')
            ->assertJsonPath('data.file_name', 'installation.jpg');
    }

    public function test_get_non_existent_wir_upload_returns_404(): void
    {
        $response = $this->actingAs($this->user)->getJson(
            "/api/units/{$this->unit->id}/wir-uploads/installation"
        );

        $response->assertStatus(404);
    }

    public function test_user_can_delete_wir_upload(): void
    {
        $wir = WIRUpload::create([
            'unit_id' => $this->unit->id,
            'progress_group' => 'installation',
            'file_path' => 'wirs/test.jpg',
            'file_name' => 'installation.jpg',
            'file_size' => 102400,
            'uploaded_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson(
            "/api/wir-uploads/{$wir->id}"
        );

        $response->assertStatus(204);

        $this->assertDatabaseMissing('wir_uploads', [
            'id' => $wir->id,
        ]);
    }

    public function test_delete_wir_without_authentication_fails(): void
    {
        $wir = WIRUpload::create([
            'unit_id' => $this->unit->id,
            'progress_group' => 'installation',
            'file_path' => 'wirs/test.jpg',
            'file_name' => 'installation.jpg',
            'file_size' => 102400,
            'uploaded_by' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/wir-uploads/{$wir->id}");

        $response->assertStatus(401);
    }

    public function test_wir_upload_stores_correct_user_id(): void
    {
        $file = UploadedFile::fake()->image('installation.jpg');

        $this->actingAs($this->user)->postJson(
            "/api/units/{$this->unit->id}/wir-uploads",
            [
                'file' => $file,
                'progress_group' => 'installation',
            ]
        );

        $this->assertDatabaseHas('wir_uploads', [
            'unit_id' => $this->unit->id,
            'uploaded_by' => $this->user->id,
        ]);
    }

    public function test_multiple_units_can_have_separate_wir_uploads(): void
    {
        $unit2 = Unit::factory()->create();

        $file1 = UploadedFile::fake()->image('installation1.jpg');
        $file2 = UploadedFile::fake()->image('installation2.jpg');

        $this->actingAs($this->user)->postJson(
            "/api/units/{$this->unit->id}/wir-uploads",
            [
                'file' => $file1,
                'progress_group' => 'installation',
            ]
        );

        $this->actingAs($this->user)->postJson(
            "/api/units/{$unit2->id}/wir-uploads",
            [
                'file' => $file2,
                'progress_group' => 'installation',
            ]
        );

        $this->assertDatabaseCount('wir_uploads', 2);
        $this->assertDatabaseHas('wir_uploads', [
            'unit_id' => $this->unit->id,
            'progress_group' => 'installation',
        ]);
        $this->assertDatabaseHas('wir_uploads', [
            'unit_id' => $unit2->id,
            'progress_group' => 'installation',
        ]);
    }
}
