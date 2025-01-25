<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class ProductDetailsPage extends Component
{
    public $slug;
    public function mount($slug){
        $this->slug = $slug;
    }
    public function render()
    {
        return view('livewire.product-details-page', [
            'product' => Product::where('slug', $this->slug)->firstOrFail()
        ]);
    }
}
