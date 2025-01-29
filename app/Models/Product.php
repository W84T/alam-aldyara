<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasFactory, HasTranslations;
    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'images',
        'description',
        'price',
        'is_active',
        'is_featured',
        'in_stock',
        'qr_code'
    ];
    public array $translatable = ['name', 'description'];
    protected $casts = [
        'images' => 'array',
        'name' => 'array',
        'description' => 'array',
    ];


    protected static function booted(): void
    {
        // Handle QR URL creation after creating a new product
        self::created(function (Product $product) {
            $qr_url = config('app.url')."/product/{$product->id}";
            $product->update(['qr_code' => $qr_url]);
        });

        // Handle image deletion on record deletion
        self::deleted(function (Product $product) {
            if (is_array($product->images)) {
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }
        });

        // Handle image deletion on record update
        self::updating(function (Product $product) {
            if ($product->isDirty('images')) {
                $originalImages = $product->getOriginal('images');
                $originalImagesArray = is_string($originalImages) ? json_decode($originalImages, true) : $originalImages;

                $newImages = $product->images; // The updated images array
                $imagesToDelete = array_diff($originalImagesArray ?? [], $newImages ?? []);

                if (is_array($imagesToDelete)) {
                    foreach ($imagesToDelete as $image) {
                        Storage::disk('public')->delete($image);
                    }
                }
            }
        });
    }


    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function orderItems(){
        return $this->hasMany(OrderItem::class);
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'discount_products')
            ->withPivot('is_active')
            ->withTimestamps();
    }

}
