{{-- resources/views/pembayaran/index.blade.php --}}

@extends('layouts.main')

@section('title', 'Daftar Pembayaran')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pembayaran.css') }}">
@endpush

@section('content')
    <div class="page-header">
        <h1>Daftar Pembayaran</h1>
        <p>Halaman untuk melihat riwayat pembayaran.</p>
    </div>

    <div class="content-area">
        <div class="table-controls">
            {{-- Form untuk search --}}
            <form action="{{ route('pembayaran') }}" method="GET" class="search-box">
                <label for="search">Search:</label>
                <input type="text" id="search" name="search" placeholder="Cari ID, nama..." value="{{ request('search') }}">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID Pembayaran</th>
                        <th>ID Pemesanan</th>
                        <th>Nama Konsumen</th>
                        <th>Metode</th>
                        <th>Total Bayar</th>
                        <th>Status</th>
                        <th>Tanggal Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Gunakan @forelse untuk looping data --}}
                    @forelse ($pembayarans as $pembayaran)
                        <tr>
                            <td>{{ $pembayaran->id_pembayaran }}</td>
                            <td>{{ $pembayaran->pemesanan_id }}</td>
                            {{-- Ambil nama dari relasi konsumen --}}
                            <td>{{ $pembayaran->konsumen->nama ?? 'N/A' }}</td>
                            {{-- Tampilkan metode pembayaran jika ada --}}
                            <td>{{ $pembayaran->metode_pembayaran ?? '-' }}</td>
                            {{-- Format angka menjadi format Rupiah --}}
                            <td>Rp {{ number_format($pembayaran->total_pembayaran, 0, ',', '.') }}</td>
                            {{-- Beri warna pada status --}}
                            <td>
                                <span class="status status-{{ strtolower($pembayaran->status) }}">
                                    {{ ucfirst($pembayaran->status) }}
                                </span>
                            </td>
                            {{-- Tampilkan tanggal jika sudah dibayar --}}
                            <td>{{ $pembayaran->tanggal_pembayaran ? $pembayaran->tanggal_pembayaran->format('d M Y, H:i') : '-' }}</td>
                        </tr>
                    @empty
                        {{-- Tampilan jika tidak ada data sama sekali --}}
                        <tr>
                            <td colspan="7" style="text-align: center;">Tidak ada data Pembayaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Bagian untuk link Paginasi --}}
        <div class="pagination-section">
            {{ $pembayarans->links() }}
        </div>
    </div>
@endsection

@push('styles')
{{-- Tambahkan sedikit CSS untuk warna status --}}
<style>
    .status {
        padding: 5px 10px;
        border-radius: 15px;
        color: white;
        font-size: 0.85em;
        text-transform: capitalize;
    }
    .status-berhasil { background-color: #28a745; }
    .status-pending { background-color: #007bff; color: #ffffff; }
    .status-gagal { background-color: #dc3545; }
</style>
@endpush