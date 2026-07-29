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

        $this->assertStringContainsString('background: "#facc15"', $theme);
        $this->assertStringContainsString('color: "#422006"', $theme);
        $this->assertStringContainsString('iconColor: "#713f12"', $theme);
        $this->assertStringNotContainsString('#dc2626', $theme);
        $this->assertStringNotContainsString('#172033', $theme);
    }

    public function test_filament_notifications_use_the_same_zonagim_yellow_theme(): void
    {
        $root = dirname(__DIR__, 2);
        $theme = file_get_contents(
            $root.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'filament'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'theme.css',
        );

        $this->assertStringContainsString('.fi-body .fi-no-notification:not(.fi-inline)', $theme);
        $this->assertStringContainsString('background-color: #facc15', $theme);
        $this->assertStringContainsString('color: #422006', $theme);
        $this->assertStringContainsString('--color-400: #713f12', $theme);
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
