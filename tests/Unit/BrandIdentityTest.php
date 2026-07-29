<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class BrandIdentityTest extends TestCase
{
    public function test_active_source_uses_zonagim_and_contains_no_legacy_brand(): void
    {
        $root = dirname(__DIR__, 2);
        $legacyPattern = '/LapakGimID|LapakGim|lapakgimid|lapakgim\.my\.id|LapakAkunID|LapakAkun\.id/u';
        $violations = [];

        foreach ($this->sourceFiles($root) as $file) {
            $contents = file_get_contents($file->getPathname());

            if (preg_match($legacyPattern, $contents) === 1) {
                $violations[] = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            }
        }

        $this->assertSame([], $violations, 'Brand lama masih ditemukan: '.implode(', ', $violations));
        $this->assertFileExists($root.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'zonagim.png');
        $this->assertStringContainsString("'brand' => env('APP_NAME', 'Zonagim')", file_get_contents($root.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'seo.php'));
        $this->assertStringContainsString('https://zonagim.my.id', file_get_contents($root.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'llms.txt'));
        $this->assertStringContainsString('https://zonagim.my.id/sitemap.xml', file_get_contents($root.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'robots.txt'));
    }

    /**
     * @return list<SplFileInfo>
     */
    private function sourceFiles(string $root): array
    {
        $files = [];
        $directories = ['app', 'config', 'resources', 'routes', 'public'];
        $extensions = ['php', 'jsx', 'js', 'css', 'txt', 'xml', 'json'];

        foreach ($directories as $directory) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root.DIRECTORY_SEPARATOR.$directory, RecursiveDirectoryIterator::SKIP_DOTS),
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                $path = str_replace('\\', '/', $file->getPathname());

                if (! $file->isFile()
                    || ! in_array(strtolower($file->getExtension()), $extensions, true)
                    || str_contains($path, '/public/build/')
                    || str_contains($path, '/public/js/filament/')) {
                    continue;
                }

                $files[] = $file;
            }
        }

        return $files;
    }
}
