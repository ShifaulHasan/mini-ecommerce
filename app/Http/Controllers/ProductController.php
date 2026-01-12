<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'brand', 'unit'])->paginate(15);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        $units = Unit::all();
        $productCode = Product::generateProductCode();
        
        return view('products.create', compact('categories', 'brands', 'units', 'productCode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'product_code' => 'required|unique:products,product_code',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->all();

        // Handle brand (create if new)
        if ($request->has('brand_new') && $request->brand_new) {
            $brand = Brand::create(['name' => $request->brand_new]);
            $data['brand_id'] = $brand->id;
        }

        // Handle unit (create if new)
        if ($request->has('unit_new') && $request->unit_new) {
            $unit = Unit::create(['name' => $request->unit_new, 'short_name' => strtolower(substr($request->unit_new, 0, 3))]);
            $data['unit_id'] = $unit->id;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images/products'), $imageName);
            $data['image'] = $imageName;
        }

        Product::create($data);
        return redirect()->route('products.index')->with('success', 'Product created successfully!');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $brands = Brand::all();
        $units = Unit::all();
        return view('products.edit', compact('product', 'categories', 'brands', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|max:255',
            'product_code' => 'required|unique:products,product_code,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->all();

        // Handle brand
        if ($request->has('brand_new') && $request->brand_new) {
            $brand = Brand::firstOrCreate(['name' => $request->brand_new]);
            $data['brand_id'] = $brand->id;
        }

        // Handle unit
        if ($request->has('unit_new') && $request->unit_new) {
            $unit = Unit::firstOrCreate(['name' => $request->unit_new]);
            $data['unit_id'] = $unit->id;
        }

        // Handle image
        if ($request->hasFile('image')) {
            if ($product->image && file_exists(public_path('images/products/' . $product->image))) {
                unlink(public_path('images/products/' . $product->image));
            }
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images/products'), $imageName);
            $data['image'] = $imageName;
        }

        $product->update($data);
        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        if ($product->image && file_exists(public_path('images/products/' . $product->image))) {
            unlink(public_path('images/products/' . $product->image));
        }
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
    }

    // Generate new product code (AJAX)
    public function generateCode()
    {
        return response()->json(['code' => Product::generateProductCode()]);
    }
}