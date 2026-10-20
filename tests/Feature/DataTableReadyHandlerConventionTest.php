<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DataTableReadyHandlerConventionTest extends TestCase
{
    public function test_datatable_views_initialize_inside_jquery_shorthand_ready_handler(): void
    {
        $dataTableViews = collect(File::allFiles(resource_path('views')))
            ->filter(fn ($view): bool => str_contains($view->getContents(), '.DataTable('));

        $this->assertGreaterThanOrEqual(19, $dataTableViews->count());

        foreach ($dataTableViews as $view) {
            $viewContents = $view->getContents();
            $viewName = $view->getRelativePathname();

            $this->assertStringContainsString('$(function () {', $viewContents, $viewName);

            foreach (['jQuery(document).ready(', '$(document).ready(', 'jQuery(function', "addEventListener('DOMContentLoaded'"] as $legacyReadyHandler) {
                $this->assertStringNotContainsString($legacyReadyHandler, $viewContents, $viewName);
            }
        }
    }

    public function test_datatable_views_use_bare_table_markup_with_column_titles_in_javascript(): void
    {
        $dataTableViews = collect(File::allFiles(resource_path('views')))
            ->filter(fn ($view): bool => str_contains($view->getContents(), '.DataTable('));

        foreach ($dataTableViews as $view) {
            $viewContents = $view->getContents();
            $viewName = $view->getRelativePathname();

            $this->assertMatchesRegularExpression(
                '/<table id="datatable(Pending|Approved)?" class="table table-bordered text-nowrap w-100"><\/table>/',
                $viewContents,
                $viewName
            );
            $this->assertDoesNotMatchRegularExpression('/<table id="datatable[^"]*"[^>]*>\s*<thead/', $viewContents, $viewName);
            $this->assertStringContainsString("title: '", $viewContents, $viewName);
        }
    }
}
