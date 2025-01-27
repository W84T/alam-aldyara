<?php
namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Services\TelegramService;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Send to Telegram')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->modalHeading('Share Product on Telegram')
                ->modalSubheading('Choose what to include in your Telegram message.')
                ->form([
                    Checkbox::make('send_name')->label('Include Product Name')->default(true),
                    Checkbox::make('send_price')->label('Include Product Price')->default(true),
                    Checkbox::make('send_images')->label('Include Product Images')->default(false),
                    Textarea::make('custom_message')->label('Custom Message')->rows(3),
                ])
                ->action(fn (array $data) => $this->sendToTelegram($data)),
            Actions\LocaleSwitcher::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function sendToTelegram(array $data)
    {
        $record = $this->record;
        if (!$record) {
            Notification::make()
                ->title('Error')
                ->body('Product not found!')
                ->danger()
                ->send();
            return;
        }

        $telegram = new TelegramService();

        // Build the message
        $message = "";
        if ($data['send_name']) {
            $message .= "🛍 *{$record->name}*\n";
        }
        if ($data['send_price']) {
            $message .= "💰 Price: {$record->price} USD\n";
        }
        if (!empty($data['custom_message'])) {
            $message .= "\n📢 " . $data['custom_message'] . "\n";
        }

        // Send message first
        $telegram->sendMessage($message);

        // If images are selected, send them
        if ($data['send_images'] && $record->images) {
            foreach ($record->images as $image) {
                $telegram->sendPhoto(url('storage/' . $image));
            }
        }

        Notification::make()
            ->title('Success')
            ->body('Product details sent to Telegram!')
            ->success()
            ->send();
    }
}
