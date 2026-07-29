<?php

namespace Tests\Feature;

use App\Filament\Auth\Login as AdminLogin;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\AdminTotpDevice;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin')
            ->assertRedirect('/admin/login');
    }

    public function test_admin_login_rejects_normal_user_and_accepts_admin(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::test(AdminLogin::class)
            ->fillForm([
                'email' => $user->email,
                'password' => 'password',
                'remember' => false,
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();

        Livewire::test(AdminLogin::class)
            ->fillForm([
                'email' => $admin->email,
                'password' => 'password',
                'remember' => false,
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($admin);
    }

    public function test_normal_user_receives_forbidden_for_every_admin_route(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $category = Category::create(['name' => 'PUBG Mobile']);
        $product = Product::create([
            'category_id' => $category->id,
            'game_name' => $category->name,
            'title' => 'Akun PUBG',
            'price' => 50000,
        ]);

        foreach ([
            '/admin',
            '/admin/products',
            '/admin/products/create',
            '/admin/products/'.$product->id.'/edit',
            '/admin/categories',
            '/admin/categories/create',
            '/admin/categories/'.$category->id.'/edit',
        ] as $path) {
            $this->actingAs($user)->get($path)->assertForbidden();
        }
    }

    public function test_admin_can_access_panel_and_resources(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $device = new AdminTotpDevice;
        $device->user()->associate($admin);
        $device->slot = 1;
        $device->name = 'Ponsel utama';
        $device->name_key = hash('sha256', 'ponsel utama');
        $device->secret = 'JBSWY3DPEHPK3PXP';
        $device->secret_fingerprint = hash('sha256', $device->secret);
        $device->save();
        $category = Category::create(['name' => 'Mobile Legends']);
        $product = Product::create([
            'category_id' => $category->id,
            'game_name' => $category->name,
            'title' => 'Akun ML',
            'price' => 100000,
        ]);

        foreach ([
            '/admin',
            '/admin/products',
            '/admin/products/create',
            '/admin/products/'.$product->id.'/edit',
            '/admin/categories',
            '/admin/categories/create',
            '/admin/categories/'.$category->id.'/edit',
        ] as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }
    }

    public function test_admin_is_redirected_from_public_frontend_to_panel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'PUBG Mobile']);

        foreach ([
            '/',
            '/market',
            '/market/'.$category->slug,
            '/terms-of-service',
            '/privacy-policy',
        ] as $path) {
            $this->actingAs($admin)
                ->get($path)
                ->assertRedirect('/admin');
        }
    }

    public function test_guest_and_customer_can_still_open_public_frontend(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->get('/market')->assertOk();
        $this->actingAs($user)->get('/market')->assertOk();
    }

    public function test_admin_cannot_use_customer_only_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Valorant']);
        $product = Product::create([
            'category_id' => $category->id,
            'game_name' => $category->name,
            'title' => 'Akun Valorant',
            'price' => 75000,
        ]);

        foreach ([
            '/profile',
            '/market/'.$category->slug.'/akun/'.$product->slug,
        ] as $path) {
            $this->actingAs($admin)
                ->get($path)
                ->assertRedirect('/admin');
        }
    }

    public function test_normal_user_cannot_open_admin_login_when_already_authenticated(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/login');

        $response->assertRedirect('/admin');
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_cannot_open_public_login_when_already_authenticated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/login')
            ->assertRedirect('/');
    }

    public function test_panel_access_requires_admin_role_and_admin_panel(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $panel = Filament::getPanel('admin');

        $this->assertFalse($user->canAccessPanel($panel));
        $this->assertTrue($admin->canAccessPanel($panel));
    }

    public function test_resource_policies_fail_closed_for_normal_user(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertFalse(Gate::forUser($user)->allows('viewAny', Product::class));
        $this->assertFalse(Gate::forUser($user)->allows('create', Product::class));
        $this->assertFalse(Gate::forUser($user)->allows('viewAny', Category::class));
        $this->assertFalse(Gate::forUser($user)->allows('create', Category::class));
        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Product::class));
        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Category::class));
        $this->actingAs($user);
        $this->assertFalse(ProductResource::canAccess());
        $this->assertFalse(CategoryResource::canAccess());
    }

    public function test_privileged_fields_are_not_mass_assignable(): void
    {
        $user = new User;

        $this->assertFalse($user->isFillable('role'));
        $this->assertFalse($user->isFillable('otp_code'));
        $this->assertFalse($user->isFillable('otp_expires_at'));

        $user->fill([
            'name' => 'Pengguna',
            'email' => 'pengguna@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->assertNull($user->role);
    }
}
