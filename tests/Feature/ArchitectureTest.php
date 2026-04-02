<?php

namespace Tests\Feature;

use Tests\TestCase;

class ArchitectureTest extends TestCase
{
    /**
     * Domain models should not depend on application or infrastructure layers.
     */
    public function test_models_do_not_depend_on_delivery_or_application_layers(): void
    {
        // For a full Pest implementation: 
        // expect('App\Models')->toOnlyUse('Illuminate\Database\Eloquent', 'Illuminate\Support');
        
        $this->assertLayerIndependence('app/Models', [
            'App\Http\\',
            'App\Livewire\\',
            'App\Console\\',
            'App\Services\\',
            'App\Jobs\\'
        ]);
    }

    /**
     * Services should not depend on HTTP/UI delivery mechanisms.
     */
    public function test_services_do_not_depend_on_delivery_layers(): void
    {
        $this->assertLayerIndependence('app/Services', [
            'App\Http\Controllers\\',
            'App\Livewire\\',
            'App\Console\\'
        ]);
    }

    /**
     * Jobs should not depend on HTTP/UI delivery mechanisms.
     */
    public function test_jobs_do_not_depend_on_delivery_layers(): void
    {
        $this->assertLayerIndependence('app/Jobs', [
            'App\Http\Controllers\\',
            'App\Livewire\\',
            'App\Console\\'
        ]);
    }

    /**
     * Helper to assert that files in a directory do not import specific namespaces.
     * Note: In a real Laravel 11 project, you would typically use Pest's architecture testing:
     * e.g., expect('App\Models')->toOnlyUse([...])
     */
    private function assertLayerIndependence(string $directory, array $forbiddenNamespaces): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(base_path($directory))
        );

        $violations = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getRealPath());

            foreach ($forbiddenNamespaces as $namespace) {
                // Look for "use App\Forbidden\Namespace"
                if (preg_match('/^use\s+' . preg_quote($namespace, '/') . '/m', $content)) {
                    $violations[] = "File {$file->getFilename()} imports forbidden namespace {$namespace}";
                }
            }
        }

        $this->assertEmpty($violations, "Architectural boundary violations found:\n" . implode("\n", $violations));
    }
}
