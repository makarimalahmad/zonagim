<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotFoundPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_receives_branded_404_with_market_direction(): void
    {
        $response = $this->get('/halaman-yang-tidak-ada');

        $response
            ->assertNotFound()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertInertia(fn ($page) => $page
                ->component('Errors/NotFound')
                ->where('auth.user', null)
            );

        $content = $response->getContent();

        $this->assertStringContainsString(
            '<meta name="robots" content="noindex, nofollow, noarchive">',
            $content,
        );
        $this->assertStringNotContainsString('<link rel="canonical"', $content);
        $this->assertStringNotContainsString('<meta property="og:url"', $content);
    }

    public function test_authenticated_user_identity_is_available_on_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/alamat-tidak-valid')
            ->assertNotFound()
            ->assertInertia(fn ($page) => $page
                ->component('Errors/NotFound')
                ->where('auth.user.id', $user->id)
            );
    }

    public function test_failed_model_binding_uses_branded_noindex_404(): void
    {
        $this->get('/market/game-tidak-ada')
            ->assertNotFound()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertInertia(fn ($page) => $page->component('Errors/NotFound'));
    }

    public function test_json_404_remains_json(): void
    {
        $this->getJson('/api-yang-tidak-ada')
            ->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    public function test_plain_text_404_is_not_replaced_with_html(): void
    {
        $this->withHeader('Accept', 'text/plain')
            ->get('/resource-tidak-ada')
            ->assertNotFound()
            ->assertHeaderMissing('X-Inertia');
    }

    public function test_not_found_source_has_accessible_market_action(): void
    {
        $source = file_get_contents(
            dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'Pages'.DIRECTORY_SEPARATOR.'Errors'.DIRECTORY_SEPARATOR.'NotFound.jsx',
        );

        $this->assertStringContainsString('aria-labelledby="not-found-title"', $source);
        $this->assertStringContainsString('href={route("market")}', $source);
        $this->assertStringContainsString('Lihat Market', $source);
        $this->assertStringNotContainsString('shadow-', $source);
        $this->assertStringNotContainsString('glow', $source);
    }
}
