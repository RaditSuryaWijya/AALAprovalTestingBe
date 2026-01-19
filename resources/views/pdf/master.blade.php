<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Laporan' }}</title>
    <style>
        body { 
            font-family: sans-serif; 
            font-size: 12px; 
            color: #333;
        }
        h1 { 
            text-align: center; 
            margin-bottom: 30px; 
            text-transform: uppercase;
        }
        
        /* Container untuk satu record/data */
        .data-card {
            border: 1px solid #999;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #fff;
            
            /* PENTING: Mencegah kartu terpotong di tengah halaman saat print */
            page-break-inside: avoid; 
        }

        /* Tabel di dalam kartu untuk merapikan Label : Value */
        .card-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .card-table td {
            padding: 4px;
            vertical-align: top; /* Agar teks panjang rapi di atas */
        }

        .label {
            font-weight: bold;
            width: 30%; /* Lebar kolom label */
            color: #555;
            border-bottom: 1px solid #eee;
        }

        .separator {
            width: 2%;
            border-bottom: 1px solid #eee;
        }

        .value {
            width: 68%;
            border-bottom: 1px solid #eee;
        }

        /* Baris terakhir tidak perlu garis bawah */
        tr:last-child .label, 
        tr:last-child .separator, 
        tr:last-child .value {
            border-bottom: none;
        }

        .meta-info {
            text-align: right;
            font-size: 10px;
            color: #888;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="meta-info">
        Dicetak pada: {{ date('d-m-Y H:i') }}
    </div>

    <h1>{{ $title ?? 'Laporan' }}</h1>

    @forelse ($rows as $index => $row)
        <div class="data-card">
            {{-- Opsional: Menampilkan Nomor Urut --}}
            <div style="background: #eee; padding: 5px; font-weight: bold; margin-bottom: 5px; border-bottom: 1px solid #ddd;">
                Data #{{ $index + 1 }}
            </div>

            <table class="card-table">
                @foreach ($columns as $col)
                    <tr>
                        <td class="label">{{ $col['label'] }}</td>
                        <td class="separator">:</td>
                        <td class="value">
                            {{-- Mengambil data, jika kosong strip --}}
                            {{ data_get($row, $col['field']) ?? '-' }}
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @empty
        <div style="text-align: center; padding: 20px; border: 1px dashed #ccc;">
            Tidak ada data untuk ditampilkan.
        </div>
    @endforelse

</body>
</html>