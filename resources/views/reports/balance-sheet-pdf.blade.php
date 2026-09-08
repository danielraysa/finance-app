@php($money = fn($value) => 'IDR ' . number_format($value ?? 0, 0, ',', '.'))
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 11px
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

        .section {
            margin-top: 18px;
            font-weight: bold;
            color: #1f4d3a
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px
        }

        .table th,
        .table td {
            border-bottom: 1px solid #d1d5db;
            padding: 7px;
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
    <div class="title">NERACA</div>
    <div class="period">Posisi per {{ $filters['endDate'] }}</div>
    <div class="section">Aset</div>
    <table class="table">
        <tr>
            <th>Keterangan</th>
            <th class="amount">Jumlah</th>
        </tr>
        @foreach ($assets as $item)
            <tr>
                <td>{{ $item['name'] }}</td>
                <td class="amount">{{ $money($item['value']) }}</td>
            </tr>
        @endforeach
        <tr class="total">
            <td>Total Aset</td>
            <td class="amount">{{ $money($summary['totalAssets']) }}</td>
        </tr>
    </table>
    <div class="section">Kewajiban dan Modal</div>
    <table class="table">
        <tr>
            <th>Keterangan</th>
            <th class="amount">Jumlah</th>
        </tr>
        <tr>
            <td>Total Kewajiban</td>
            <td class="amount">{{ $money($summary['totalLiabilities']) }}</td>
        </tr>
        <tr>
            <td>Total Modal</td>
            <td class="amount">{{ $money($summary['totalEquity']) }}</td>
        </tr>
        <tr class="total">
            <td>Total Kewajiban dan Modal</td>
            <td class="amount">{{ $money($summary['totalLiabilities'] + $summary['totalEquity']) }}</td>
        </tr>
    </table>
    <div class="footer">Dibuat oleh {{ $company['company_name'] }} pada {{ now()->format('d/m/Y H:i') }}</div>
</body>

</html>
