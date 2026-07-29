<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class FrontendImagePerformanceTest extends TestCase
{
    private string $javascriptPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->javascriptPath = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js';
    }

    public function test_raw_image_elements_are_only_rendered_by_progressive_image(): void
    {
        $violations = [];

        foreach ($this->jsxFiles() as $file) {
            if ($file->getFilename() === 'ProgressiveImage.jsx') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if (preg_match('/<img\b/i', $contents) === 1) {
                $violations[] = $this->relativePath($file);
            }
        }

        $this->assertSame(
            [],
            $violations,
            'Gunakan ProgressiveImage agar gambar memiliki ruang tetap, skeleton, dan fallback.',
        );
    }

    public function test_every_progressive_image_usage_reserves_layout_space(): void
    {
        $violations = [];
        $usageCount = 0;

        foreach ($this->jsxFiles() as $file) {
            if ($file->getFilename() === 'ProgressiveImage.jsx') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            preg_match_all('/<ProgressiveImage\b[\s\S]*?\/>/', $contents, $matches);

            foreach ($matches[0] as $index => $component) {
                $usageCount++;

                $missingProps = array_values(array_filter(
                    ['width', 'height', 'wrapperClassName'],
                    fn (string $prop): bool => preg_match('/\b'.preg_quote($prop, '/').'\s*=/', $component) !== 1,
                ));

                if ($missingProps !== []) {
                    $violations[] = sprintf(
                        '%s usage #%d missing %s',
                        $this->relativePath($file),
                        $index + 1,
                        implode(', ', $missingProps),
                    );
                }
            }
        }

        $this->assertGreaterThan(0, $usageCount, 'Tidak ada pemakaian ProgressiveImage yang ditemukan.');
        $this->assertSame(
            [],
            $violations,
            'Setiap ProgressiveImage wajib memiliki width, height, dan wrapperClassName untuk mencegah CLS.',
        );
    }

    public function test_landing_game_wall_uses_text_and_generic_icons_without_third_party_assets(): void
    {
        $root = dirname(__DIR__, 2);
        $landing = file_get_contents($this->javascriptPath.DIRECTORY_SEPARATOR.'Pages'.DIRECTORY_SEPARATOR.'Landing.jsx');
        $controller = file_get_contents($root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.'PageController.php');

        $this->assertStringContainsString('{game.name}', $landing);
        $this->assertStringContainsString('<Gamepad2', $landing);
        $this->assertStringNotContainsString('images/games', $landing);
        $this->assertStringNotContainsString('cdn.simpleicons.org', $landing);
        $this->assertStringNotContainsString('api.iconify.design', $landing);
        $this->assertStringNotContainsString('gameLogos', $controller);
        $this->assertDirectoryDoesNotExist($root.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'games');
    }

    public function test_auth_scroll_container_ignores_landing_smooth_scroll_and_landing_cleans_up(): void
    {
        $guestLayout = file_get_contents($this->javascriptPath.DIRECTORY_SEPARATOR.'Layouts'.DIRECTORY_SEPARATOR.'GuestLayout.jsx');
        $landing = file_get_contents($this->javascriptPath.DIRECTORY_SEPARATOR.'Pages'.DIRECTORY_SEPARATOR.'Landing.jsx');

        $this->assertStringContainsString('data-lenis-prevent', $guestLayout);
        $this->assertStringContainsString('overflow-y-auto', $guestLayout);
        $this->assertStringContainsString('overscroll-contain', $guestLayout);
        $this->assertStringContainsString('gsap.ticker.remove(updateLenis)', $landing);
        $this->assertStringContainsString('lenis.destroy()', $landing);
    }

    public function test_interactive_controls_use_consistent_cursor_feedback(): void
    {
        $root = dirname(__DIR__, 2);
        $appStyles = file_get_contents($root.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'app.css');
        $adminStyles = file_get_contents($root.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'filament'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'theme.css');

        foreach ([$appStyles, $adminStyles] as $styles) {
            $this->assertStringContainsString('button:not(:disabled)', $styles);
            $this->assertStringContainsString('[role="button"]:not([aria-disabled="true"])', $styles);
            $this->assertStringContainsString('cursor: pointer;', $styles);
            $this->assertStringContainsString('button:disabled', $styles);
            $this->assertStringContainsString('cursor: not-allowed;', $styles);
        }
    }

    public function test_frontend_delete_buttons_use_shared_solid_red_style(): void
    {
        $root = dirname(__DIR__, 2);
        $styles = file_get_contents($root.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'app.css');
        $dangerButton = file_get_contents($this->javascriptPath.DIRECTORY_SEPARATOR.'Components'.DIRECTORY_SEPARATOR.'DangerButton.jsx');
        $chatPanel = file_get_contents($this->javascriptPath.DIRECTORY_SEPARATOR.'Components'.DIRECTORY_SEPARATOR.'ChatPanel.jsx');

        $this->assertStringContainsString('app-delete-button', $dangerButton);
        $this->assertStringContainsString('app-delete-button btn btn-circle btn-sm', $chatPanel);
        $this->assertStringContainsString('confirmButton: "app-delete-button"', $chatPanel);
        $this->assertStringContainsString('.app-delete-button {', $styles);
        $this->assertStringContainsString('background-color: #dc2626;', $styles);
        $this->assertStringContainsString('background-color: #b91c1c;', $styles);
        $this->assertStringContainsString('color: #ffffff;', $styles);
    }

    public function test_progressive_image_defaults_to_non_blocking_image_loading(): void
    {
        $component = file_get_contents($this->javascriptPath.DIRECTORY_SEPARATOR.'Components'.DIRECTORY_SEPARATOR.'ProgressiveImage.jsx');

        $this->assertStringContainsString('loading = "lazy"', $component);
        $this->assertStringContainsString('decoding = "async"', $component);
        $this->assertStringContainsString('width={width}', $component);
        $this->assertStringContainsString('height={height}', $component);
        $this->assertStringContainsString('progressive-image__skeleton', $component);
        $this->assertStringContainsString('progressive-image__fallback', $component);
    }

    /**
     * @return list<SplFileInfo>
     */
    private function jsxFiles(): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->javascriptPath, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'jsx') {
                $files[] = $file;
            }
        }

        return $files;
    }

    private function relativePath(SplFileInfo $file): string
    {
        return str_replace(
            DIRECTORY_SEPARATOR,
            '/',
            substr($file->getPathname(), strlen(dirname(__DIR__, 2)) + 1),
        );
    }
}
