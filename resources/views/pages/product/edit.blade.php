@extends('layouts.master')

@section('title', 'Edit Produk')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Produk</h3>
    </div>
    <form method="POST" action="{{ route('product.update', $product->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Nama Produk</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                            name="name" value="{{ old('name', $product->name) }}" required
                            placeholder="Masukkan nama produk">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="code" class="form-label fw-bold">Kode Produk</label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code"
                            name="code" value="{{ old('code', $product->code) }}" required placeholder="Contoh: PRD001">
                        @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="category_id" class="form-label fw-bold">Kategori</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id"
                            name="category_id" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) ==
                                $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="price" class="form-label fw-bold">Harga</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control @error('price') is-invalid @enderror" id="price"
                                name="price" value="{{ old('price', $product->price) }}" required placeholder="0"
                                min="0" step="0.01">
                            @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="stock" class="form-label fw-bold">Stok</label>
                        <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock"
                            name="stock" value="{{ old('stock', $product->stock) }}" required placeholder="0" min="0">
                        @error('stock')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="minimum_stock" class="form-label fw-bold">Minimum Stok</label>
                        <input type="number" class="form-control @error('minimum_stock') is-invalid @enderror"
                            id="minimum_stock" name="minimum_stock"
                            value="{{ old('minimum_stock', $product->minimum_stock) }}" required placeholder="0"
                            min="0">
                        @error('minimum_stock')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label fw-bold">Deskripsi</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                    name="description" rows="3"
                    placeholder="Deskripsi produk (opsional)">{{ old('description', $product->description) }}</textarea>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-end">
                <a href="{{ route('product.index') }}" class="btn btn-secondary me-2">Batal</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </div>
    </form>
</div>
@endsection

