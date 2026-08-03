<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class NotificationThemeSourceTest extends TestCase
{
    public function test_notification_cards_follow_zonagim_status_colors(): void
    {
        $root = dirname(__DIR__, 2);
        $theme = file_get_contents(
            $root.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'utils'.DIRECTORY_SEPARATOR.'notificationTheme.js',
        );

        $this->assertStringContainsString('if (icon === "success")', $theme);
        $this->assertStringContainsString('background: "#16a34a"', $theme);
        $this->assertStringContainsString('color: "#ffffff"', $theme);
        $this->assertStringContainsString('iconColor: "#ffffff"', $theme);
        $this->assertStringContainsString('background: "#facc15"', $theme);
        $this->assertStringNotContainsString('#dc2626', $theme);
        $this->assertStringNotContainsString('#172033', $theme);
    }

    public function test_filament_success_notifications_use_full_green_with_aligned_white_content(): void
    {
        $root = dirname(__DIR__, 2);
        $theme = file_get_contents(
            $root.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'filament'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'theme.css',
        );

        $this->assertStringContainsString('.fi-body .fi-no-notification.fi-status-success:not(.fi-inline)', $theme);
        $this->assertStringContainsString('background-color: #16a34a', $theme);
        $this->assertStringContainsString('color: #ffffff', $theme);
        $this->assertStringContainsString('align-items: center', $theme);
        $this->assertStringContainsString('.fi-no-notification-main {', $theme);
        $this->assertStringContainsString('margin-top: 0', $theme);
    }

    public function test_all_toasts_use_shared_notification_theme(): void
    {
        $root = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js';
        $paths = [
            'Components/FlashToaster.jsx',
            'Components/ChatPanel.jsx',
            'Pages/Profile/Partials/UpdateProfileInformationForm.jsx',
            'Pages/Profile/Partials/UpdatePasswordForm.jsx',
        ];

        foreach ($paths as $path) {
            $source = file_get_contents($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path));

            $this->assertStringContainsString('toastOptions', $source);
        }
    }
}
