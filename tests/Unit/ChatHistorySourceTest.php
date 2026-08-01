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

    public function test_chat_history_is_memory_only_and_not_persisted(): void
    {
        $this->assertStringContainsString('useState([WELCOME])', $this->chatPanel);
        $this->assertStringNotContainsString('localStorage.setItem', $this->chatPanel);
        $this->assertStringNotContainsString('sessionStorage.setItem', $this->chatPanel);
        $this->assertStringNotContainsString('chat_history_user_', $this->chatPanel);
        $this->assertStringNotContainsString('userId', $this->chatPanel);
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

    public function test_chat_output_is_rendered_as_text(): void
    {
        $this->assertStringContainsString('{message.content}', $this->chatPanel);
        $this->assertStringNotContainsString('dangerouslySetInnerHTML', $this->chatPanel);
        $this->assertStringNotContainsString('innerHTML', $this->chatPanel);
    }
}
