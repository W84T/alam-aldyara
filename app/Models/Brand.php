<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class Brand extends Model
{
    use HasFactory, HasTranslations;
    protected $fillable = ['name', 'slug', 'image', 'is_active'];

    public array $translatable = ['name'];

    protected $casts = ['name' => 'array'];

    protected static function booted(): void{

        self::deleted(function (Brand $brand) {
            Storage::disk('public')->delete($brand->image);
        });

        self::updating(function (Brand $brand) {
            if ($brand->isDirty('image')) {
                $originalImage = $brand->getOriginal('image');
                if ($originalImage) {
                    Storage::disk('public')->delete($originalImage);
                }
            }
        });
    }
    public function products(){
        return $this->hasMany(Product::class);
    }
}
