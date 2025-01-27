<?php
namespace App\Services;

use Telegram\Bot\Api;
use Telegram\Bot\FileUpload\InputFile;

class TelegramService
{
    protected $telegram;
    protected $channelId;

    public function __construct()
    {
        $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
        $this->channelId = env('TELEGRAM_CHANNEL_ID');
    }

    public function sendMessage($message)
    {
        return $this->telegram->sendMessage([
            'chat_id' => $this->channelId,
            'text' => $message,
            'parse_mode' => 'Markdown',
        ]);
    }

    public function sendPhoto($photoPath, $caption = null)
    {
        return $this->telegram->sendPhoto([
            'chat_id' => $this->channelId,
            'photo' => InputFile::create($photoPath), // Properly upload image
            'caption' => $caption,
            'parse_mode' => 'Markdown',
        ]);
    }
}
