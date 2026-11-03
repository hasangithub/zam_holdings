
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Freight Record</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 22px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .header h2 {
            font-size: 18px;
            margin: 0 0 5px;
        }

        .header p {
            font-size: 9px;
            margin: 0;
            color: #666;
        }

        .section {
            margin-top: 16px;
            page-break-inside: avoid;
        }

        .section-title {
            background: #e9ecef;
            border-left: 4px solid #343a40;
            padding: 7px 9px;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            width: 25%;
            border: 1px solid #ddd;
            padding: 9px;
            vertical-align: top;
        }

        .label {
            display: block;
            font-size: 8px;
            color: #777;
            margin-bottom: 5px;
        }

        .value {
            font-size: 10px;
            font-weight: bold;
            color: #222;
        }

        .footer {
            margin-top: 25px;
            padding-top: 7px;
            border-top: 1px solid #ccc;
            text-align: right;
            font-size: 8px;
            color: #777;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>FREIGHT RECORD</h2>
        <p>Freight Record Details</p>
        <p>Record No: {{ $record->id }}</p>
    </div>

    {{-- SHIPMENT INFORMATION --}}

    <div class="section">

        <div class="section-title">
            SHIPMENT INFORMATION
        </div>

        <table class="info-table">
            <tr>
                <td>
                    <span class="label">Date</span>
                    <span class="value">{{ $record->date ?: '-' }}</span>
                </td>

                <td>
                    <span class="label">Airway No</span>
                    <span class="value">{{ $record->airway_no ?: '-' }}</span>
                </td>

                <td>
                    <span class="label">Consignor</span>
                    <span class="value">{{ $record->consignor ?: '-' }}</span>
                </td>

                <td>
                    <span class="label">Consignee</span>
                    <span class="value">{{ $record->consignee ?: '-' }}</span>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="label">Net Weight (Kg)</span>
                    <span class="value">{{ number_format($record->net_weight, 2) }}</span>
                </td>

                <td>
                    <span class="label">Gross Weight (Kg)</span>
                    <span class="value">{{ number_format($record->gross_weight, 2) }}</span>
                </td>

                <td>
                    <span class="label">Boxes</span>
                    <span class="value">{{ $record->boxes }}</span>
                </td>

                <td>
                    <span class="label">Exchange Rate</span>
                    <span class="value">{{ number_format($record->exchange_rate, 4) }}</span>
                </td>
            </tr>
        </table>

    </div>

    {{-- INVOICE DETAILS --}}

    <div class="section">

        <div class="section-title">
            INVOICE DETAILS
        </div>

        <table class="info-table">
            <tr>
                <td>
                    <span class="label">Custom Invoice (USD)</span>
                    <span class="value">{{ number_format($record->custom_usd, 2) }}</span>
                </td>

                <td>
                    <span class="label">Custom Invoice (LKR)</span>
                    <span class="value">{{ number_format($record->custom_lkr, 2) }}</span>
                </td>

                <td>
                    <span class="label">Freight Invoice (USD)</span>
                    <span class="value">{{ number_format($record->freight_usd, 2) }}</span>
                </td>

                <td>
                    <span class="label">Freight Invoice (LKR)</span>
                    <span class="value">{{ number_format($record->freight_lkr, 2) }}</span>
                </td>
            </tr>
        </table>

    </div>

    {{-- OTHER INFORMATION --}}

    <div class="section">

        <div class="section-title">
            OTHER INFORMATION
        </div>

        <table class="info-table">
            <tr>
                <td>
                    <span class="label">CUSDEC No</span>
                    <span class="value">{{ $record->cusdec_no ?: '-' }}</span>
                </td>

                <td>
                    <span class="label">Booking</span>
                    <span class="value">{{ $record->booking ?: '-' }}</span>
                </td>

                <td>
                    <span class="label">Bank</span>
                    <span class="value">{{ $record->bank ?: '-' }}</span>
                </td>

                <td>
                    <span class="label">Record No</span>
                    <span class="value">{{ $record->id }}</span>
                </td>
            </tr>
        </table>

    </div>

    <div class="footer">
        Generated on {{ now()->format('Y-m-d H:i') }}
    </div>

</body>
</html>
