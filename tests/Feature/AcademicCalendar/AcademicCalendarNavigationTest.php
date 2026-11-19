<?php

namespace Tests\Feature\AcademicCalendar;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The Academic Calendar lives under School Setup in the admin sidebar and replaces
 * the old "Years & Terms" link (years and terms are tabs on the calendar page).
 */
class AcademicCalendarNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);
    }

    private function sidebarFor(string $role): string
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);

        return view('layouts.partials.admin-sidebar')->render();
    }

    private function schoolSetupSection(string $html): string
    {
        $start = strpos($html, 'School Setup');
        $this->assertNotFalse($start, 'School Setup section is missing');

        return substr($html, $start, (strpos($html, 'sidebar-category-header', $start) ?: strlen($html)) - $start);
    }

    public function test_super_admin_sees_calendar_in_school_setup_instead_of_years_and_terms(): void
    {
        $html = $this->sidebarFor('Super Admin');
        $setup = $this->schoolSetupSection($html);

        $this->assertStringContainsString(route('admin.academic-calendar.index'), $setup);
        $this->assertStringContainsString(route('admin.divisions.index'), $setup);
        $this->assertStringNotContainsString('Years &amp; Terms', $html);
        $this->assertSame(1, substr_count($html, 'title="Academic Calendar"'), 'Calendar link should appear once (not also under Overview)');
    }

    public function test_principal_sees_only_the_calendar_under_school_setup(): void
    {
        $setup = $this->schoolSetupSection($this->sidebarFor('Principal'));

        $this->assertStringContainsString(route('admin.academic-calendar.index'), $setup);
        $this->assertStringNotContainsString(route('admin.divisions.index'), $setup);
        $this->assertStringNotContainsString(route('admin.subjects.index'), $setup);
    }

    public function test_year_and_term_tabs_are_hidden_from_roles_that_cannot_open_them(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Principal');
        $this->actingAs($user);

        $tabs = view('admin.layouts.partials.academic-setup-tabs')->render();

        $this->assertStringContainsString('Academic Calendar', $tabs);
        $this->assertStringNotContainsString(route('admin.academic-years.index'), $tabs);
        $this->assertStringNotContainsString(route('admin.terms.index'), $tabs);
    }
}
