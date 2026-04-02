<?php

namespace Tests\Feature;

use Tests\TestCase;

class A11yTemplateTest extends TestCase
{
    public function test_blade_images_have_alt_attribute(): void
    {
        $files = $this->bladeFiles();
        $violations = [];

        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (! is_string($content)) {
                continue;
            }
            $content = str_replace('->', '__', $content);

            if (preg_match_all('/<img\b[^>]*>/i', $content, $matches)) {
                foreach ($matches[0] as $tag) {
                    if (! preg_match('/\balt\s*=/i', $tag)) {
                        $violations[] = $file;
                        break;
                    }
                }
            }
        }

        $this->assertSame([], $violations, "Missing alt= on <img> in:\n".implode("\n", array_values(array_unique($violations))));
    }

    public function test_blade_iframes_have_title_attribute(): void
    {
        $files = $this->bladeFiles();
        $violations = [];

        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (! is_string($content)) {
                continue;
            }
            $content = str_replace('->', '__', $content);

            if (preg_match_all('/<iframe\b[^>]*>/i', $content, $matches)) {
                foreach ($matches[0] as $tag) {
                    if (! preg_match('/\btitle\s*=/i', $tag)) {
                        $violations[] = $file;
                        break;
                    }
                }
            }
        }

        $this->assertSame([], $violations, "Missing title= on <iframe> in:\n".implode("\n", array_values(array_unique($violations))));
    }

    private function bladeFiles(): array
    {
        $dir = base_path('resources/views');
        if (! is_dir($dir)) {
            return [];
        }

        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        $files = [];

        foreach ($it as $file) {
            if (! $file instanceof \SplFileInfo) {
                continue;
            }

            if (! $file->isFile()) {
                continue;
            }

            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $files[] = $file->getRealPath();
        }

        return array_values(array_filter($files, fn ($p) => is_string($p) && $p !== ''));
    }
}
