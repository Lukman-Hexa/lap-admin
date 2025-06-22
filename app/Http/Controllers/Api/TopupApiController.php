<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Topup;
use App\Models\Konsumen;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Throwable;

class TopupApiController extends Controller
{
    /**
     * Endpoint untuk nerima request top up dari Flutter.
     * Konsumen_id akan diambil dari token otentikasi.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Mendapatkan konsumen yang sedang terotentikasi dari token Sanctum
        $konsumen = $request->user(); 

        // Jika tidak ada konsumen yang terotentikasi (seharusnya tidak terjadi jika middleware sudah benar)
        if (!$konsumen) {
            return response()->json([
                'message' => 'Unauthorized. User not found from token.',
            ], 401); 
        }

        $validator = Validator::make($request->all(), [
            'nominal' => 'required|numeric|min:10000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $topup = Topup::create([
                'konsumen_id' => $konsumen->no_identitas, // Menggunakan no_identitas dari user yang terotentikasi
                'nominal'     => $request->nominal,
                'status'      => 'Pending',
            ]);

            return response()->json([
                'message' => 'Request top up berhasil, menunggu konfirmasi admin.',
                'data'    => $topup
            ], 201);

        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Gagal memproses request top up.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint untuk konfirmasi pembayaran oleh Admin dari web.
     * (Versi final yang sudah diperbaiki)
     */
    public function confirm(Request $request, $id)
    {
        // 2. Menggunakan with('konsumen') (Eager Loading) untuk efisiensi query.
        //    Model Topup sudah diperbaiki dengan `protected $primaryKey = 'id';`
        $topup = Topup::with('konsumen')->find($id);

        if (!$topup) {
            return response()->json(['message' => 'Data top up tidak ditemukan'], 404);
        }

        if ($topup->status == 'Berhasil') {
            return response()->json(['message' => 'Top up ini sudah pernah dikonfirmasi.'], 400);
        }

        try {
            // 3. Menggunakan DB::transaction untuk keamanan dan konsistensi data.
            DB::transaction(function () use ($topup) {
                
                $konsumen = $topup->konsumen;
                
                // 4. Pengecekan eksplisit jika konsumen tidak ditemukan.
                if (!$konsumen) {
                    throw new \Exception('Konsumen yang terkait dengan top-up ini tidak ditemukan.');
                }

                // 5. Logika penambahan saldo yang aman dari error 'null'.
                $saldo_sekarang = $konsumen->saldo ?? 0;
                $konsumen->saldo = $saldo_sekarang + $topup->nominal;
                $konsumen->save();

                // Status diubah di dalam transaksi.
                $topup->status = 'Berhasil';
                $topup->save();

            });

        } catch (Throwable $e) {
            // 6. Blok penanganan error yang akan menangkap semua masalah di dalam 'try'.
            report($e); // Melaporkan error ke file log.
            return response()->json([
                'message' => 'Gagal memproses konfirmasi top up.',
                'error' => $e->getMessage()
            ], 500);
        }
        
        // 7. Refresh model untuk mendapatkan data terbaru (termasuk relasi konsumen)
        $topup->refresh();

        // Mengembalikan response sukses dengan data topup yang sudah terupdate.
        return response()->json([
            'message' => 'Top up berhasil dikonfirmasi dan saldo telah ditambahkan!',
            'data' => $topup // Data topup sudah berisi relasi konsumen yang terupdate.
        ], 200);
    }
}