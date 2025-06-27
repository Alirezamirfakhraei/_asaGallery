<?php

namespace App\Http\Controllers\Back;

use App\Events\ProductPricesChanged;
use App\Exports\ProductsExport;
use App\Models\AttributeGroup;
use App\Models\Brand;
use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Http\Requests\Back\Product\StoreProductRequest;
use App\Http\Requests\Back\Product\UpdateProductRequest;
use App\Http\Resources\Api\V1\Product\ProductTorobCollection;
use App\Http\Resources\Datatable\Product\ProductCollection;
use App\Models\Currency;
use App\Models\Label;
use App\Models\Price;
use App\Models\Product;
use App\Models\SizeType;
use App\Models\Specification;
use App\Models\SpecificationGroup;
use App\Models\SpecType;
use App\Services\Nila\ClientService;
use App\Services\Nila\HolooService;
use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Morilog\Jalali\Jalalian;


class ProductController extends Controller
{

    protected $holoo;
    protected $clientService;
    public function __construct(HolooService $holoo, ClientService $clientService)
    {
        $this->holoo = $holoo;
        $this->clientService = $clientService;
        $this->authorizeResource(Product::class, 'product');
    }

    private function getMainGroup()
    {
        return $this->holoo->callApi("MainGroup");
    }

    private function getSideGroup(){
        return $this->holoo->callApi("SideGroup");
    }


    public function index()
    {
        $brands = Brand::orderBy('name')->select('id', 'name')->get();
        return view('back.products.index', compact('brands'));
    }

    public function apiIndex(Request $request)
    {
        $this->authorize('products.index');

        $products = Product::detectLang()->datatableFilter($request);

        $products = datatable($request, $products);

        return new ProductCollection($products);
    }

    public function indexPrices(Request $request)
    {
        $this->authorize('products.prices');

        $products = Product::detectLang()->filter($request)->customPaginate($request);
        $brands = Brand::orderBy('name')->select('id', 'name')->get();

        return view('back.products.prices', compact('products', 'brands'));
    }

    public function updatePrices(Request $request)
    {
        $this->authorize('products.prices');

        $request->validate([
            'products' => 'required|array',
        ]);

        $products_id = array_keys($request->products);
        $prices_count = Price::whereIn('product_id', $products_id)->count() * 2;
        $max_input_vars = ini_get('max_input_vars');

        if ($prices_count + 5 > $max_input_vars) {
            throw ValidationException::withMessages([
                'prices' => 'لطفا مقدار max_input_vars را در فایل php.ini تغییر دهید.'
            ]);
        }

        foreach ($request->products as $key => $value) {
            $product = Product::find($key);

            if (!$product) {
                continue;
            }

            foreach ($product->prices as $price) {
                if (!isset($value['prices'][$price->id])) {
                    continue;
                }

                $request_price = $value['prices'][$price->id];

                if (isset($request_price['price']) && isset($request_price['stock']) && ($request_price['price'] != $price->price || $request_price['stock'] != $price->stock)) {

                    $price->update([
                        'price' => $request_price['price'],
                        'stock' => $request_price['stock'],
                        'discount_price' => get_discount_price($request_price['price'], $price->discount, $product),
                        'regular_price' => get_discount_price($request_price['price'], 0, $product),
                    ]);
                }
            }

            event(new ProductPricesChanged($product));
        }

        // clear product caches
        Product::clearCache();

        return response('success');
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->only([
            'title',
            'title_en',
            'category_id',
            'size_type_id',
            'weight',
            'unit',
            'type',
            'description',
            'short_description',
            'special',
            'meta_title',
            'image_alt',
            'meta_description',
            'published',
            'currency_id',
            'rounding_amount',
            'rounding_type',
        ]);
        $data['spec_type_id'] = spec_type($request);
        $data['price_type'] = "multiple-price";
        $data['slug'] = $request->slug ?: $request->title;
        $data['publish_date'] = $request->publish_date ? Jalalian::fromFormat('Y-m-d H:i:s', $request->publish_date)->toCarbon() : null;
        $data['special_end_date'] = $request->special_end_date ? Jalalian::fromFormat('Y-m-d H:i:s', $request->special_end_date)->toCarbon() : null;
        $data['lang'] = app()->getLocale();
        $data['admin_updated_at'] = now();

        $product = Product::create($data);

        // update product brand
        $this->updateProductBrand($product, $request);

        // update product prices
        $this->updateProductPrices($product, $request);

        // update product files
        $this->updateProductFiles($product, $request);

        // update product specifications
        $this->updateProductSpecifications($product, $request);

        // update product images
        $this->updateProductImages($product, $request);

        // update product categories
        $this->updateProductCategories($product, $request);

        // update product labels
        $this->updateProductLabels($product, $request);

        // update product sizes
        $this->updateProductSizes($product, $request);

        toastr()->success('محصول با موفقیت ایجاد شد.');

        return response("success");
    }

