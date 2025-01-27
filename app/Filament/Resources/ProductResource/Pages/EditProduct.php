<?php
namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Services\TelegramService;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;

class EditProduct extends EditRecord
{
    use Translatable;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Send to Telegram')
                ->label(__('form.share'))
                ->icon('heroicon-o-share')
                ->color('telegram')
                ->form([
                    Select::make('share')
                        ->multiple()
                        ->options([
                            'send_product_name' => __('form.send_product_name'),
                            'send_product_price' => __('form.send_product_price'),
                            'send_product_image' => __('form.send_product_image'),
                        ])
                        ->default(['send_product_name', 'send_product_price']),

                    Forms\Components\MarkdownEditor::make('custom_message'),
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
        if (in_array('send_product_name', $data['share'])) {
            $message .= "🛍 *{$record->name}*\n";
        }
        if (in_array('send_product_price', $data['share'])) {
            $message .= "💰 Price: {$record->price} USD\n";
        }
        if (!empty($data['custom_message'])) {
            $message .= "\n📢 " . $data['custom_message'] . "\n";
        }

        // Send message first
        $telegram->sendMessage($message);

        // If images are selected, send them
        if (in_array('send_product_image', $data['share']) && $record->images) {
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
