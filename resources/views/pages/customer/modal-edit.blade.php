<!-- Modal -->
<div class="modal fade" id="editModal-{{ $customer->id }}" tabindex="-1"
    aria-labelledby="editModalLabel-{{ $customer->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('customer.update', $customer->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="editModalLabel-{{ $customer->id }}">Edit Konsumen</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name-{{ $customer->id }}" class="form-label">Nama Konsumen</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                            id="name-{{ $customer->id }}" name="name" value="{{ old('name', $customer->name) }}"
                            required>
                        @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="phone-{{ $customer->id }}" class="form-label">No. Telepon</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                            id="phone-{{ $customer->id }}" name="phone" value="{{ old('phone', $customer->phone) }}"
                            required>
                        @error('phone')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="loyalty_points" class="form-label">Poin Loyalitas</label>
                        <input type="number" min="0" class="form-control @error('loyalty_points') is-invalid @enderror" id="loyalty_points"
                            name="loyalty_points" value="{{ old('loyalty_points', 0) }}">
                        <div class="form-text">Masukkan angka 0 jika pelanggan baru belum memiliki poin.</div>
                        @error('loyalty_points')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
