@extends('layouts.master')

@section('title', 'Dashboard Penjualan')

@section('content')
<div class="container-fluid">
    {{-- HEADER (Tetap) --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#importCsvModal">
                <i class="bi bi-cart-plus-fill me-1"></i> Import Penjualan
            </button>
            <button type="button" class="btn btn-info text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#importCustomerModal">
                <i class="bi bi-people-fill me-1"></i> Import Pelanggan
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert"><i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- STATS CARDS --}}
    <div class="mb-4 row">
        <div class="mb-3 col-md-4">
            <div class="border-0 shadow-sm card h-100">
                <div class="text-center card-body">
                    <div class="mb-2"><i class="bi bi-box-seam" style="font-size:2rem;color:#a60000;"></i></div>
                    <h5 class="mb-1 card-title">Total Produk</h5>
                    <h2 class="fw-bold">{{ $totalProducts }}</h2>
                </div>
            </div>
        </div>
        <div class="mb-3 col-md-4">
            <div class="border-0 shadow-sm card h-100">
                <div class="text-center card-body">
                    <div class="mb-2"><i class="bi bi-people" style="font-size:2rem;color:#a60000;"></i></div>
                    <h5 class="mb-1 card-title">Total Pelanggan</h5>
                    <h2 class="fw-bold">{{ $totalCustomers }}</h2>
                </div>
            </div>
        </div>
        <div class="mb-3 col-md-4">
            <div class="border-0 shadow-sm card h-100">
                <div class="text-center card-body">
                    <div class="mb-2"><i class="bi bi-cash-stack" style="font-size:2rem;color:#a60000;"></i></div>
                    <h5 class="mb-1 card-title">Pendapatan Hari Ini</h5>
                    <h2 class="fw-bold">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- CHART UTAMA PENJUALAN --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="border-0 shadow-sm card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                        <h5 class="card-title mb-0" id="chartTitle">Penjualan Bulan Ini</h5>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div id="date-range-picker" class="d-flex gap-2">
                                <input type="date" id="start_date" class="form-control form-control-sm" value="{{ date('Y-m-01') }}">
                                <span class="align-self-center">-</span>
                                <input type="date" id="end_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                                <button class="btn btn-sm btn-success" id="apply-date-filter"><i class="bi bi-search"></i></button>
                            </div>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary filter-btn active" data-filter="monthly">Bulan Ini</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary filter-btn" data-filter="yearly">Tahun Ini</button>
                            </div>
                        </div>
                    </div>
                    <div style="position: relative; height:300px; width:100%">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PRODUK & MEMBER --}}
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><h5 class="mb-0 card-title"><i class="bi bi-trophy-fill text-warning me-2"></i>Produk Terlaris</h5></div>
                <div class="card-body">
                    <div class="mb-4" style="height: 200px;"><canvas id="bestSellingChart"></canvas></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-sm">
                            <thead class="table-light"><tr><th>Nama</th><th class="text-center">Jual</th></tr></thead>
                            <tbody id="bestSellingTableBody">
                                {{-- Data diisi via JS/Blade --}}
                                @forelse($bestSellingProducts as $product)
                                <tr><td class="fw-semibold small">{{ Str::limit($product->product_name, 20) }}</td><td class="text-center"><span class="badge bg-success rounded-pill">{{ $product->total_sold }}</span></td></tr>
                                @empty
                                <tr><td colspan="2" class="text-center small text-muted">Nihil</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><h5 class="mb-0 card-title"><i class="bi bi-star-fill text-primary me-2"></i>Top Membership</h5></div>
                <div class="card-body">
                    <div class="mb-4" style="height: 200px;"><canvas id="topCustomerChart"></canvas></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-sm">
                            <thead class="table-light"><tr><th>Nama</th><th class="text-center">Poin</th></tr></thead>
                            <tbody id="topCustomerTableBody">
                                @forelse($topCustomers as $customer)
                                <tr><td>{{ Str::limit($customer->name, 15) }}</td><td class="text-center fw-bold">{{ $customer->loyalty_points }}</td></tr>
                                @empty
                                <tr><td colspan="2" class="text-center small text-muted">Nihil</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><h5 class="mb-0 card-title"><i class="bi bi-pie-chart-fill text-info me-2"></i>Kategori Favorit</h5></div>
                <div class="card-body">
                    <div style="height: 200px;"><canvas id="topCategoriesChart"></canvas></div>
                    <div class="mt-3 text-center small text-muted">Distribusi penjualan.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- TRANSAKSI TERAKHIR (TETAP SAMA) --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h5 class="mb-0 card-title"><i class="bi bi-clock-history text-secondary me-2"></i>Transaksi Terakhir</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th class="ps-4">No. Struk</th><th>Waktu</th><th>Pelanggan</th><th>Total</th><th class="text-center">Status</th></tr></thead>
                            <tbody>
                                @forelse($recentSales as $sale)
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">{{ $sale->transaction_id }}</td>
                                    <td class="text-muted small">{{ $sale->created_at->format('d M H:i') }}</td>
                                    <td>{{ $sale->customer->name ?? 'Umum' }}</td>
                                    <td class="fw-bold">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                    <td class="text-center"><span class="badge bg-success rounded-pill">Selesai</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada transaksi</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALS TETAP SAMA (Import dll) --}}
