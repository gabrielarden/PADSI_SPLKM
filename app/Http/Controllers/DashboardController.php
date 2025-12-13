<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Interface\ProductServiceInterface;
use App\Services\Interface\CustomerServiceInterface;
use App\Services\SalesService; 

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse; 
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Sale; 
use App\Models\SalesItem; 
use App\Models\Customer;
use App\Models\Product;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly ProductServiceInterface $productService,
        private readonly CustomerServiceInterface $customerService,
        private readonly SalesService $salesService 
    ) {}

    public function index(Request $request): View
    {
        $totalProducts = $this->productService->getAllProducts()->count();
        $totalCustomers = $this->customerService->getAllCustomers()->count();
        $todayRevenue = $this->salesService->getDailySalesTotal();

        // Default: Bulan Ini
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        
        // 1. Data Chart Penjualan
        $salesReport = $this->salesService->getSalesReport($startOfMonth, $endOfMonth);
        $chartLabels = [];
        $chartData = [];
        if (isset($salesReport['daily_breakdown'])) {
            foreach ($salesReport['daily_breakdown'] as $date => $data) {
                $chartLabels[] = Carbon::parse($date)->format('d M');
                $chartData[] = $data['total'];
            }
        }

        // 2. Produk Terlaris (Filter Bulan Ini)
        $bestSellingProducts = $this->salesService->getBestSellingProducts($startOfMonth, $endOfMonth, 5); 

        // 3. Kategori Terlaris (Filter Bulan Ini)
        $topCategories = $this->salesService->getTopCategories($startOfMonth, $endOfMonth, 5);

        // 4. Top Membership (Global - Poin Akumulasi)
        $topCustomers = Customer::orderByDesc('loyalty_points')->limit(5)->get();

        // 5. Transaksi Terakhir (Global - Terbaru)
        $recentSales = $this->salesService->getRecentSales(5);
        
        // 6. Stok Menipis
        $lowStockProducts = Product::whereColumn('stock', '<=', 'minimum_stock')
            ->where('status', 1)->orderBy('stock', 'asc')->limit(5)->get();

        return view('pages.dashboard', [
            'totalProducts' => $totalProducts,
            'totalCustomers' => $totalCustomers,
            'todayRevenue' => $todayRevenue,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'bestSellingProducts' => $bestSellingProducts,
            'topCustomers' => $topCustomers,
            'recentSales' => $recentSales,
            'topCategories' => $topCategories,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }

    // --- UPDATE PENTING DI SINI ---
    public function getChartData(Request $request): JsonResponse
    {
        $filter = $request->query('filter', 'monthly');
        $now = Carbon::now();
        $labels = [];
        $data = [];

        // Tentukan Range Tanggal
        if ($filter === 'weekly') {
            $startDate = $now->startOfWeek()->toDateString();
            $endDate = $now->endOfWeek()->toDateString();
        } elseif ($filter === 'yearly') {
            $startDate = $now->startOfYear()->toDateString();
            $endDate = $now->endOfYear()->toDateString();
        } elseif ($filter === 'custom') {
            $startDate = $request->query('start_date', $now->copy()->subDays(7)->toDateString());
            $endDate = $request->query('end_date', $now->toDateString());
        } else { 
            $startDate = $now->startOfMonth()->toDateString();
            $endDate = $now->endOfMonth()->toDateString();
        }

        // 1. Ambil Data Chart Penjualan
        $report = $this->salesService->getSalesReport($startDate, $endDate);
        $breakdown = $report['daily_breakdown'] ?? [];

        if ($filter === 'yearly') {
            // Logic Tahunan
            $monthlyData = [];
            for ($m = 1; $m <= 12; $m++) { $monthlyData[$m] = 0; }
            foreach ($breakdown as $date => $info) {
                $monthNumber = (int) Carbon::parse($date)->format('n');
                if (isset($monthlyData[$monthNumber])) { $monthlyData[$monthNumber] += $info['total']; }
            }
            foreach ($monthlyData as $monthNum => $total) {
                $labels[] = Carbon::create(null, $monthNum, 1)->format('M');
                $data[] = $total;
            }
            $title = 'Penjualan Tahun Ini';
        } else {
            // Logic Harian
            foreach ($breakdown as $date => $info) {
                $labels[] = Carbon::parse($date)->format('d M');
                $data[] = $info['total'];
            }
            $title = ($filter === 'custom') 
                ? 'Penjualan ' . Carbon::parse($startDate)->format('d M') . ' - ' . Carbon::parse($endDate)->format('d M')
                : 'Penjualan Bulan Ini';
        }

        // 2. Ambil Data Pendukung Lainnya (Sesuai Filter Tanggal)
        $bestSellingProducts = $this->salesService->getBestSellingProducts($startDate, $endDate, 5);
        $topCategories = $this->salesService->getTopCategories($startDate, $endDate, 5);
        
        // Membership tetap global (poin akumulasi), tapi kita kirim ulang untuk refresh
        $topCustomers = Customer::orderByDesc('loyalty_points')->limit(5)->get();

        // Return SEMUA data dalam JSON
        return response()->json([
            'title' => $title,
            'labels' => $labels,
            'data' => $data,
            'bestSellingProducts' => $bestSellingProducts, // Data Baru
            'topCategories' => $topCategories,             // Data Baru
            'topCustomers' => $topCustomers,               // Data Baru
        ]);
    }

    public function importCsv(Request $request): RedirectResponse
    {
        $request->validate(['csv_file' => 'required|mimes:csv,txt|max:2048']);

        if ($request->hasFile('csv_file')) {
            $file = $request->file('csv_file');
            $path = $file->getRealPath();

            DB::beginTransaction();
            try {
                $fileHandle = fopen($path, 'r');
                $isHeader = true; 
                $groupedData = [];

                while (($row = fgetcsv($fileHandle, 1000, ",")) !== FALSE) {
                    if ($isHeader) { $isHeader = false; continue; }
                    $trxId = $row[1] ?? null;
                    if (!$trxId) continue;

                    $cleanMoney = function($val) { return (float) preg_replace('/[^0-9]/', '', $val ?? '0'); };
                    $dateStr = ($row[2] ?? '') . ' ' . ($row[3] ?? '');
                    try { $createdAt = Carbon::createFromFormat('d-m-Y H:i:s', $dateStr); } 
                    catch (\Exception $e) { $createdAt = now(); }

                    $productName = $row[7] ?? 'Produk Tanpa Nama';
                    $qty         = (int) ($row[8] ?? 1);
                    $unitPrice   = $cleanMoney($row[9]);
                    $subtotalRow = $cleanMoney($row[13]);
                    $discountRow = $cleanMoney($row[14]);
                    $taxRow      = $cleanMoney($row[15]);
                    $totalRow    = $cleanMoney($row[18]);

                    if (!isset($groupedData[$trxId])) {
                        $rawPayment = strtolower($row[20] ?? '');
                        $paymentMethod = 'cash';
                        if (str_contains($rawPayment, 'non tunai')) $paymentMethod = 'transfer';
                        elseif (str_contains($rawPayment, 'qris')) $paymentMethod = 'qris';
                        
                        $groupedData[$trxId] = [
                            'header' => [
                                'transaction_id'  => $trxId,
                                'customer_id'     => null,
                                'user_id'         => 1,
                                'subtotal'        => 0, 
                                'tax_amount'      => 0, 
                                'discount_amount' => 0, 
                                'total_amount'    => 0, 
                                'paid_amount'     => 0,
                                'change_amount'   => 0,
                                'payment_method'  => $paymentMethod,
                                'status'          => ($row[19] == 'LUNAS') ? 'completed' : 'pending',
                                'created_at'      => $createdAt,
                                'updated_at'      => $createdAt,
                            ],
                            'items' => []
                        ];
                    }
                    $groupedData[$trxId]['items'][] = [
                        'product_id'   => null, 
                        'product_name' => $productName,
                        'quantity'     => $qty,
                        'unit_price'   => $unitPrice,
                        'total_price'  => $subtotalRow,
                    ];
                    $groupedData[$trxId]['header']['subtotal'] += $subtotalRow;
                    $groupedData[$trxId]['header']['total_amount'] += $totalRow;
                    $groupedData[$trxId]['header']['discount_amount'] = max($groupedData[$trxId]['header']['discount_amount'], $discountRow);
                    $groupedData[$trxId]['header']['tax_amount'] = max($groupedData[$trxId]['header']['tax_amount'], $taxRow);
                }
                fclose($fileHandle);

                foreach ($groupedData as $trxData) {
                    $headerData = $trxData['header'];
                    $headerData['paid_amount'] = $headerData['total_amount'];
                    $sale = Sale::updateOrCreate(['transaction_id' => $headerData['transaction_id']], $headerData);
                    $sale->salesItems()->delete();
                    foreach ($trxData['items'] as $itemData) {
                        $sale->salesItems()->create($itemData);
                    }
                }
                DB::commit();
                return redirect()->back()->with('success', 'Penjualan & Detail Produk berhasil diimpor!');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->withErrors(['csv_file' => 'Error Import: ' . $e->getMessage()]);
            }
        }
        return redirect()->back()->withErrors(['csv_file' => 'Gagal upload file.']);
    }

    public function importCustomers(Request $request): RedirectResponse
    {
        $request->validate(['csv_file_customer' => 'required|mimes:csv,txt|max:2048']);
        if ($request->hasFile('csv_file_customer')) {
            $file = $request->file('csv_file_customer');
            $path = $file->getRealPath();
            DB::beginTransaction();
            try {
                $fileHandle = fopen($path, 'r');
                $isHeader = true; 
                while (($row = fgetcsv($fileHandle, 1000, ",")) !== FALSE) {
                    if ($isHeader) { $isHeader = false; continue; }
                    $id = $row[0] ?? null;
                    $name = $row[1] ?? 'Tanpa Nama';
                    $phone = $row[2] ?? null;
                    $points = $row[3] ?? '0';
                    if (!$id) continue;
                    Customer::updateOrCreate(
                        ['id' => $id], 
                        ['name' => $name, 'phone' => $phone, 'loyalty_points' => (int) $points, 'address' => null]
                    );
                }
                fclose($fileHandle);
                DB::commit();
                return redirect()->back()->with('success', 'Data Pelanggan berhasil diimpor!');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->withErrors(['csv_file_customer' => 'Error: ' . $e->getMessage()]);
            }
        }
        return redirect()->back()->withErrors(['csv_file_customer' => 'Gagal upload file pelanggan.']);
    }

    
}