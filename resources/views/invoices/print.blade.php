<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Invoice {{ $invoice->invoice_number }}
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            background: #f3f4f6;
        }

        body {
            padding: 30px 0;
        }

        .invoice-wrapper {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 20mm;
            background: #ffffff;
        }

        .no-print {
            margin-bottom: 20px;
            text-align: center;
        }

        .print-button {
            display: inline-block;
            padding: 10px 18px;
            border: 0;
            border-radius: 6px;
            background: #111827;
            color: #ffffff;
            font-size: 14px;
            cursor: pointer;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 30px;
            margin-bottom: 30px;
        }

        .company-name {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .invoice-title {
            margin: 0;
            font-size: 30px;
            font-weight: 700;
            text-align: right;
        }

        .invoice-number {
            margin-top: 8px;
            font-size: 14px;
            color: #6b7280;
            text-align: right;
        }

        .meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .meta-box {
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }

        .meta-label {
            margin-bottom: 5px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
        }

        .meta-value {
            font-size: 14px;
            font-weight: 600;
        }

        .unit-block {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .unit-heading {
            margin-bottom: 10px;
            padding: 10px 12px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-bottom: 0;
            border-radius: 8px 8px 0 0;
        }

        .unit-title {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
        }

        .unit-imei {
            margin-top: 4px;
            font-size: 12px;
            color: #6b7280;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            font-size: 13px;
        }

        th {
            background: #f9fafb;
            font-weight: 700;
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .total-section {
            margin-top: 20px;
            margin-left: auto;
            width: 320px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }

        .total-row.grand-total {
            border-bottom: 0;
            font-size: 18px;
            font-weight: 700;
        }

        .notes {
            margin-top: 35px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }

        .notes-title {
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 700;
        }

        .notes-content {
            white-space: pre-line;
            font-size: 13px;
            color: #4b5563;
        }

        .footer {
            margin-top: 45px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
        }

        @page {
            size: A4;
            margin: 15mm;
        }

        @media print {
            html,
            body {
                background: #ffffff;
            }

            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .invoice-wrapper {
                width: 100%;
                min-height: auto;
                margin: 0;
                padding: 0;
            }

            .unit-block {
                page-break-inside: avoid;
            }

            .unit-heading {
                break-after: avoid;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <button
            type="button"
            class="print-button"
            onclick="window.print()"
        >
            Cetak Invoice
        </button>
    </div>

    <main class="invoice-wrapper">

        <section class="header">
            <div>
                <h1 class="company-name">
                    Unit Repair Management
                </h1>
            </div>

            <div>
                <h2 class="invoice-title">
                    INVOICE
                </h2>

                <div class="invoice-number">
                    {{ $invoice->invoice_number }}
                </div>
            </div>
        </section>

        <section class="meta">

            <div class="meta-box">
                <div class="meta-label">
                    Customer
                </div>

                <div class="meta-value">
                    {{ $invoice->customer?->name ?? '-' }}
                </div>
            </div>

            <div class="meta-box">
                <div class="meta-label">
                    Trip
                </div>

                <div class="meta-value">
                    {{ $invoice->trip?->trip_number ?? '-' }}
                </div>
            </div>

            <div class="meta-box">
                <div class="meta-label">
                    Tanggal Invoice
                </div>

                <div class="meta-value">
                    {{ $invoice->invoice_date
                        ? \Illuminate\Support\Carbon::parse($invoice->invoice_date)->format('d/m/Y')
                        : '-'
                    }}
                </div>
            </div>

            <div class="meta-box">
                <div class="meta-label">
                    Status
                </div>

                <div class="meta-value">
                    {{ $invoice->status ?? '-' }}
                </div>
            </div>

        </section>

        @php
            $groupedItems = $invoice->items->groupBy('unit_id');
        @endphp

        @forelse ($groupedItems as $unitId => $items)

            @php
                $unit = $items->first()?->unit;
            @endphp

            <section class="unit-block">

                <div class="unit-heading">

                    <h3 class="unit-title">
                        Unit
                    </h3>

                    <div class="unit-imei">
                        IMEI:
                        {{ $unit?->imei ?? '-' }}
                    </div>

                </div>

                <table>

                    <thead>
                        <tr>
                            <th style="width: 55%;">
                                Repair
                            </th>

                            <th
                                class="text-center"
                                style="width: 15%;"
                            >
                                Qty
                            </th>

                            <th
                                class="text-right"
                                style="width: 30%;"
                            >
                                Harga
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($items as $item)

                            <tr>

                                <td>
                                    {{ $item->description ?? '-' }}
                                </td>

                                <td class="text-center">
                                    {{ $item->quantity }}
                                </td>

                                <td class="text-right">
                                    Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </section>

        @empty

            <table>

                <tbody>
                    <tr>
                        <td class="text-center">
                            Tidak ada detail invoice.
                        </td>
                    </tr>
                </tbody>

            </table>

        @endforelse

        <section class="total-section">

            <div class="total-row">
                <span>
                    Subtotal
                </span>

                <span>
                    Rp {{ number_format((float) $invoice->total, 0, ',', '.') }}
                </span>
            </div>

            <div class="total-row grand-total">

                <span>
                    TOTAL
                </span>

                <span>
                    Rp {{ number_format((float) $invoice->total, 0, ',', '.') }}
                </span>

            </div>

        </section>

        @if ($invoice->notes)

            <section class="notes">

                <div class="notes-title">
                    Catatan
                </div>

                <div class="notes-content">
                    {{ $invoice->notes }}
                </div>

            </section>

        @endif

        <footer class="footer">
            Terima kasih.
        </footer>

    </main>

</body>
</html>