<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Konsumen;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PembayaranController extends Controller
{
    public function processPayment(Request $request, $id_pemesanan)
    {
        $validator = Validator::make($request->all(), [
            'metode_pembayaran' => 'required|string|in:saldo,transfer_bank,lainnya,Qris,FSMoney',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $pembayaran = Pembayaran::where('pemesanan_id', $id_pemesanan)->first();
        $konsumen = $request->user();

        if (!$pembayaran) {
            return response()->json(['status' => 'error', 'message' => 'Pembayaran untuk pemesanan ini tidak ditemukan.'], 404);
        }
        if ($pembayaran->konsumen_id !== $konsumen->no_identitas) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized. Pembayaran ini bukan milik Anda.'], 403);
        }
        if ($pembayaran->status !== 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Pembayaran ini sudah diproses.'], 409);
        }

        DB::beginTransaction();
        try {
            $pembayaran->metode_pembayaran = $request->metode_pembayaran;

            if (in_array($request->metode_pembayaran, ['saldo', 'FSMoney'])) {
                $konsumen_locked = Konsumen::where('no_identitas', $konsumen->no_identitas)->lockForUpdate()->first();
                if ($konsumen_locked->saldo < $pembayaran->total_pembayaran) {
                    $pembayaran->status = 'gagal';
                } else {
                    $konsumen_locked->saldo -= $pembayaran->total_pembayaran;
                    $konsumen_locked->save();
                    $pembayaran->status = 'berhasil';
                    $pembayaran->tanggal_pembayaran = now();
                }
            } else {
                $pembayaran->status = 'berhasil';
                $pembayaran->tanggal_pembayaran = now();
            }

            $pembayaran->save();
            DB::commit();

            if ($pembayaran->status == 'berhasil') {
                return response()->json(['status' => 'success', 'message' => 'Pembayaran berhasil!', 'data' => $pembayaran], 200);
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Pembayaran gagal, saldo tidak mencukupi.', 'data' => $pembayaran], 402);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan server: ' . $e->getMessage()], 500);
        }
    }
}