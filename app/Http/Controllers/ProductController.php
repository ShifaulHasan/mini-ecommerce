<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        // product_warehouse  total stock calculate  products load 
        $products = Product::with(['category', 'brand', 'unit'])
            ->select('products.*')
            ->selectRaw('COALESCE((SELECT SUM(quantity) FROM product_warehouse WHERE product_warehouse.product_id = products.id), 0) as total_stock')
            ->paginate(15);
        
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

        DB::beginTransaction();
        try {
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

            // Stock field  0  product_warehouse  calculate 
            $data['stock'] = 0;

            $product = Product::create($data);

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Product created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error creating product: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $brands = Brand::all();
        $units = Unit::all();
        
        // Product warehouse-wise stock load
        $product->warehouseStocks = DB::table('product_warehouse')
            ->join('warehouses', 'product_warehouse.warehouse_id', '=', 'warehouses.id')
            ->where('product_warehouse.product_id', $product->id)
            ->select('warehouses.name as warehouse_name', 'product_warehouse.quantity', 'product_warehouse.warehouse_id')
            ->get();
        
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

        DB::beginTransaction();
        try {
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

            // Stock update  product_warehouse auto-calculate 
            unset($data['stock']);

            $product->update($data);

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Product updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error updating product: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            if ($product->image && file_exists(public_path('images/products/' . $product->image))) {
                unlink(public_path('images/products/' . $product->image));
            }

            // product_warehouse থ delete 
            DB::table('product_warehouse')->where('product_id', $product->id)->delete();
            
            $product->delete();
            
            DB::commit();
            return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error deleting product: ' . $e->getMessage()]);
        }
    }

    // Generate new product code (AJAX)
    public function generateCode()
    {
        return response()->json(['code' => Product::generateProductCode()]);
    }
    
    // Product  warehouse-wise stock 
    public function getWarehouseStocks($productId)
    {
        return DB::table('product_warehouse')
            ->join('warehouses', 'product_warehouse.warehouse_id', '=', 'warehouses.id')
            ->where('product_warehouse.product_id', $productId)
            ->select('warehouses.name', 'product_warehouse.quantity')
            ->get();
    }
}