<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ChatHistorySourceTest extends TestCase
{
    private string $chatPanel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->chatPanel = file_get_contents(
            dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'Components'.DIRECTORY_SEPARATOR.'ChatPanel.jsx',
        );
    }

    public function test_authenticated_history_is_user_scoped_and_expires_after_twenty_four_hours(): void
    {
        $this->assertStringContainsString('const CHAT_HISTORY_TTL_MS = 24 * 60 * 60 * 1000;', $this->chatPanel);
        $this->assertStringContainsString('`chat_history_user_${userId}`', $this->chatPanel);
        $this->assertStringContainsString('savedAt: Date.now()', $this->chatPanel);
        $this->assertStringContainsString('Date.now() - parsed.savedAt < CHAT_HISTORY_TTL_MS', $this->chatPanel);
        $this->assertStringContainsString('removeStoredHistory(key)', $this->chatPanel);
    }

    public function test_guest_history_is_memory_only_and_not_persisted(): void
    {
        $this->assertStringContainsString('if (!userId) {', $this->chatPanel);
        $this->assertStringContainsString('return [WELCOME];', $this->chatPanel);
        $this->assertStringNotContainsString('sessionStorage', $this->chatPanel);
        $this->assertStringNotContainsString('localStorage.setItem("chat_history"', $this->chatPanel);
    }

    public function test_provider_history_only_contains_user_messages(): void
    {
        $this->assertStringContainsString('.filter((message) => message.role === "user")', $this->chatPanel);
        $this->assertStringContainsString('role: "user"', $this->chatPanel);
        $this->assertStringContainsString('status === 429', $this->chatPanel);
        $this->assertStringContainsString('status === 419', $this->chatPanel);
        $this->assertStringContainsString('status === 422', $this->chatPanel);
        $this->assertStringContainsString('error.response?.data?.reply', $this->chatPanel);
        $this->assertStringNotContainsString('Maaf, terjadi kesalahan atau jaringan tidak stabil.', $this->chatPanel);
    }

    public function test_chat_output_is_rendered_as_text_and_storage_is_validated(): void
    {
        $this->assertStringContainsString('sanitizeMessages', $this->chatPanel);
        $this->assertStringContainsString('JSON.parse(saved)', $this->chatPanel);
        $this->assertStringContainsString('{message.content}', $this->chatPanel);
        $this->assertStringNotContainsString('dangerouslySetInnerHTML', $this->chatPanel);
        $this->assertStringNotContainsString('innerHTML', $this->chatPanel);
    }
}
