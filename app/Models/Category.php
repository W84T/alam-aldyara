<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasFactory, HasTranslations;
    protected $fillable = ['name', 'slug', 'image', 'is_active'];

    public array $translatable = ['name'];

    protected $casts = ['name' => 'array'];

    protected static function booted(): void{

        self::deleted(function (Category $category) {
            Storage::disk('public')->delete($category->image);
        });

        self::updating(function (Category $category) {
            if ($category->isDirty('image')) {
                $originalImage = $category->getOriginal('image');
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
