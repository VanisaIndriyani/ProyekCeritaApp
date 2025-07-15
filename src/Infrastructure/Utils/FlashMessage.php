<?php

declare(strict_types=1);

namespace App\Infrastructure\Utils;

class FlashMessage
{
    private const SESSION_KEY = 'flash_messages';

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Add success message
     */
    public function success(string $message): void
    {
        $this->addMessage('success', $message);
    }

    /**
     * Add error message
     */
    public function error(string $message): void
    {
        $this->addMessage('error', $message);
    }

    /**
     * Add info message
     */
    public function info(string $message): void
    {
        $this->addMessage('info', $message);
    }

    /**
     * Add warning message
     */
    public function warning(string $message): void
    {
        $this->addMessage('warning', $message);
    }

    /**
     * Get all messages and clear them
     */
    public function getMessages(): array
    {
        $messages = $_SESSION[self::SESSION_KEY] ?? [];
        unset($_SESSION[self::SESSION_KEY]);
        return $messages;
    }

    /**
     * Check if there are any messages
     */
    public function hasMessages(): bool
    {
        return !empty($_SESSION[self::SESSION_KEY]);
    }

    /**
     * Get messages by type
     */
    public function getMessagesByType(string $type): array
    {
        $messages = $_SESSION[self::SESSION_KEY] ?? [];
        return array_filter($messages, function($msg) use ($type) {
            return $msg['type'] === $type;
        });
    }

    /**
     * Add message to session
     */
    private function addMessage(string $type, string $message): void
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        $_SESSION[self::SESSION_KEY][] = [
            'type' => $type,
            'message' => $message,
            'timestamp' => time()
        ];
    }

    /**
     * Generate HTML for displaying messages
     */
    public static function renderMessages(array $messages): string
    {
        if (empty($messages)) {
            return '';
        }

        $html = '<div class="flash-messages">';
        
        foreach ($messages as $message) {
            $type = htmlspecialchars($message['type']);
            $text = htmlspecialchars($message['message']);
            
            $html .= sprintf(
                '<div class="flash-message flash-%s" data-auto-dismiss="true">
                    <span class="flash-icon">%s</span>
                    <span class="flash-text">%s</span>
                    <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
                </div>',
                $type,
                self::getIconForType($type),
                $text
            );
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Get icon for message type
     */
    private static function getIconForType(string $type): string
    {
        switch ($type) {
            case 'success':
                return '✓';
            case 'error':
                return '✗';
            case 'warning':
                return '⚠';
            case 'info':
            default:
                return 'ℹ';
        }
    }
}
