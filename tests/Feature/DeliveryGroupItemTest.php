<?php

namespace Tests\Feature;

use App\Models\DeliveryGroup;
use App\Models\DeliveryModule;
use App\Models\DeliveryModuleContent;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryGroupItemTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected DeliveryGroup $group;

    protected DeliveryModuleContent $content;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $project = Project::factory()->create();
        $unit = Unit::factory()->create(['project_id' => $project->id]);
        $this->group = DeliveryGroup::create([
            'unit_id' => $unit->id,
            'group_name' => 'DG 1',
            'group_number' => 1,
        ]);

        $module = DeliveryModule::create(['name' => 'Module 1']);
        $this->content = DeliveryModuleContent::create([
            'delivery_module_id' => $module->id,
            'name' => 'Content 1.1',
        ]);
    }

    public function test_can_list_delivery_group_items()
    {
        $this->group->items()->create([
            'delivery_module_content_id' => $this->content->id,
            'package_type' => 'Standard Packing',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/delivery-groups/{$this->group->id}/items");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.content.name', 'Content 1.1');
    }

    public function test_can_store_delivery_group_item()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/delivery-groups/{$this->group->id}/items", [
                'delivery_module_content_id' => $this->content->id,
                'package_type' => 'Sea Packing',
                'remarks' => 'Test remarks',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.package_type', 'Sea Packing');

        $this->assertDatabaseHas('delivery_group_items', [
            'delivery_group_id' => $this->group->id,
            'delivery_module_content_id' => $this->content->id,
            'package_type' => 'Sea Packing',
        ]);
    }

    public function test_can_delete_delivery_group_item()
    {
        $item = $this->group->items()->create([
            'delivery_module_content_id' => $this->content->id,
            'package_type' => 'Standard Packing',
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/delivery-groups/{$this->group->id}/items/{$item->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('delivery_group_items', ['id' => $item->id]);
    }

    public function test_can_list_delivery_modules_catalog()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/delivery-modules');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Module 1');
    }
}
