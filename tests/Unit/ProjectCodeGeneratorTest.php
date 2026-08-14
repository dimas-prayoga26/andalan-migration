<?php

namespace Tests\Unit;

use App\Http\Controllers\ProjectManagement\ProjectController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ProjectCodeGeneratorTest extends TestCase
{
    public function test_project_code_base_uses_prefix_initials_and_year(): void
    {
        $this->assertSame('RNB-MPL-2026', $this->projectCodeBaseFromName('RNB Multi Position Launch 2026'));
    }

    public function test_project_code_base_uses_initials_for_long_first_words(): void
    {
        $this->assertSame('EBDS-2026', $this->projectCodeBaseFromName('Executive Business Development Summit 2026'));
    }

    public function test_project_code_base_falls_back_for_empty_name(): void
    {
        $this->assertSame('PROJECT', $this->projectCodeBaseFromName(''));
    }

    private function projectCodeBaseFromName(string $projectName): string
    {
        $method = new ReflectionMethod(ProjectController::class, 'projectCodeBaseFromName');

        return $method->invoke(new ProjectController, $projectName);
    }
}
