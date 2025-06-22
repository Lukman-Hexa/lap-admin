<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pemesanan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth; // Tambahkan ini

class PemesananController extends Controller
{
    // Fungsi untuk menampilkan halaman Pemesanan (untuk web)
    public function index(Request $request)
    {
        // Mendapatkan nilai pencarian (jika ada)
        $search = $request->input('search');

        // Ambil data pemesanan, dengan pencarian jika ada
        $pemesanan = Pemesanan::when($search, function ($query, $search) {
            // Perbaiki kolom pencarian agar sesuai dengan tabel
            return $query->where('id_pemesanan', 'like', "%$search%") // Ganti 'id' menjadi 'id_pemesanan'
                         ->orWhere('konsumen_id', 'like', "%$search%") // Ini sudah merujuk ke no_identitas konsumens
                         ->orWhere('lapangan_id', 'like', "%$search%");
        })
        ->paginate(10); // Pagination untuk membatasi jumlah data per halaman

        // BARIS PENTING INI UNTUK MENGIRIM VARIABEL $pemesanan KE VIEW
        return view('pemesanan', compact('pemesanan'));
    }

    // Fungsi untuk menyimpan data pemesanan dari API
    public function store(Request $request)
    {
        // Pastikan pengguna sudah terautentikasi
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. User not logged in.',
            ], 401); // 401 Unauthorized
        }

        // Ambil no_identitas dari pengguna yang sedang login
        $konsumenId = Auth::user()->no_identitas; // Sesuaikan dengan nama kolom primary key di tabel Konsumen

        $validator = Validator::make($request->all(), [
            // konsumen_id tidak lagi diperlukan dari request body karena akan diambil dari token
            // 'konsumen_id' => 'required|exists:konsumens,no_identitas',
            'lapangan_id' => 'required|exists:lapangans,id_lapangan',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i:s',
            'jam_selesai' => 'required|date_format:H:i:s|after:jam_mulai',
            'harga' => 'required|numeric|min:0',
            'catatan' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $pemesanan = Pemesanan::create([
                'konsumen_id' => $konsumenId, // Gunakan konsumen_id dari user yang terautentikasi
                'lapangan_id' => $request->lapangan_id,
                'tanggal' => $request->tanggal,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'harga' => $request->harga,
                'catatan' => $request->catatan,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pemesanan berhasil ditambahkan',
                'data' => $pemesanan
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan pemesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function indexApi(Request $request)
    {
        $search = $request->input('search');

        $pemesanan = Pemesanan::when($search, function ($query, $search) {
            return $query->where('id_pemesanan', 'like', "%$search%") // Ganti 'id' menjadi 'id_pemesanan'
                         ->orWhere('konsumen_id', 'like', "%$search%")
                         ->orWhere('lapangan_id', 'like', "%$search%");
        })
        ->paginate(10); // Pagination untuk data API

        // Mengembalikan data sebagai JSON
        return response()->json($pemesanan);
    }
}