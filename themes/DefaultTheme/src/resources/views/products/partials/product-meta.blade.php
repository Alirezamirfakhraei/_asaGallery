<meta property="og:title" content="{{ $product->meta_title ?: $product->title }}" />
<meta property="og:type" content="product" />
<meta property="og:url" content="{{ route('front.products.show', ['product' => $product]) }}" />
<meta name="description" content="{{ $product->meta_description ?: $product->short_description }}">
<meta name="keywords" content="{{ $product->getTags }}">
<meta name="product_id" content="{{ $product->id }}">

<link rel="canonical" href="{{ route('front.products.show', ['product' => $product]) }}" />

<meta name="product_name" content="{{ $product->title }}">
<meta name="product_price" content="{{ $product->getLowestPrice(true) }}">
<meta name="product_old_price" content="{{ $product->getLowestDiscount(true) }}">

@if ($product->image)
    <meta property="og:image" content="{{ asset($product->image) }}">
    <meta property="og:image:width" content="600"/>
    <meta property="og:image:height" content="600"/>
@endif

@if ($product->addableToCart())
    <meta property="product:availability" content="in stock">
    <meta property="product:price:amount" content="{{ $product->getLowestPrice(true) }}">
    <meta property="product:price:currency" content="IRR">
    <meta name="availability" content="instock">
@else
    <meta property="product:availability" content="out of stock">
    <meta name="availability" content="outofstock">
@endif

<script type="application/ld+json">
    {
        "@context": "https://schema.org/",
        "@type": "Product",
        "name": "{{ $product->meta_title ?: $product->title }}",
        "alternateName": "{{ $product->title_en }}",
        "image": [
            "{{ asset($product->image) }}"
            @if ($product->gallery()->count())
                ,
                @foreach ($product->gallery as $gallery)
                    "{{ asset($gallery->image) }}" {{ !$loop->last ? ',' : '' }}
                @endforeach
            @endif
        ],

        @if ($product->brand)
        "brand": {
            "@type": "Brand",
            "name": "{{ $product->brand->name }}"
        },
        @endif

        "offers": {
            "@type": "Offer",
            "url": "{{ route('front.products.show', ['product' => $product]) }}",
            "priceCurrency": "IRR",
            "price": "{{ $product->getLowestPrice(true) }}"
        },

        "description": "{{ $product->meta_description ?: $product->short_description }}"
    }
</script>
