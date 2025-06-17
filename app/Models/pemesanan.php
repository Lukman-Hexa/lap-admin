<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB; // Tambahkan ini
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Log;

class Pemesanan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan'; // Nama tabel yang benar

    protected $primaryKey = 'id_pemesanan'; // Primary key yang benar
    public $incrementing = false; // Karena id_pemesanan adalah string
    protected $keyType = 'string'; // Tipe data primary key adalah string

    protected $fillable = [
        'konsumen_id',
        'lapangan_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'harga',
        'catatan',
    ];

    // Boot method untuk event model
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pemesanan) {
            // Ambil ID pemesanan terakhir
            $latestPemesanan = static::orderBy('id_pemesanan', 'desc')->first();
            $nextNumber = 1;

            if ($latestPemesanan) {
                // Ekstrak angka dari ID terakhir (misal: PN001 -> 1)
                $lastIdNumber = (int) substr($latestPemesanan->id_pemesanan, 2); // Ambil angka setelah 'PN'
                $nextNumber = $lastIdNumber + 1;
            }

            // Format angka menjadi 3 digit dengan padding nol
            $pemesanan->id_pemesanan = 'PN' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        });

        // == LOGIKA BARU: INI JANTUNG DARI FITUR OTOMATIS ANDA ==
        // Event ini akan berjalan SETELAH sebuah pemesanan berhasil dibuat.
        static::created(function ($pemesanan) {
            try {
                Pembayaran::create([
                    'pemesanan_id'      => $pemesanan->id_pemesanan,
                    'konsumen_id'       => $pemesanan->konsumen_id,
                    'total_pembayaran'  => $pemesanan->harga,
                    'status'            => 'pending',
                ]);
            } catch (\Exception $e) {
                // Jika terjadi error, catat ke log file
                Log::error('Gagal membuat pembayaran otomatis untuk pemesanan ID ' . $pemesanan->id_pemesanan . ': ' . $e->getMessage());
            }
        });
    }

    // Relasi ke model Konsumen
    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class, 'konsumen_id', 'no_identitas');
    }

    // Relasi ke model Lapangan
    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id', 'id_lapangan');
    }

    // 3. TAMBAHKAN RELASI BARU KE PEMBAYARAN
    // Ini akan mempermudah kita jika suatu saat ingin mengambil data pembayaran dari pemesanan
    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'pemesanan_id', 'id_pemesanan');
    }
}