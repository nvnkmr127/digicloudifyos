<?php

namespace Tests\Feature;

use Tests\TestCase;

class DesignDriftTest extends TestCase
{
    public function test_app_views_do_not_use_hex_colors_or_inline_styles(): void
    {
        $files = $this->appBladeFiles();
        $hexViolations = [];
        $styleViolations = [];

        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (! is_string($content)) {
                continue;
            }

            $content = str_replace('->', '__', $content);

            if (preg_match_all('/(?<!:)\bstyle\s*=\s*"([^"]*)"/i', $content, $styleMatches)) {
                foreach ($styleMatches[1] as $styleValue) {
                    if (! is_string($styleValue)) {
                        continue;
                    }

                    $allowed = preg_match('/^\s*(width|height)\s*:\s*\{\{[^}]+\}\}%\s*;?\s*$/i', $styleValue) === 1;
                    if (! $allowed) {
                        $styleViolations[] = $file;
                        break;
                    }
                }
            }

            if (preg_match('/\bg-\[#|\btext-\[#|\bborder-\[#/i', $content)) {
                $hexViolations[] = $file;

                continue;
            }

            if (preg_match('/#[0-9a-fA-F]{2,8}[a-fA-F][0-9a-fA-F]{0,8}/', $content)) {
                $hexViolations[] = $file;
            }
        }

        $this->assertSame([], array_values(array_unique($hexViolations)), "Hex/arbitrary colors found in:\n".implode("\n", array_values(array_unique($hexViolations))));
        $this->assertSame([], array_values(array_unique($styleViolations)), "Inline styles found in:\n".implode("\n", array_values(array_unique($styleViolations))));
    }

    private function appBladeFiles(): array
    {
        $base = base_path('resources/views');
        if (! is_dir($base)) {
            return [];
        }

        $excluded = [
            DIRECTORY_SEPARATOR.'emails'.DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR.'reports'.DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR.'deliverables'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR,
        ];

        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base));
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

            $path = $file->getRealPath();
            if (! is_string($path) || $path === '') {
                continue;
            }

            $skip = false;
            foreach ($excluded as $needle) {
                if (str_contains($path, $needle)) {
                    $skip = true;
                    break;
                }
            }

            if ($skip) {
                continue;
            }

            $files[] = $path;
        }

        return $files;
    }
}
