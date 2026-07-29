<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForbiddenPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_user_receives_branded_forbidden_page_for_admin(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->get('/admin');

        $response
            ->assertForbidden()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertInertia(fn ($page) => $page
                ->component('Errors/Forbidden')
                ->where('auth.user.id', $user->id)
            );

        $content = $response->getContent();

        $this->assertStringContainsString(
            '<meta name="robots" content="noindex, nofollow, noarchive">',
            $content,
        );
        $this->assertStringNotContainsString('<link rel="canonical"', $content);
        $this->assertStringNotContainsString('<meta property="og:url"', $content);
    }

    public function test_json_forbidden_response_is_not_replaced_with_html(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->getJson('/admin')
            ->assertForbidden()
            ->assertJsonStructure(['message']);
    }

    public function test_forbidden_source_is_accessible_and_does_not_expose_security_details(): void
    {
        $source = file_get_contents(
            dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'Pages'.DIRECTORY_SEPARATOR.'Errors'.DIRECTORY_SEPARATOR.'Forbidden.jsx',
        );

        $this->assertStringContainsString('aria-labelledby="forbidden-title"', $source);
        $this->assertStringContainsString('href={route("market")}', $source);
        $this->assertStringContainsString('Lihat Market', $source);
        $this->assertStringNotContainsString('policy', strtolower($source));
        $this->assertStringNotContainsString('role', strtolower($source));
        $this->assertStringNotContainsString('middleware', strtolower($source));
        $this->assertStringNotContainsString('shadow-', $source);
        $this->assertStringNotContainsString('glow', $source);
    }
}
