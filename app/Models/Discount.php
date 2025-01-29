<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'discount_type',
        'value',
        'applies_to',
        'min_quantity',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    protected static function booted()
    {
        static::created(function ($discount) {
            // Extract categories from Filament's structured request
            $requestData = request()->all();

            if (!empty($requestData['components'][0]['updates']['data.categories.0'])) {
                $categoryIds = (array) $requestData['components'][0]['updates']['data.categories.0'];

                Log::info('Extracted categories:', $categoryIds);

                // Sync categories with the discount
                $discount->categories()->sync($categoryIds);

                // Find products belonging to these categories
                $categoryProducts = Product::whereIn('category_id', $categoryIds)->get();

                foreach ($categoryProducts as $product) {
                    $hasActiveDiscount = $product->discounts()->wherePivot('is_active', true)->exists();
                    $product->discounts()->attach($discount->id, ['is_active' => !$hasActiveDiscount]);
                }
            } else {
                Log::warning('No categories found in request.');
            }
        });
    }





    public function products()
    {
        return $this->belongsToMany(Product::class, 'discount_products');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'discount_categories');
    }
}
