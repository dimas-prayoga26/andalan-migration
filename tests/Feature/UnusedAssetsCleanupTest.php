<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class UnusedAssetsCleanupTest extends TestCase
{
    public function test_every_local_script_and_stylesheet_referenced_by_views_exists(): void
    {
        $missingAssets = [];

        foreach (File::allFiles(resource_path('views')) as $view) {
            $viewContents = preg_replace('/\{\{--.*?--\}\}/s', '', $view->getContents());

            preg_match_all('/<(?:script|link)\b[^>]*\b(?:src|href)="([^"]+)"/', $viewContents, $assetMatches);

            foreach ($assetMatches[1] as $assetReference) {
                $assetPath = $this->resolveLocalAssetPath($assetReference);

                if ($assetPath !== null && ! File::exists($assetPath)) {
                    $missingAssets[] = $view->getRelativePathname().' -> '.$assetReference;
                }
            }
        }

        $this->assertSame([], $missingAssets);
    }

    public function test_unused_scripts_and_views_are_removed(): void
    {
        $removedPaths = [
            public_path('assets/js/agenda/absensi.js'),
            public_path('assets/js/settings.js'),
            public_path('assets/js/dashboard'),
            public_path('assets/js/plugins-init/daterangepicker-init.js'),
            public_path('assets/js/plugins-init/chartjs-init.js'),
            public_path('assets/js/plugins-init/toastr-init.js'),
            resource_path('views/auth/register.blade.php'),
            resource_path('views/error_code/error-404.blade.php'),
        ];

        foreach ($removedPaths as $removedPath) {
            $this->assertFileDoesNotExist($removedPath);
        }

        $this->assertFileExists(public_path('assets/js/plugins-init/fullcalendar-init.js'));
        $this->assertFileExists(resource_path('views/error_code/maintance.blade.php'));
    }

    public function test_pages_no_longer_load_unused_libraries(): void
    {
        $commonJs = File::get(resource_path('views/layouts/commonjs.blade.php'));
        $loginView = File::get(resource_path('views/auth/login.blade.php'));
        $projectOverviewView = File::get(resource_path('views/project_management/overview/index.blade.php'));

        $this->assertStringNotContainsString('daterangepicker-init.js', $commonJs);
        $this->assertStringContainsString('vendor/daterangepicker/daterangepicker.js', $commonJs);

        foreach (['tagify', 'metisMenu', 'bootstrap-select', 'chartist', 'jquery.min.js', '{{-- <script'] as $unusedLoginAsset) {
            $this->assertStringNotContainsString($unusedLoginAsset, $loginView);
        }
        $this->assertStringContainsString('sweetalert2@11', $loginView);

        $this->assertStringNotContainsString('sweetalert2', $projectOverviewView);
    }

    /**
     * Resolve a view asset reference to its file under public/, or null for external/dynamic references.
     */
    private function resolveLocalAssetPath(string $assetReference): ?string
    {
        if (preg_match('/^\{\{\s*asset\(\'([^\']+)\'\)\s*\}\}/', $assetReference, $assetHelperMatch)) {
            return public_path($assetHelperMatch[1]);
        }

        $assetPath = preg_replace('/\?.*$/', '', $assetReference);

        if (preg_match('#^(https?:)?//|^\{\{|^data:|^\#|^$#', $assetPath) || ! preg_match('/\.(js|css)$/', $assetPath)) {
            return null;
        }

        return str_starts_with($assetPath, 'assets')
            ? public_path($assetPath)
            : public_path('assets/'.ltrim($assetPath, '/'));
    }
}
