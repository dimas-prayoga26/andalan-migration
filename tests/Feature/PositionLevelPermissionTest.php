<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Position;
use App\Models\User;
use App\Services\PositionLevelPermissionService;
use Database\Seeders\PositionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionLevelPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_positions_are_mapped_to_permission_levels(): void
    {
        $this->assertSame(Position::LEVEL_ADMINISTRATOR, $this->position('Administrator')->level);
        $this->assertSame(Position::LEVEL_DIRECTOR, $this->position('Director')->level);
        $this->assertSame(Position::LEVEL_DIRECTOR, $this->position('Chief Operating Officer')->level);
        $this->assertSame(Position::LEVEL_DIRECTOR, $this->position('Commissioner')->level);
        $this->assertSame(Position::LEVEL_DIRECTOR, $this->position('Commissioner Independent')->level);
        $this->assertSame(Position::LEVEL_SUPERVISOR, $this->position('Supervisor')->level);
        $this->assertSame(Position::LEVEL_CORE_STAFF, $this->position('Operations Coordinator')->level);
        $this->assertSame(Position::LEVEL_CORE_STAFF, $this->position('Finance and Administration Coordinator')->level);
        $this->assertSame(Position::LEVEL_CORE_STAFF, $this->position('Graphic Design')->level);
        $this->assertSame(Position::LEVEL_CORE_STAFF, $this->position('Legal Officer & Partnership')->level);
        $this->assertSame(Position::LEVEL_CORE_STAFF, $this->position('Brand New Custom Position')->level);
        $this->assertSame(Position::LEVEL_NON_CORE_STAFF, $this->position('Driver')->level);
        $this->assertSame(Position::LEVEL_NON_CORE_STAFF, $this->position('Executive Assistant')->level);
    }

    public function test_super_administrator_is_not_listed_in_any_level(): void
    {
        $this->position('Super Administrator');

        $levelPositionNames = app(PositionLevelPermissionService::class)
            ->levels()
            ->flatMap(fn (array $level) => $level['positions']->pluck('name'));

        $this->assertNotContains('Super Administrator', $levelPositionNames);
    }

    public function test_assign_permission_page_shows_level_columns_and_partial_access(): void
    {
        $permission = $this->permission('view-admin-attendance');
        $legalOfficer = $this->position('Legal Officer & Partnership');
        $this->position('Graphic Design');
        $administrator = $this->position('Administrator');
        $permission->positions()->sync([$legalOfficer->id, $administrator->id]);

        $this->actingAs(User::factory()->superAdministrator()->create())
            ->get(route('authorization.access-menus'))
            ->assertOk()
            ->assertSeeInOrder(['<th>Menu</th>', 'Administrator', 'Director', 'Supervisor', 'Staff'], false)
            ->assertSee('Choose which position levels can access each menu.')
            ->assertSee('name="permission_levels['.$permission->uuid.'][core_staff]" value="partial" data-level-fallback', false)
            ->assertSee('data-partial-level', false)
            ->assertSee('Partial: Legal Officer &amp; Partnership', false)
            ->assertDontSee('authorization-position-select', false);
    }

    public function test_level_state_is_all_only_when_every_position_in_the_level_holds_the_permission(): void
    {
        $administrator = $this->position('Administrator');
        $director = $this->position('Director');
        $this->position('Chief Operating Officer');
        $legalOfficer = $this->position('Legal Officer & Partnership');
        $service = app(PositionLevelPermissionService::class);

        $levelStates = $service->levelStates(
            $service->levels(),
            collect([(string) $administrator->id, (string) $director->id, (string) $legalOfficer->id]),
        );

        $this->assertSame(PositionLevelPermissionService::STATE_ALL, $levelStates[Position::LEVEL_ADMINISTRATOR]['state']);
        $this->assertSame(PositionLevelPermissionService::STATE_PARTIAL, $levelStates[Position::LEVEL_DIRECTOR]['state']);
        $this->assertSame(['Director'], $levelStates[Position::LEVEL_DIRECTOR]['granted_position_names']);
        $this->assertSame(PositionLevelPermissionService::STATE_NONE, $levelStates[Position::LEVEL_SUPERVISOR]['state']);
    }

    public function test_saving_levels_grants_and_revokes_every_position_in_the_level(): void
    {
        $permission = $this->permission('view-talent-acquisition');
        $director = $this->position('Director');
        $chiefOperatingOfficer = $this->position('Chief Operating Officer');
        $supervisor = $this->position('Supervisor');
        $graphicDesign = $this->position('Graphic Design');
        $permission->positions()->sync([$supervisor->id, $graphicDesign->id]);

        $this->actingAs(User::factory()->superAdministrator()->create())
            ->post(route('authorization.position-permissions.update'), [
                'permission_levels' => [
                    (string) $permission->uuid => [
                        Position::LEVEL_DIRECTOR => 'all',
                        Position::LEVEL_SUPERVISOR => 'none',
                        Position::LEVEL_CORE_STAFF => 'none',
                    ],
                ],
            ])
            ->assertRedirect(route('authorization.access-menus'))
            ->assertSessionHas('status', 'Access menu berhasil diperbarui.');

        $grantedPositionIds = $permission->positions()->pluck('positions.id')->map(fn (mixed $id): string => (string) $id)->all();

        $this->assertEqualsCanonicalizing([(string) $director->id, (string) $chiefOperatingOfficer->id], $grantedPositionIds);
    }

    public function test_partial_or_missing_level_keeps_existing_position_access(): void
    {
        $permission = $this->permission('view-admin-attendance');
        $legalOfficer = $this->position('Legal Officer & Partnership');
        $this->position('Graphic Design');
        $commissioner = $this->position('Commissioner');
        $this->position('Director');
        $permission->positions()->sync([$legalOfficer->id, $commissioner->id]);

        $this->actingAs(User::factory()->superAdministrator()->create())
            ->post(route('authorization.position-permissions.update'), [
                'permission_levels' => [
                    (string) $permission->uuid => [
                        Position::LEVEL_CORE_STAFF => 'partial',
                    ],
                ],
            ])
            ->assertRedirect(route('authorization.access-menus'));

        $grantedPositionIds = $permission->positions()->pluck('positions.id')->map(fn (mixed $id): string => (string) $id)->all();

        $this->assertEqualsCanonicalizing([(string) $legalOfficer->id, (string) $commissioner->id], $grantedPositionIds);
    }

    public function test_level_access_applies_to_employees_holding_a_position_in_that_level(): void
    {
        $permission = $this->permission('view-email-management');
        $staffUser = User::factory()->activeEmployee()->create();

        $this->assertFalse($staffUser->hasAnyPositionPermission(['view-email-management']));

        $this->actingAs(User::factory()->superAdministrator()->create())
            ->post(route('authorization.position-permissions.update'), [
                'permission_levels' => [
                    (string) $permission->uuid => [Position::LEVEL_CORE_STAFF => 'all'],
                ],
            ]);

        $this->assertTrue($staffUser->fresh()->hasAnyPositionPermission(['view-email-management']));
    }

    public function test_invalid_level_state_is_rejected(): void
    {
        $permission = $this->permission('view-email-management');

        $this->actingAs(User::factory()->superAdministrator()->create())
            ->from(route('authorization.access-menus'))
            ->post(route('authorization.position-permissions.update'), [
                'permission_levels' => [
                    (string) $permission->uuid => [Position::LEVEL_CORE_STAFF => 'everyone'],
                ],
            ])
            ->assertRedirect(route('authorization.access-menus'))
            ->assertSessionHasErrors('permission_levels.'.$permission->uuid.'.'.Position::LEVEL_CORE_STAFF);
    }

    public function test_new_position_inherits_permissions_its_whole_level_holds(): void
    {
        $levelWidePermission = $this->permission('view-meeting');
        $partialPermission = $this->permission('view-admin-attendance');
        $graphicDesign = $this->position('Graphic Design');
        $this->position('Web Developer');
        $superAdministrator = User::factory()->superAdministrator()->create();
        $partialPermission->positions()->sync([$graphicDesign->id]);

        $this->actingAs($superAdministrator)
            ->post(route('authorization.position-permissions.update'), [
                'permission_levels' => [
                    (string) $levelWidePermission->uuid => [Position::LEVEL_CORE_STAFF => 'all'],
                ],
            ]);

        $this->actingAs($superAdministrator)
            ->post(route('settings.positions.store'), [
                'name' => 'Social Media Specialist',
                'status' => 'active',
                'level' => Position::LEVEL_CORE_STAFF,
            ])
            ->assertRedirect(route('settings.positions.index'));

        $newPosition = Position::query()->where('name', 'Social Media Specialist')->sole();

        $this->assertSame(Position::LEVEL_CORE_STAFF, $newPosition->level);
        $this->assertSame(['view-meeting'], $newPosition->permissions()->pluck('name')->all());
    }

    public function test_new_non_core_staff_position_does_not_inherit_core_staff_access(): void
    {
        $coreStaffPermission = $this->permission('view-meeting');
        $nonCoreStaffPermission = $this->permission('view-attendance');
        $this->position('Graphic Design');
        $this->position('Driver');
        $superAdministrator = User::factory()->superAdministrator()->create();

        $this->actingAs($superAdministrator)
            ->post(route('authorization.position-permissions.update'), [
                'permission_levels' => [
                    (string) $coreStaffPermission->uuid => [Position::LEVEL_CORE_STAFF => 'all'],
                    (string) $nonCoreStaffPermission->uuid => [Position::LEVEL_CORE_STAFF => 'all', Position::LEVEL_NON_CORE_STAFF => 'all'],
                ],
            ]);

        $this->actingAs($superAdministrator)
            ->post(route('settings.positions.store'), [
                'name' => 'Barista',
                'status' => 'active',
                'level' => Position::LEVEL_NON_CORE_STAFF,
            ])
            ->assertRedirect(route('settings.positions.index'));

        $barista = Position::query()->where('name', 'Barista')->sole();

        $this->assertSame(Position::LEVEL_NON_CORE_STAFF, $barista->level);
        $this->assertSame(['view-attendance'], $barista->permissions()->pluck('name')->all());
    }

    public function test_moving_a_position_to_another_level_replaces_its_access_with_the_new_level_access(): void
    {
        $coreStaffPermission = $this->permission('view-meeting');
        $nonCoreStaffPermission = $this->permission('view-attendance');
        $crew = $this->position('Crew');
        $this->position('Graphic Design');
        $this->position('Driver');
        $superAdministrator = User::factory()->superAdministrator()->create();

        $this->actingAs($superAdministrator)
            ->post(route('authorization.position-permissions.update'), [
                'permission_levels' => [
                    (string) $coreStaffPermission->uuid => [Position::LEVEL_CORE_STAFF => 'all'],
                    (string) $nonCoreStaffPermission->uuid => [Position::LEVEL_NON_CORE_STAFF => 'all'],
                ],
            ]);

        $this->assertSame(['view-meeting'], $crew->permissions()->pluck('name')->all());

        $this->actingAs($superAdministrator)
            ->put(route('settings.positions.update', $crew), [
                'name' => 'Crew',
                'status' => 'active',
                'level' => Position::LEVEL_NON_CORE_STAFF,
            ])
            ->assertRedirect(route('settings.positions.index'));

        $this->assertSame(Position::LEVEL_NON_CORE_STAFF, $crew->refresh()->level);
        $this->assertSame(['view-attendance'], $crew->permissions()->pluck('name')->all());
    }

    public function test_updating_a_position_without_changing_level_keeps_its_access(): void
    {
        $permission = $this->permission('view-admin-attendance');
        $legalOfficer = $this->position('Legal Officer & Partnership');
        $this->position('Graphic Design');
        $permission->positions()->sync([$legalOfficer->id]);

        $this->actingAs(User::factory()->superAdministrator()->create())
            ->put(route('settings.positions.update', $legalOfficer), [
                'name' => 'Legal Officer & Partnership',
                'status' => 'active',
                'level' => Position::LEVEL_CORE_STAFF,
            ])
            ->assertRedirect(route('settings.positions.index'));

        $this->assertSame(['view-admin-attendance'], $legalOfficer->permissions()->pluck('name')->all());
    }

    public function test_position_level_is_required_and_must_be_known(): void
    {
        $superAdministrator = User::factory()->superAdministrator()->create();

        $this->actingAs($superAdministrator)
            ->from(route('settings.positions.create'))
            ->post(route('settings.positions.store'), ['name' => 'Crew', 'status' => 'active'])
            ->assertSessionHasErrors('level');

        $this->actingAs($superAdministrator)
            ->from(route('settings.positions.create'))
            ->post(route('settings.positions.store'), ['name' => 'Crew', 'status' => 'active', 'level' => 'staff'])
            ->assertSessionHasErrors('level');

        $this->assertDatabaseMissing('positions', ['name' => 'Crew']);
    }

    public function test_position_form_and_table_show_the_level(): void
    {
        $driver = $this->position('Driver');
        $superAdministrator = User::factory()->superAdministrator()->create();

        $this->actingAs($superAdministrator)
            ->get(route('settings.positions.edit', $driver))
            ->assertOk()
            ->assertSee('name="level"', false)
            ->assertSee('<option value="non_core_staff" selected>Non-Core Staff</option>', false);

        $this->actingAs($superAdministrator)
            ->getJson(route('settings.positions.datatable'))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Driver', 'level' => Position::LEVEL_NON_CORE_STAFF]);
    }

    public function test_position_seeder_inserts_positions_with_their_level(): void
    {
        $this->seed(PositionSeeder::class);

        $this->assertSame(Position::LEVEL_DIRECTOR, Position::query()->where('name', 'Director')->value('level'));
        $this->assertSame(Position::LEVEL_NON_CORE_STAFF, Position::query()->where('name', 'Driver')->value('level'));
        $this->assertSame(Position::LEVEL_CORE_STAFF, Position::query()->where('name', 'Graphic Design')->value('level'));
    }

    public function test_backfill_migration_assigns_levels_to_existing_positions(): void
    {
        $positions = collect(['Director', 'Supervisor', 'Operations Coordinator', 'Driver', 'Executive Assistant', 'Crew', 'barista', 'Graphic Design'])
            ->mapWithKeys(fn (string $name): array => [$name => $this->position($name)]);
        Position::query()->update(['level' => Position::LEVEL_CORE_STAFF]);

        (require database_path('migrations/2026_11_03_113552_backfill_position_levels.php'))->up();

        $levelsByName = Position::query()
            ->whereIn('id', $positions->pluck('id'))
            ->pluck('level', 'name')
            ->all();

        $this->assertEquals([
            'Director' => Position::LEVEL_DIRECTOR,
            'Supervisor' => Position::LEVEL_SUPERVISOR,
            'Operations Coordinator' => Position::LEVEL_CORE_STAFF,
            'Driver' => Position::LEVEL_NON_CORE_STAFF,
            'Executive Assistant' => Position::LEVEL_NON_CORE_STAFF,
            'Crew' => Position::LEVEL_NON_CORE_STAFF,
            'barista' => Position::LEVEL_NON_CORE_STAFF,
            'Graphic Design' => Position::LEVEL_CORE_STAFF,
        ], $levelsByName);
    }

    private function position(string $name): Position
    {
        return Position::query()->firstOrCreate(['name' => $name], ['status' => 'active']);
    }

    private function permission(string $name): Permission
    {
        return Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }
}