    public function create(Request $request)
    {
        $categories = Category::detectLang()->where('type', 'productcat')->orderBy('ordering')->get();
        $specTypes = SpecType::detectLang()->get();
        $sizetypes = SizeType::detectLang()->get();
        $attributeGroups = AttributeGroup::detectLang()->orderBy('ordering')->get();
        $currencies = Currency::latest()->get();

        $copy_product = null;

        if ($request->product) {
            $copy_product = Product::where('slug', $request->product)->first();
        } else if ($request->copy_product_id) {
            $copy_product = Product::find($request->copy_product_id);
        }

        $autoFillData = $this->getAutoFillData($request);

        return view('back.products.create', compact(
            'categories',
            'specTypes',
            'sizetypes',
            'attributeGroups',
            'copy_product',
            'currencies',
            'autoFillData'
        ));
    }

    public function edit(Product $product)
    {
        $categories = Category::detectLang()->where('type', 'productcat')->orderBy('ordering')->get();
        $specTypes = SpecType::detectLang()->get();
        $sizetypes = SizeType::detectLang()->get();
        $attributeGroups = AttributeGroup::detectLang()->orderBy('ordering')->get();
        $currencies = Currency::whereNull('deleted_at')->orWhere('id', $product->currency_id)->latest()->get();

        return view('back.products.edit', compact(
            'product',
            'categories',
            'specTypes',
            'sizetypes',
            'attributeGroups',
            'currencies'
        ));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->only([
            'title',
            'title_en',
            'category_id',
            'size_type_id',
            'weight',
            'unit',
            'type',
            'description',
            'short_description',
            'special',
            'meta_title',
            'image_alt',
            'meta_description',
            'published',
            'currency_id',
            'rounding_amount',
            'rounding_type',
        ]);

        $data['spec_type_id'] = spec_type($request);
        $data['price_type'] = "multiple-price";
        $data['slug'] = $request->slug ?: $request->title;
        $data['publish_date'] = $request->publish_date ? Jalalian::fromFormat('Y-m-d H:i:s', $request->publish_date)->toCarbon() : null;
        $data['special_end_date'] = $request->special_end_date ? Jalalian::fromFormat('Y-m-d H:i:s', $request->special_end_date)->toCarbon() : null;
        $data['admin_updated_at'] = now();

        $product->update($data);

        // update product brand
        $this->updateProductBrand($product, $request);

        // update product prices
        $this->updateProductPrices($product, $request);

        // update product files
        $this->updateProductFiles($product, $request);

        // update product specifications
        $this->updateProductSpecifications($product, $request);

        // update product images
        $this->updateProductImages($product, $request);

        // update product categories
        $this->updateProductCategories($product, $request);

        // update product labels
        $this->updateProductLabels($product, $request);

        // update product sizes
        $this->updateProductSizes($product, $request);

        toastr()->success('محصول با موفقیت ویرایش شد.');

        return response("success");
    }

