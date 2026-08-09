<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Kunjungan - {{ $school->name ?? 'E-Library' }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
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
            font-size: 20px;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 16px;
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
            margin-bottom: 30px;
        }
        table th, table td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
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
        <h1>LAPORAN REKAPITULASI KUNJUNGAN PERPUSTAKAAN</h1>
        <h2>{{ $school->name ?? 'PERPUSTAKAAN E-LIBRARY' }}</h2>
        <p>Generated on {{ \Carbon\Carbon::now()->isoFormat('DD MMMM YYYY, HH:mm') }} WIB</p>
    </div>

    <!-- Filter Meta -->
    <div class="meta-info">
        <strong>Periode Laporan:</strong> {{ \Carbon\Carbon::parse($startDate)->isoFormat('DD MMMM YYYY') }} s/d {{ \Carbon\Carbon::parse($endDate)->isoFormat('DD MMMM YYYY') }}<br>
        @if($kelas) <strong>Filter Kelas:</strong> Kelas {{ $kelas }} &nbsp;&nbsp;|&nbsp;&nbsp; @endif
        @if($jurusan) <strong>Filter Jurusan:</strong> {{ $jurusan }} &nbsp;&nbsp;|&nbsp;&nbsp; @endif
        <strong>Total Pengunjung:</strong> {{ number_format($visits->count()) }} Orang
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 18%;">Waktu Scan</th>
                <th style="width: 17%;">NIS / NIP</th>
                <th style="width: 30%;">Nama Pengunjung</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 15%;">Kelas / Jurusan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($visits as $index => $visit)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($visit->scanned_at)->isoFormat('DD/MM/YYYY HH:mm') }}</td>
                    <td>{{ $visit->member ? $visit->member->member_code : '-' }}</td>
                    <td><strong>{{ $visit->member ? $visit->member->name : ($visit->visitor_name ?? 'Tamu') }}</strong></td>
                    <td>{{ $visit->member ? ($visit->member->source_type === 'siswa' ? 'Siswa' : 'Guru / Staf') : 'Tamu' }}</td>
                    <td>{{ $visit->member ? ($visit->member->class_or_dept ?? '-') : ($visit->class_or_dept ?? '-') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #94a3b8;">Tidak ada data kunjungan yang ditemukan.</td>
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
