<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'pembayaran';

    // Mendefinisikan primary key
    protected $primaryKey = 'id_pembayaran';

    // Memberi tahu Laravel bahwa primary key bukan auto-incrementing integer
    public $incrementing = false;

    // Memberi tahu Laravel tipe data dari primary key
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pemesanan_id',
        'konsumen_id',
        'metode_pembayaran',
        'total_pembayaran',
        'status',
        'tanggal_pembayaran',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_pembayaran' => 'decimal:2',
        'tanggal_pembayaran' => 'datetime',
    ];

    /**
     * Boot the model.
     * Secara otomatis akan mengisi 'id_pembayaran' saat membuat record baru.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Pembayaran $pembayaran) {
            // Jika id_pembayaran belum diisi, buat otomatis
            if (empty($pembayaran->id_pembayaran)) {
                $latestPembayaran = self::orderBy('id_pembayaran', 'desc')->first();
                $nextNumber = 1;

                if ($latestPembayaran) {
                    // Ekstrak angka dari ID terakhir (misal: PMB001 -> 1)
                    $lastNumber = (int) substr($latestPembayaran->id_pembayaran, 3);
                    $nextNumber = $lastNumber + 1;
                }
                
                // == PERUBAHAN DI SINI ==
                // Format diubah menjadi: PMB001, PMB002, dst.
                $pembayaran->id_pembayaran = 'PMB' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            }
        });
    }
    
    /**
     * Mendefinisikan relasi "belongsTo" ke model Pemesanan.
     */
    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id', 'id_pemesanan');
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model Konsumen.
     */
    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class, 'konsumen_id', 'no_identitas');
    }
}