<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Size; // اضافه شده
use Carbon\Carbon;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(5);
        return view('products.index', compact('products'));
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function create()
    {
        $categories = Category::all();
        $sizes = config('sizes', ['S', 'M', 'L', 'XL', 'XXL']); // لیست سایزها

        return view('products.create', compact('categories', 'sizes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'primary_image' => 'required|image',
            'name' => 'required|string',
            'category_id' => 'required|integer',
            'description' => 'required',
            'price' => 'required|integer',
            'status' => 'required|integer',
            'quantity' => 'required|integer',
            'sale_price' => 'nullable|integer',
            'date_on_sale_from' => 'nullable|string',
            'date_on_sale_to' => 'nullable|string',
            'images.*' => 'nullable|image',
        ]);

        // ذخیره تصویر اصلی
        $primaryImageName = Carbon::now()->microsecond . '-' . $request->primary_image->getClientOriginalName();
        Storage::disk('public')->putFileAs('images/products', $request->file('primary_image'), $primaryImageName);

        // ذخیره تصاویر دیگر
        $fileNameImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $fileNameImage = Carbon::now()->microsecond . '-' . $image->getClientOriginalName();
                $image->storeAs('images/products/', $fileNameImage, 'public');
                $fileNameImages[] = $fileNameImage;
            }
        }

        DB::beginTransaction();

        try {
            // ایجاد محصول
            $product = Product::create([
                'name' => $request->name,
                'slug' => $this->makeSlug($request->name),
                'category_id' => $request->category_id,
                'primary_image' => $primaryImageName,
                'description' => $request->description,
                'status' => $request->status,
                'price' => $request->price,
                'quantity' => $request->quantity,
                'sale_price' => !empty($request->sale_price) ? $request->sale_price : 0,
                'date_on_sale_from' => !empty($request->date_on_sale_from) ? getMiladiDate($request->date_on_sale_from) : null,
                'date_on_sale_to' => !empty($request->date_on_sale_to) ? getMiladiDate($request->date_on_sale_to) : null,
            ]);

            // ذخیره تصاویر دیگر در جدول product_images
            foreach ($fileNameImages as $fileNameImage) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $fileNameImage,
                ]);
            }

            // ===== پردازش سایزها =====
            $sizesData = $request->input('sizes', []);
            foreach ($sizesData as $sizeName => $data) {
                if (isset($data['checked']) && $data['checked'] == 1) {
                    Size::create([
                        'product_id' => $product->id,
                        'size_name' => $sizeName,
                        'stock' => 1, // موجود
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('product.index')->with('success', 'محصول با موفقیت ایجاد شد');
        } catch (\Exception $e) {
            DB::rollBack();
            // حذف فایل‌های آپلود شده در صورت خطا (اختیاری)
            Storage::disk('public')->delete('images/products/' . $primaryImageName);
            foreach ($fileNameImages as $img) {
                Storage::disk('public')->delete('images/products/' . $img);
            }
            throw $e;
        }
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $sizes = config('sizes', ['S', 'M', 'L', 'XL', 'XXL']);

        // سایزهای موجود برای این محصول (برای مشخص کردن وضعیت چک‌باکس‌ها)
        $existingSizes = $product->sizes()->pluck('stock', 'size_name')->toArray();

        return view('products.edit', compact('product', 'categories', 'sizes', 'existingSizes'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'primary_image' => 'nullable|image',
            'name' => 'required|string',
            'category_id' => 'required|integer',
            'description' => 'required',
            'price' => 'required|integer',
            'status' => 'required|integer',
            'quantity' => 'required|integer',
            'sale_price' => 'nullable|integer',
            'date_on_sale_from' => 'nullable|date_format:Y/m/d H:i:s',
            'date_on_sale_to' => 'nullable|date_format:Y/m/d H:i:s',
            'images.*' => 'nullable|image',
        ]);

        $newPrimaryImage = null;
        $newImages = [];

        DB::beginTransaction();

        try {
            // ========== بروزرسانی تصویر اصلی ==========
            if ($request->hasFile('primary_image')) {
                if ($product->primary_image && Storage::disk('public')->exists('images/products/' . $product->primary_image)) {
                    Storage::disk('public')->delete('images/products/' . $product->primary_image);
                }
                $newPrimaryImage = Carbon::now()->microsecond . '-' . $request->primary_image->getClientOriginalName();
                $request->primary_image->storeAs('images/products/', $newPrimaryImage, 'public');
            } else {
                $newPrimaryImage = $product->primary_image;
            }

            $uploadedFiles = $request->file('images');
            if ($uploadedFiles && count($uploadedFiles) > 0) {
                // حذف تصاویر قبلی
                foreach ($product->images as $image) {
                    // dd($request->all(), $request->file('images'));
                    if (Storage::disk('public')->exists('images/products/' . $image->image)) {
                        Storage::disk('public')->delete('images/products/' . $image->image);

                        $image->delete();
                    }
                }

                foreach ($uploadedFiles as $image) {
                    $fileNameImage = Carbon::now()->microsecond . '-' . $image->getClientOriginalName();
                    $image->storeAs('images/products/', $fileNameImage, 'public');
                    $newImages[] = $fileNameImage;
                }
            }

            // ========== بروزرسانی اطلاعات محصول ==========
            $product->update([
                'name' => $request->name,
                'slug' => $request->name != $product->name ? $this->makeSlug($request->name) : $product->slug,
                'category_id' => $request->category_id,
                'primary_image' => $newPrimaryImage,
                'description' => $request->description,
                'status' => $request->status,
                'price' => $request->price,
                'quantity' => $request->quantity,
                'sale_price' => $request->sale_price !== null ? $request->sale_price : 0,
                'date_on_sale_from' => $request->date_on_sale_from !== null ? getMiladiDate($request->date_on_sale_from) : null,
                'date_on_sale_to' => $request->date_on_sale_to !== null ? getMiladiDate($request->date_on_sale_to) : null,
            ]);

            // ========== ذخیره تصاویر جدید در دیتابیس ==========
            foreach ($newImages as $fileNameImage) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $fileNameImage,
                ]);
            }

            // حذف همه سایزهای قبلی
            $product->sizes()->delete();

            $sizesData = $request->input('sizes', []);
            foreach ($sizesData as $sizeName => $data) {
                if (isset($data['checked']) && $data['checked'] == 1) {
                    Size::create([
                        'product_id' => $product->id,
                        'size_name' => $sizeName,
                        'stock' => 1,
                    ]);
                }
            }

            DB::commit();

            $product->refresh();

            // هدایت به صفحه نمایش با شکستن کش
            return redirect()
                ->route('product.index', ['product' => $product->slug . '?t=' . time()])
                ->with('success', 'محصول با موفقیت ویرایش شد');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->hasFile('primary_image') && $newPrimaryImage) {
                Storage::disk('public')->delete('images/products/' . $newPrimaryImage);
            }
            foreach ($newImages as $img) {
                Storage::disk('public')->delete('images/products/' . $img);
                dd($img);
            }

            return back()->with('error', 'خطا در ویرایش محصول: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        // حذف فایل‌های تصویر
        Storage::disk('public')->delete('images/products/' . $product->primary_image);
        foreach ($product->images as $image) {
            Storage::disk('public')->delete('images/products/' . $image->image);
        }
        // حذف سایزها (به دلیل cascade در دیتابیس، خودکار حذف می‌شوند)
        $product->delete();

        return redirect()->route('product.index')->with('warning', 'محصول با موفقیت حذف شد');
    }

    public function makeSlug($string)
    {
        $slug = slugify($string);
        $count = Product::whereRaw("slug RLIKE '^{$slug}(-[0-9]+)?$'")->count();
        $result = $count ? "{$slug}-{$count}" : $slug;
        return $result;
    }
}
