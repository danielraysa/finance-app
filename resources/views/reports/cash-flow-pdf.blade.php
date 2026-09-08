@php($money = fn($value) => 'IDR ' . number_format($value ?? 0, 0, ',', '.'))
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 10px
        }

        .header {
            border-bottom: 2px solid #1f4d3a;
            padding-bottom: 12px;
            margin-bottom: 18px
        }

        .company {
            font-size: 16px;
            font-weight: bold;
            color: #1f4d3a
        }

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 14px 0 4px
        }

        .period {
            text-align: center;
            color: #6b7280
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px
        }

        .table th,
        .table td {
            border-bottom: 1px solid #d1d5db;
            padding: 6px;
            text-align: left
        }

        .table th {
            background: #e8f0eb
        }

        .amount {
            text-align: right
        }

        .total {
            font-weight: bold;
            border-top: 2px solid #374151
        }

        .footer {
            margin-top: 24px;
            color: #6b7280;
            font-size: 9px;
            text-align: center
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company">{{ $company['company_name'] }}</div>
        @if ($company['company_address'])
            <div>{{ $company['company_address'] }}</div>
        @endif
        @if ($company['company_contact'])
            <div>{{ $company['company_contact'] }}</div>
        @endif
    </div>
    <div class="title">LAPORAN ARUS KAS</div>
    <div class="period">Periode {{ $filters['startDate'] }} s.d. {{ $filters['endDate'] }}</div>
    <table class="table">
        <tr>
            <th>Tanggal</th>
            <th>Uraian</th>
            <th>Kategori</th>
            <th>Rekening</th>
            <th class="amount">Masuk</th>
            <th class="amount">Keluar</th>
        </tr>
        @foreach (array_merge($cashInTransactions, $cashOutTransactions) as $item)
            <tr>
                <td>{{ $item['date'] }}</td>
                <td>{{ $item['description'] }}</td>
                <td>{{ $item['category'] }}</td>
                <td>{{ $item['account'] }}</td>
                <td class="amount">
                    {{ isset($item['amount']) && in_array($item, $cashInTransactions, true) ? $money($item['amount']) : '-' }}
                </td>
                <td class="amount">
                    {{ isset($item['amount']) && in_array($item, $cashOutTransactions, true) ? $money($item['amount']) : '-' }}
                </td>
            </tr>
        @endforeach
        <tr class="total">
            <td colspan="4">Total</td>
            <td class="amount">{{ $money($summary['cashIn']) }}</td>
            <td class="amount">{{ $money($summary['cashOut']) }}</td>
        </tr>
        <tr class="total">
            <td colspan="5">Arus Kas Bersih</td>
            <td class="amount">{{ $money($summary['netCashFlow']) }}</td>
        </tr>
    </table>
    <div class="footer">Dibuat oleh {{ $company['company_name'] }} pada {{ now()->format('d/m/Y H:i') }}</div>
</body>

</html>
