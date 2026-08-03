<?php

namespace Tests\Feature;

use Tests\TestCase;

class MaintenancePageTest extends TestCase
{
    public function test_custom_maintenance_page_matches_zonagim_brand(): void
    {
        $content = view('errors.503')->render();

        $this->assertStringContainsString('<title>Maintenance - Zonagim</title>', $content);
        $this->assertStringContainsString('Website Sedang Dalam Perbaikan', $content);
        $this->assertMatchesRegularExpression('/<p class="label">503<\/p>/', $content);
        $this->assertStringContainsString('Coba Lagi', $content);
        $this->assertStringNotContainsString('Pemeliharaan', $content);
        $this->assertStringNotContainsString('Zonagim sedang menjalani pembaruan', $content);
        $this->assertStringContainsString('content="noindex, nofollow, noarchive"', $content);
        $this->assertStringNotContainsString('/images/zonagim.png', $content);
        $this->assertStringNotContainsString('box-shadow', $content);
        $this->assertStringNotContainsString('@keyframes', $content);
        $this->assertStringNotContainsString('@vite', $content);
    }
}
