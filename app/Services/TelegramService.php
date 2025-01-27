<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
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
            'photo' => InputFile::create($photoPath),
            'caption' => $caption,
            'parse_mode' => 'Markdown',
        ]);
    }

    // New method to send media group (multiple images)
    public function sendMediaGroup(array $media)
    {
        $response = $this->telegram->sendMediaGroup([
            'chat_id' => $this->channelId,
            'media' => json_encode($media),
        ]);


        return $response;
    }
}