<div class="modal fade" id="importCsvModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('dashboard.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Import Penjualan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><input class="form-control" type="file" name="csv_file" accept=".csv" required></div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Upload</button></div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="importCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('dashboard.import-customers') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-info text-white"><h5 class="modal-title">Import Pelanggan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><input class="form-control" type="file" name="csv_file_customer" accept=".csv" required></div>
                <div class="modal-footer"><button type="submit" class="btn btn-info text-white">Upload</button></div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('addon-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>.filter-btn.active { background-color: #a60000; color: white; border-color: #a60000; }</style>
@endpush

@push('addon-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const formatRupiah = (value) => 'Rp ' + value.toLocaleString('id-ID');

        // VARIABEL CHART GLOBAL
        let salesChart = null;
        let bestSellingChart = null;
        let topCustomerChart = null;
        let topCategoriesChart = null;

        // 1. FUNGSI RENDER CHART PENJUALAN
        const ctxSales = document.getElementById('salesChart').getContext('2d');
        function renderSalesChart(labels, data) {
            if (salesChart) salesChart.destroy();
            salesChart = new Chart(ctxSales, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{ label: 'Pendapatan', data: data, borderColor: '#00A63E', backgroundColor: 'rgba(0,166,62,0.1)', fill: true, tension: 0.3, pointRadius: 4, pointBackgroundColor: '#00A63E' }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(context) { return formatRupiah(context.parsed.y); } } } }, scales: { y: { beginAtZero: true, ticks: { callback: function(value) { if(value >= 1000000) return 'Rp ' + (value/1000000) + 'Jt'; return formatRupiah(value); } } } } }
            });
        }

        // 2. FUNGSI RENDER PRODUK TERLARIS
        const ctxProduct = document.getElementById('bestSellingChart').getContext('2d');
        function renderProductChart(labels, data) {
            if (bestSellingChart) bestSellingChart.destroy();
            bestSellingChart = new Chart(ctxProduct, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{ label: 'Terjual', data: data, backgroundColor: 'rgba(0, 166, 62, 0.7)', borderWidth: 1 }]
                },
                options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
            });
        }

        // 3. FUNGSI RENDER PELANGGAN
        const ctxCustomer = document.getElementById('topCustomerChart').getContext('2d');
        function renderCustomerChart(labels, data) {
            if (topCustomerChart) topCustomerChart.destroy();
            topCustomerChart = new Chart(ctxCustomer, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{ data: data, backgroundColor: ['#FFC107', '#0DCAF0', '#6C757D', '#20C997', '#6610f2'], hoverOffset: 4 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } } } }
            });
        }

        // 4. FUNGSI RENDER KATEGORI
        const ctxCategory = document.getElementById('topCategoriesChart').getContext('2d');
        function renderCategoryChart(labels, data) {
            if (topCategoriesChart) topCategoriesChart.destroy();
            topCategoriesChart = new Chart(ctxCategory, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{ data: data, backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'], hoverOffset: 4 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } } } }
            });
        }

        // --- INISIALISASI AWAL (DATA DARI BLADE) ---
        renderSalesChart(@json($chartLabels), @json($chartData));
        
        const initProd = @json($bestSellingProducts);
        renderProductChart(initProd.map(i => i.product_name), initProd.map(i => i.total_sold));

        const initCust = @json($topCustomers);
        renderCustomerChart(initCust.map(i => i.name), initCust.map(i => i.loyalty_points));

        const initCat = @json($topCategories);
        renderCategoryChart(initCat.map(i => i.category_name), initCat.map(i => i.total_sold));


        // --- LOGIKA FILTER & UPDATE CHART ---
        const filterBtns = document.querySelectorAll('.filter-btn');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const applyBtn = document.getElementById('apply-date-filter');
        const chartContainer = document.getElementById('salesChart');

        // Fungsi Helper untuk Update Tabel HTML (Opsional tapi bagus)
        function updateTableBody(tableId, rowsHTML) {
            const tbody = document.getElementById(tableId);
            if(tbody) tbody.innerHTML = rowsHTML;
        }

        function fetchData(filter, start = null, end = null) {
            chartContainer.style.opacity = '0.5';
            let url = `{{ route('dashboard.chart-data') }}?filter=${filter}`;
            if (filter === 'custom') url += `&start_date=${start}&end_date=${end}`;
            
            fetch(url)
                .then(response => response.json())
                .then(result => {
                    // Update Judul
                    document.getElementById('chartTitle').innerText = result.title;
                    
                    // 1. Update Chart Penjualan
                    renderSalesChart(result.labels, result.data);

                    // 2. Update Chart & Tabel Produk
                    const prodNames = result.bestSellingProducts.map(p => p.product_name);
                    const prodSold = result.bestSellingProducts.map(p => p.total_sold);
                    renderProductChart(prodNames, prodSold);
                    
                    // Update Tabel Produk (Simple string building)
                    let prodRows = '';
                    result.bestSellingProducts.forEach(p => {
                        prodRows += `<tr><td class="fw-semibold small">${p.product_name.substring(0,20)}</td><td class="text-center"><span class="badge bg-success rounded-pill">${p.total_sold}</span></td></tr>`;
                    });
                    updateTableBody('bestSellingTableBody', prodRows || '<tr><td colspan="2" class="text-center text-muted">Nihil</td></tr>');

                    // 3. Update Chart & Tabel Member
                    const custNames = result.topCustomers.map(c => c.name);
                    const custPoints = result.topCustomers.map(c => c.loyalty_points);
                    renderCustomerChart(custNames, custPoints);

                    let custRows = '';
                    result.topCustomers.forEach(c => {
                        custRows += `<tr><td>${c.name.substring(0,15)}</td><td class="text-center fw-bold">${c.loyalty_points}</td></tr>`;
                    });
                    updateTableBody('topCustomerTableBody', custRows || '<tr><td colspan="2" class="text-center text-muted">Nihil</td></tr>');

                    // 4. Update Chart Kategori
                    const catNames = result.topCategories.map(c => c.category_name);
                    const catSold = result.topCategories.map(c => c.total_sold);
                    renderCategoryChart(catNames, catSold);

                })
                .catch(err => console.error(err))
                .finally(() => { chartContainer.style.opacity = '1'; });
        }

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                fetchData(this.getAttribute('data-filter'));
            });
        });

        applyBtn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            if(!startDateInput.value || !endDateInput.value) { alert("Pilih tanggal!"); return; }
            fetchData('custom', startDateInput.value, endDateInput.value);
        });
    });
</script>
@endpush