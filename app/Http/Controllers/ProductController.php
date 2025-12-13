<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        // Ambil semua produk dengan relasi kategori
        $products = Product::with('category')->get();
        
        // PERBAIKAN: Ambil data kategori untuk filter di halaman index
        $categories = Category::all();

        // Kirim variabel $products DAN $categories ke view
        return view('pages.product.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        // Tidak mengirim variabel $units karena sudah tidak dipakai
        return view('pages.product.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:products,code',
            'category_id' => 'required|exists:categories,id',
            // unit_id dihapus dari validasi
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            // image dihapus dari validasi
        ]);

        Product::create([
            'name' => $request->name,
            'code' => $request->code,
            'category_id' => $request->category_id,
            // unit_id tidak diisi (akan otomatis NULL di database jika sudah dimigrasi nullable)
            'price' => $request->price,
            'stock' => $request->stock,
            'minimum_stock' => $request->minimum_stock ?? 1, 
            'description' => $request->description,
            'status' => $request->status,
            // image dihapus
        ]);

        return redirect()->route('product.index')->with('success', 'Produk berhasil ditambahkan!');
    }

    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);
        return view('pages.product.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        return view('pages.product.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:products,code,' . $id,
            'category_id' => 'required|exists:categories,id',
            // unit_id dihapus
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        // Update semua data kecuali image (karena sudah tidak ada)
        $product->update($request->all());

        return redirect()->route('product.index')->with('success', 'Produk berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->route('product.index')->with('success', 'Produk berhasil dihapus!');
    }
}