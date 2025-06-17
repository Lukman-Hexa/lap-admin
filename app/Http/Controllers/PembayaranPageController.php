<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran; // Import model Pembayaran

class PembayaranPageController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembayaran::with('pemesanan', 'konsumen')->latest();

        // Logika untuk fitur Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('id_pembayaran', 'like', '%' . $search . '%')
                  ->orWhere('pemesanan_id', 'like', '%' . $search . '%')
                  ->orWhereHas('konsumen', function($q) use ($search) {
                      $q->where('nama', 'like', '%' . $search . '%');
                  });
        }

        // Ambil data dengan paginasi (10 data per halaman)
        $pembayarans = $query->paginate(10);

        // Kirim data $pembayarans ke view
        return view('pembayaran', compact('pembayarans'));
    }
}