    public function image_store(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|image|max:10240',
        ]);

        $image = $request->file('file');

        $currentDate = Carbon::now()->toDateString();
        $imagename = 'img' . '-' . $currentDate . '-' . uniqid() . '.' . $image->getClientOriginalExtension();

        $image->storeAs('tmp', $imagename);

        return response()->json(['imagename' => $imagename]);
    }

    public function image_delete(Request $request)
    {
        $filename = $request->get('filename');

        if (Storage::exists('tmp/' . $filename)) {
            Storage::delete('tmp/' . $filename);
        }

        return response('success');
    }

    public function destroy(Product $product)
    {
        $product->tags()->detach();
        $product->specifications()->detach();

        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        foreach ($product->gallery as $image) {
            if (Storage::disk('public')->exists($image->image)) {
                Storage::disk('public')->delete($image->image);
            }

            $image->delete();
        }

        $product->delete();

        return response('success');
    }

    public function multipleDestroy(Request $request)
    {
        $this->authorize('products.delete');

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
        ]);

        foreach ($request->ids as $id) {
            $product = Product::find($id);
            $this->destroy($product);
        }

        return response('success');
    }

    public function generate_slug(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $slug = SlugService::createSlug(Product::class, 'slug', $request->title);

        return response()->json(['slug' => $slug]);
    }

    public function export(Request $request)
    {
        $this->authorize('products.export');

        $products = Product::detectLang()->datatableFilter($request)->get();

        switch ($request->export_type) {
            case 'excel':
            {
                return $this->exportExcel($products, $request);
                break;
            }
            default:
            {
                return $this->exportPrint($products, $request);
            }
        }
    }

    public function torobProductsList()
    {
        $products = Product::with('prices')
            ->select('id', 'slug', 'type', 'price_type', 'currency_id', 'created_at', 'admin_updated_at', 'published', 'publish_date')
            ->published()
            ->orderBy('admin_updated_at')
            ->latest()
            ->paginate(100);

        return $this->respondWithResourceCollection(new ProductTorobCollection($products));
    }

    //------------- Category methods

    public function categories()
    {
        $this->authorize('products.category');

        $categories = Category::detectLang()->where('type', 'productcat')->whereNull('category_id')
            ->with('childrenCategories')
            ->orderBy('ordering')
            ->get();

        return view('back.products.categories', compact('categories'));
    }



