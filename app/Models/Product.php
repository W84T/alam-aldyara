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
        'is_feature',
        'in_stock',
        'on_sale',
    ];
    public array $translatable = ['name', 'description'];
    protected $casts = [
        'images' => 'array',
        'name' => 'array',
        'description' => 'array',
    ];


    protected static function booted(): void
    {
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

    public function brand(){
        return $this->belongsTo(Brand::class);
    }

    public function orderItems(){
        return $this->hasMany(OrderItem::class);
    }
}
