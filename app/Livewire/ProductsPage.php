<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductsPage extends Component
{
    use WithPagination;


    public $selected_categories = [];
    #[url]
    public $selected_brands = [];
    public $featured;
    public $on_sale;
    public $price_range;
    public function render()
    {
        $products = Product::query()->where('is_active', 1);

        if (!empty($this->selected_categories)) {
            $products->whereIn('category_id', $this->selected_categories);
        }


        if (!empty($this->selected_brands)) {
            $products->whereIn('brand_id', $this->selected_brands);
        }

        if (!empty($this->featured)) {
            $products->where('is_feature', 1);
        }

        if (!empty($this->on_sale)) {
            $products->where('on_sale', 1);
        }

        if (!empty($this->price_range)) {
            $products->whereBetween('price', [0, $this->price_range]);
        }
        return view('livewire.products-page', [
            'products' => $products->paginate(6),
            'brands' => Brand::where('is_active', 1)->get(['id', 'name', 'slug']),
            'categories' => Category::where('is_active', 1)->get(['id', 'name', 'slug']),
        ]);
    }
}

