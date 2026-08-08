<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Peminjaman - {{ $school->name ?? 'E-Library' }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 15px;
            margin: 0 0 5px 0;
            color: #475569;
        }
        .header p {
            margin: 0;
            font-size: 11px;
            color: #64748b;
        }
        .meta-info {
            margin-bottom: 15px;
            font-size: 11px;
            line-height: 1.6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }
        table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <!-- Kop Header -->
    <div class="header">
        <h1>LAPORAN REKAPITULASI PEMINJAMAN BUKU</h1>
        <h2>{{ $school->name ?? 'PERPUSTAKAAN E-LIBRARY' }}</h2>
        <p>Generated on {{ \Carbon\Carbon::now()->isoFormat('DD MMMM YYYY, HH:mm') }} WIB</p>
    </div>

    <!-- Filter Meta -->
    <div class="meta-info">
        <strong>Periode Laporan:</strong> {{ \Carbon\Carbon::parse($startDate)->isoFormat('DD MMMM YYYY') }} s/d {{ \Carbon\Carbon::parse($endDate)->isoFormat('DD MMMM YYYY') }}<br>
        @if($status) <strong>Filter Status:</strong> {{ ucfirst($status) }} &nbsp;&nbsp;|&nbsp;&nbsp; @endif
        @if($kelas) <strong>Filter Kelas:</strong> Kelas {{ $kelas }} &nbsp;&nbsp;|&nbsp;&nbsp; @endif
        @if($jurusan) <strong>Filter Jurusan:</strong> {{ $jurusan }} &nbsp;&nbsp;|&nbsp;&nbsp; @endif
        <strong>Total Transaksi:</strong> {{ number_format($loans->count()) }} Data Transaksi
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 4%;">No</th>
                <th style="width: 10%;">Tgl Pinjam</th>
                <th style="width: 10%;">Tgl Tempo</th>
                <th style="width: 10%;">Tgl Kembali</th>
                <th style="width: 20%;">Peminjam</th>
                <th style="width: 10%;">Kelas/Jurusan</th>
                <th style="width: 22%;">Judul Buku</th>
                <th class="text-center" style="width: 4%;">Qty</th>
                <th class="text-center" style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($loans as $index => $loan)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($loan->borrow_date)->isoFormat('DD/MM/YYYY') }}</td>
                    <td>{{ \Carbon\Carbon::parse($loan->due_date)->isoFormat('DD/MM/YYYY') }}</td>
                    <td>{{ $loan->return_date ? \Carbon\Carbon::parse($loan->return_date)->isoFormat('DD/MM/YYYY') : '-' }}</td>
                    <td><strong>{{ $loan->member->name ?? '-' }}</strong> <br><small style="color:#64748b;">{{ $loan->member->member_code ?? '' }}</small></td>
                    <td>{{ $loan->member->class_or_dept ?? '-' }}</td>
                    <td>{{ $loan->book->title ?? '-' }}</td>
                    <td class="text-center">{{ $loan->qty ?? 1 }}</td>
                    <td class="text-center"><strong>{{ ucfirst($loan->status) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px; color: #94a3b8;">Tidak ada data peminjaman yang ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <table style="border: none; margin-top: 30px;">
        <tr style="border: none; background: transparent;">
            <td style="border: none; width: 60%;"></td>
            <td style="border: none; width: 40%; text-align: center;">
                <p>{{ $school->name ?? 'Sekolah' }}, {{ \Carbon\Carbon::now()->isoFormat('DD MMMM YYYY') }}</p>
                <p><strong>Kepala / Pengelola Perpustakaan</strong></p>
                <div style="height: 60px;"></div>
                <p>__________________________</p>
            </td>
        </tr>
    </table>

    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
