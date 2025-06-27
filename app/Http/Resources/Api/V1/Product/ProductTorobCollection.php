<?php

namespace App\Http\Resources\Api\V1\Product;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductTorobCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function ($product) {
            return [
                'product_id'   => $product->id,
                'page_url'     => $product->link(),
                'price'        => $product->getLowestPrice(true),
                'availability' => $product->addableToCart() ? 'instock' : false,
                'old_price'    => $product->getLowestDiscount(true) ?? $product->getLowestPrice(true),
            ];
        });
    }
}