//    private function updateProductPrices(Product $product, Request $request)
//    {
//        if ($product->isDownload()) {
//            return;
//        }
//
//        $prices_id = [];
//
//        foreach ($request->prices as $price) {
//
//            $attributes = array_filter($price['attributes'] ?? []);
//
//            $update_price = false;
//
//            foreach ($product->prices()->withTrashed()->get() as $product_price) {
//                $product_price_attributes = $product_price->get_attributes()->get()->pluck('id')->toArray();
//
//                sort($attributes);
//                sort($product_price_attributes);
//
//                if ($attributes == $product_price_attributes) {
//                    $update_price = $product_price;
//                    break 1;
//                }
//            }
//            if ($update_price) {
//                $update_price->update([
//                    "productCode" => $price["productCode"],
//                    "price" => $price["price"],
//                    "discount" => $price["discount"],
//                    "discount_price" => get_discount_price($price["price"], $price["discount"], $product),
//                    "regular_price" => get_discount_price($price["price"], 0, $product),
//                    "stock" => $price["stock"],
//                    "cart_max" => $price["cart_max"],
//                    "cart_min" => $price["cart_min"],
//                    "discount_expire_at" => $price["discount_expire_at"] ? Jalalian::fromFormat('Y-m-d H:i:s', $price["discount_expire_at"])->toCarbon() : null,
//                    "deleted_at" => null,
//                ]);
//
//                $update_price->get_attributes()->sync($attributes);
//
//                $prices_id[] = $update_price->id;
//            } else {
//                $insert_price = $product->prices()->create(
//                    [
//                        "productCode" => $price["productCode"],
//                        "price" => $price["price"],
//                        "discount" => $price["discount"],
//                        "discount_price" => get_discount_price($price["price"], $price["discount"], $product),
//                        "regular_price" => get_discount_price($price["price"], 0, $product),
//                        "stock" => $price["stock"],
//                        "cart_max" => $price["cart_max"],
//                        "cart_min" => $price["cart_min"],
//                        "discount_expire_at" => $price["discount_expire_at"] ? Jalalian::fromFormat('Y-m-d H:i:s', $price["discount_expire_at"])->toCarbon() : null,
//                    ]
//                );
//
//                foreach ($attributes as $attribute) {
//                    $insert_price->get_attributes()->attach([$attribute]);
//                }
//
//                $prices_id[] = $insert_price->id;
//            }
//        }
//
//        $product->prices()->whereNotIn('id', $prices_id)->delete();
//
//        DB::table('cart_product')
//            ->where('product_id', $product->id)
//            ->whereNotNull('price_id')
//            ->whereNotIn('price_id', $prices_id)
//            ->delete();
//
//        event(new ProductPricesChanged($product));
//    }


    private function updateProductPrices(Product $product, Request $request)
    {
        if ($product->isDownload()) {
            return;
        }

        $prices_id = [];

        foreach ($request->prices as $price) {
            $attributes = array_filter($price['attributes'] ?? []);
            $update_price = false;
            $priceCode = $price['productCode'];
            // دریافت قیمت و موجودی از وب‌سرویس هلـو
            $mainGroup = self::getMainGroup();
            $erpMainGroup = $mainGroup['mainGroup'][0]['ErpCode'] ?? "bBAHfg==";
            $getPriceAndStock  = $this->holoo->callApi("Product?code=$priceCode&maingrouperpcode=$erpMainGroup");
            $finalPriceRial = $getPriceAndStock['product'][0]['SellPrice'] ?? $price['price'];
            $finalPrice = $finalPriceRial / 10;
            $finalStock = $getPriceAndStock['product'][0]['Few'] ?? $price['stock'];

            foreach ($product->prices()->withTrashed()->get() as $product_price) {
                $product_price_attributes = $product_price->get_attributes()->get()->pluck('id')->toArray();

                sort($attributes);
                sort($product_price_attributes);

                if ($attributes == $product_price_attributes) {
                    $update_price = $product_price;
                    break;
                }
            }

            $commonData = [
                "productCode" => $price["productCode"],
                "price" => $finalPrice,
                "discount" => $price["discount"],
                "discount_price" => get_discount_price($finalPrice, $price["discount"], $product),
                "regular_price" => get_discount_price($finalPrice, 0, $product),
                "stock" => $finalStock,
                "cart_max" => $price["cart_max"],
                "cart_min" => $price["cart_min"],
                "discount_expire_at" => $price["discount_expire_at"] ? Jalalian::fromFormat('Y-m-d H:i:s', $price["discount_expire_at"])->toCarbon() : null,
            ];

            if ($update_price) {
                $update_price->update(array_merge($commonData, [
                    "deleted_at" => null,
                ]));

                $update_price->get_attributes()->sync($attributes);
                $prices_id[] = $update_price->id;
            } else {
                $insert_price = $product->prices()->create($commonData);

                foreach ($attributes as $attribute) {
                    $insert_price->get_attributes()->attach([$attribute]);
                }

                $prices_id[] = $insert_price->id;
            }
        }

        $product->prices()->whereNotIn('id', $prices_id)->delete();

        DB::table('cart_product')
            ->where('product_id', $product->id)
            ->whereNotNull('price_id')
            ->whereNotIn('price_id', $prices_id)
            ->delete();

        event(new ProductPricesChanged($product));
    }

    private function updateProductFiles(Product $product, Request $request)
    {
        if ($product->isPhysical()) {
            return;
        }

        $prices_id = [];
        $ordering = 1;

        foreach ($request->download_files as $price) {

            $update_price = false;

            if (isset($price['price_id'])) {
                $update_price = $product->prices()->withTrashed()->where('prices.id', $price['price_id'])->first();
            }

            if ($update_price) {

                $update_price->update([
                    "price" => $price["price"],
                    "discount" => $price["discount"],
                    "discount_price" => get_discount_price($price["price"], $price["discount"], $product),
                    "regular_price" => get_discount_price($price["price"], 0, $product),
                    "deleted_at" => null,
                    "ordering" => $ordering++
                ]);

                $update_price->updateFile($price['title'], $price['file'] ?? null, $price['status']);

                $prices_id[] = $update_price->id;
            } else {
                $insert_price = $product->prices()->create(
                    [
                        "price" => $price["price"],
                        "discount" => $price["discount"],
                        "discount_price" => get_discount_price($price["price"], $price["discount"], $product),
                        "regular_price" => get_discount_price($price["price"], $price["discount"], $product),
                        "ordering" => $ordering++
                    ]
                );

                $insert_price->createFile($price['title'], $price['file'], $price['status']);

                $prices_id[] = $insert_price->id;
            }
        }

        $delete_prices = $product->prices()->whereNotIn('id', $prices_id)->get();

        foreach ($delete_prices as $delete_price) {
            $file = $delete_price->file;

            if ($file) {
                Storage::disk('downloads')->delete('product-files/' . $file->file);
                $file->delete();
            }

            $delete_price->delete();
        }
    }

    private function updateProductSpecifications(Product $product, Request $request)
    {
        $product->specifications()->detach();
        $group_ordering = 0;

        if ($request->specification_group) {
            foreach ($request->specification_group as $group) {

                if (!isset($group['specifications'])) {
                    continue;
                }

                $spec_group = SpecificationGroup::firstOrCreate([
                    'name' => $group['name'],
                ]);

                $specification_ordering = 0;

                foreach ($group['specifications'] as $specification) {
                    $spec = Specification::firstOrCreate([
                        'name' => $specification['name']
                    ]);

                    $product->specifications()->attach([
                        $spec->id => [
                            'specification_group_id' => $spec_group->id,
                            'group_ordering' => $group_ordering,
                            'specification_ordering' => $specification_ordering++,
                            'value' => $specification['value'],
                            'special' => isset($specification['special']) ? true : false
                        ]
                    ]);
                }

                $group_ordering++;
            }
        }
    }

    private function updateProductBrand(Product $product, Request $request)
    {
        if ($request->brand) {
            $brand = Brand::firstOrCreate(
                [
                    'name' => $request->brand,
                    'lang' => app()->getLocale(),
                ],
                [
                    'slug' => $request->brand,
                ]
            );

            $product->update([
                'brand_id' => $brand->id
            ]);
        }
    }

    private function updateProductImages(Product $product, Request $request)
    {
        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $file = $request->image;
            $name = uniqid() . '_' . $product->id . '.' . $file->getClientOriginalExtension();
            $request->image->storeAs('products', $name);

            $product->image = '/uploads/products/' . $name;
            $product->save();
        }

        $product_images = $product->gallery()->pluck('image')->toArray();
        $images = explode(',', $request->images);
        $deleted_images = array_diff($product_images, $images);

        foreach ($deleted_images as $del_img) {
            $del_img = $product->gallery()->where('image', $del_img)->first();

            if (!$del_img) {
                continue;
            }

            if (Storage::disk('public')->exists($del_img)) {
                Storage::disk('public')->delete($del_img);
            }

            $del_img->delete();
        }

        $ordering = 1;

        if ($request->images) {

            foreach ($images as $image) {

                if (Storage::exists('tmp/' . $image)) {

                    Storage::move('tmp/' . $image, 'products/' . $image);

                    $product->gallery()->create([
                        'image' => '/uploads/products/' . $image,
                        'ordering' => $ordering++,
                    ]);
                } else {
                    $product->gallery()->where('image', $image)->update([
                        'ordering' => $ordering++,
                    ]);
                }
            }
        }
    }

    private function updateProductCategories(Product $product, Request $request)
    {
        if ($request->categories) {
            $product->categories()->sync(array_merge($request->categories, [$product->category_id]));
        } else {
            $product->categories()->sync([$product->category_id]);
        }
    }

    private function updateProductLabels(Product $product, Request $request)
    {
        $label_ids = [];

        if ($request->labels) {
            $labels = explode(',', $request->labels);

            foreach ($labels as $item) {
                $label = Label::firstOrCreate([
                    'title' => $item,
                    'lang' => app()->getLocale(),
                ]);

                $label_ids[] = $label->id;
            }
        }

        $product->labels()->sync($label_ids);
    }

    private function updateProductSizes(Product $product, Request $request)
    {
        $product->sizes()->detach();

        if (!$request->sizes) return;

        $ordering = 1;
        $groupOrdering = 1;

        foreach ($request->sizes as $group => $sizes) {

            foreach ($sizes as $size_id => $value) {
                $product->sizes()->attach(
                    [
                        $size_id => [
                            'group' => $groupOrdering,
                            'value' => $value,
                            'ordering' => $ordering++
                        ]
                    ]
                );
            }

            $groupOrdering++;
        }
    }

    private function exportExcel($products, Request $request)
    {
        return Excel::download(new ProductsExport($products, $request), 'products.xlsx');
    }

    private function exportPrint($products, Request $request)
    {
        //
    }

    private function getDigikalaProduct($request)
    {
        try {
            $url = $request->digikala_product;

            // Regular expression to extract the number from the URL
            $pattern = "/(?<=dkp-)\d+(?=[\/%])/";

            preg_match($pattern, $url, $matches);
            $productID = $matches[0];

            $response = Http::get("https://api.digikala.com/v1/product/$productID/")->json();

            $data = $response['data']['product'];

            $autoFillData = ['specificationGroups' => []];

            foreach ($data['specifications'] as $group) {
                $specifications = [];

                foreach ($group['attributes'] as $specification) {
                    $specifications[] = [
                        'special' => 0,
                        'name' => $specification['title'],
                        'value' => implode('&#13;&#10;', $specification['values'])
                    ];
                }

                $autoFillData['specificationGroups'][] = ['name' => $group['title'], 'specifications' => $specifications];

                return $autoFillData;
            }
        } catch (Exception $e) {
            return null;
        }
    }

    private function getCopyProduct($request)
    {
        $copy_product = Product::where('slug', $request->product)->first();

        if (!$copy_product) return;

        $autoFillData = ['specificationGroups' => []];

        foreach ($copy_product->specificationGroups->unique() as $group) {
            $specifications = [];

            foreach ($copy_product->specifications()->where('specification_group_id', $group->id)->get() as $specification) {
                $specifications[] = [
                    'special' => $specification->pivot->special,
                    'name' => $specification->name,
                    'value' => $specification->pivot->value
                ];
            }

            $autoFillData['specificationGroups'][] = ['name' => $group->name, 'specifications' => $specifications];
        }

        return $autoFillData;
    }

    private function getAutoFillData($request)
    {
        $autoFillData = null;

        if ($request->product) {
            $autoFillData = $this->getCopyProduct($request);
        }

        if ($request->digikala_product) {
            $autoFillData = $this->getDigikalaProduct($request);
        }

        return $autoFillData;
    }

    public function showUploadFormExcel()
    {
        return view('back.products.excelUpload');
    }

    public function uploadFileExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            DB::beginTransaction();

            // صفر کردن موجودی همه محصولات قبل از شروع
            Price::query()->update(['stock' => 0]);

            // خواندن داده‌های اکسل
            $file = Excel::toArray([], $request->file('file'));

            // حذف ردیف سرستون
            array_shift($file[0]);

            $updateCount = 0;

            $data = $file[0];
            foreach ($data as $item) {
                $productCode = $item[2];
                $stock = $item[3];
                $price = $item[4] / 10;

                $findPrice = Price::query()->where('productCode', $productCode)->exists();
                if ($findPrice) {
                    if ($price == 0 || $price == "0") {
                        $update = Price::query()->where('productCode', $productCode)->update([
                            'price' => $price,
                            'regular_price' => $price,
                            'stock' => 0
                        ]);
                    } else {
                        $update = Price::query()->where('productCode', $productCode)->update([
                            'price' => $price,
                            'regular_price' => $price,
                            'stock' => $stock
                        ]);
                    }
                    if ($update) {
                        $updateCount++;
                        Log::info("✅ محصول آپدیت شد - ID: {$productCode}, قیمت جدید: {$price}, موجودی جدید: {$stock}");
                    } else {
                        Log::warning("❌ محصول آپدیت نشد - ID: {$productCode}, قیمت جدید: {$price}, موجودی جدید: {$stock}");
                        $updateCount--;
                    }
                } else {
                    Log::warning("❌ قیمت پیدا نشد - ID: {$productCode} (موجودی آن صفر باقی می‌ماند)");
                }
            }

            Log::info("🔢 تعداد کل محصولات آپدیت‌شده: {$updateCount}");
            DB::commit();
            toastr()->success('موجودی و قیمت‌ها به‌روزرسانی شد.');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ خطا در آپدیت قیمت‌ها: " . $e->getMessage());
            return response()->json(['error' => 'خطایی رخ داد: ' . $e->getMessage()], 500);
        }
    }


    public function downloadFileExcelTemplate()
    {
        $filePath = public_path('files/excelTemplate/asa_accounts.xlsx');
        if (file_exists($filePath)) {
            return response()->download($filePath);
        } else {
            return false;
        }
    }


    public function getProductPriceAndStock(string $productCode, string $mainGroupErpCode, string $sideGroupErpCode): array
    {

        $params = http_build_query([
            'code' => $productCode,
            'maingrouperpcode' => $mainGroupErpCode,
            'sidegrouperpcode' => $sideGroupErpCode,
        ]);

        $response = $this->holoo->callApi("Product?$params");
        dd($response);

        if (!empty($response['product'][0])) {
            $product = $response['product'][0];

            return [
                'price' => floatval($product['SellPrice'] ?? 0),
                'stock' => floatval($product['Few'] ?? 0),
            ];
        }

        return [
            'price' => 0,
            'stock' => 0,
        ];
    }


}
