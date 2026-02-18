<!-- resources/views/invoices/laundry.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Thermal print optimized for laundry service */
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.3;
            margin: 0;
            padding: 2mm;
            max-width: 80mm;
            color: #000;
        }

        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-xs { font-size: 9px; }
        .text-sm { font-size: 10px; }
        
        .mt-1 { margin-top: 1mm; }
        .mt-2 { margin-top: 2mm; }
        .mb-1 { margin-bottom: 1mm; }
        .pt-1 { padding-top: 1mm; }
        .pb-1 { padding-bottom: 1mm; }

        .border-top {
            border-top: 1px solid #000;
        }

        /* Header */
        .laundry-header {
            text-align: center;
            margin-bottom: 3mm;
        }
        
        .laundry-header h1 {
            font-size: 15px;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
        }
        
        .laundry-header .tagline {
            font-size: 10px;
            margin: 1mm 0;
            color: #333;
        }

        /* Customer info */
        .customer-info {
            width: 100%;
            font-size: 11px;
            margin: 2mm 0;
        }
        
        .customer-info td {
            padding: 0.5mm 0;
        }
        
        .info-label {
            font-weight: bold;
            width: 35%;
        }

        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2mm 0;
        }
        
        .items-table th {
            font-size: 11px;
            font-weight: bold;
            padding: 1mm 0;
        }
        
        .items-table td {
            padding: 0.5mm 0;
            vertical-align: top;
        }
        
        .service-name {
            font-size: 12px;
            font-weight: bold;
        }
        
        .service-details {
            font-size: 10px;
            color: #000;
            padding-left: 2mm;
        }

        /* Dash line separator - simple text dashes */
        .dash-line {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            letter-spacing: 0;
            text-align: center;
            margin: 0;
            padding: 0;
            line-height: 1;
        }

        /* Special instructions */
        .special-instructions {
            font-size: 10px;
            font-style: italic;
            padding: 1mm;
            margin: 1mm 0;
            border-left: 2px solid #000;
        }

        /* Summary */
        .summary-table {
            width: 100%;
            font-size: 12px;
            margin: 2mm 0;
        }
        
        .summary-table td {
            padding: 0.5mm 0;
        }
        
        .grand-total {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 1mm 0;
            margin: 1mm 0;
        }

        /* Status badges */
        .status-badge {
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #000;
            padding: 0.5mm 2mm;
            display: inline-block;
            font-size: 12px;
        }
        
        .status-paid {
            background: #000;
            color: #fff;
        }
        
        .status-pending {
            border-color: #000;
            color: #000;
        }

        /* Service info */
        .service-info {
            width: 100%;
            margin: 2mm 0;
            font-size: 11px;
            border-collapse: collapse;
        }
        
        .service-info td {
            padding: 0.5mm;
        }
        
        .service-info .label {
            font-weight: bold;
            width: 40%;
        }

        /* Footer */
        .laundry-footer {
            text-align: center;
            margin-top: 1mm;
            font-size: 10px;
            padding-top: 2mm;
        }
        
        .thanks-message {
            font-size: 12px;
            margin: 1mm 0;
        }

        /* Barcode */
        .barcode {
            font-family: 'Courier New', monospace;
            font-size: 20px;
            letter-spacing: 2px;
            margin: 2mm 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="laundry-header">
        @if($company_logo)
            <img style="padding:5mm; padding-right:10mm; padding-left:10mm" src="{{public_path('uploads/logos/gray-' . $company_logo)}}" />
        @endif
        <h1>{{ $company_name }}</h1>
        <div class="tagline">{!! $site_description !!}</div>
    </div>

    <!-- Bill Reference -->
    <table class="service-info">
        <tr>
            <td class="label">Bill No:</td>
            <td class="text-right font-bold">#{{ str_pad($invoiceNumber, 6, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td class="label">Date & Time</td>
            <td class="text-right">{{ $time . ' - ' . $date }}</td>
        </tr>
    </table>

    <!-- Customer Information -->
    {{-- <table class="customer-info">
        <tr>
            <td class="info-label">Customer:</td>
            <td class="text-right"><strong>{{ $order->customer->name ?? 'Walk-in Customer' }}</strong></td>
        </tr>
        @if(isset($order->customer->phone))
        <tr>
            <td class="info-label">Phone:</td>
            <td class="text-right">{{ $order->customer->phone }}</td>
        </tr>
        @endif
        @if(isset($customer_address))
        <tr>
            <td class="info-label">Address:</td>
            <td>{{ $customer_address }}</td>
        </tr>
        @endif
        @if(isset($membership_id))
        <tr>
            <td class="info-label">Member ID:</td>
            <td>{{ $membership_id }} <span class="text-xs">({{ $membership_type ?? 'Regular' }})</span></td>
        </tr>
        @endif
    </table> --}}

    <!-- Service Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-left">Item</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $i = 0; 
                $total_price = 0;
                $total_tax = [];
                $grand_total = 0;
                $total_items = 0;
            @endphp

            <tr>
                <td colspan="4" class="dash-line">-------------------------------------------</td>
            </tr>
            
            @foreach($items as $item)
                @php 
                    $i++; 
                    $tax = $item['tax_rate'] ?? 0;
                    $item_total = $item->unit_price * $item->quantity;
                    $tax_amount = ($item_total * $tax) / 100;
                    $item_total_with_tax = $item_total + $tax_amount;
                    $total_price += $item_total;
                    $total_tax[$tax] = ($total_tax[$tax] ?? 0) + $tax_amount;
                    $grand_total += $item_total_with_tax;
                    $total_items += $item->quantity;
                @endphp
                
                <!-- Item name -->
                <tr>
                    <td class="service-name" colspan="4">{{ $i }}. {{ $item->name }}</td>
                </tr>
                
                <!-- Item details -->
                @if(isset($item->weight) || isset($item->pieces) || isset($item->service_type))
                <tr>
                    <td class="service-details" colspan="4">
                        @if(isset($item->weight)) {{ $item->weight }}kg @endif
                        @if(isset($item->pieces)) {{ $item->pieces }} pcs @endif
                        @if(isset($item->service_type)) | {{ $item->service_type }} @endif
                    </td>
                </tr>
                @endif
                
                <!-- Pricing row -->
                <tr>
                    <td></td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item_total, 2) }}</td>
                </tr>
                
                <!-- Dash line between items (except last) -->
                {{-- @if(!$loop->last)
                <tr>
                    <td colspan="4" class="dash-line">-------------------------------------------</td>
                </tr>
                @endif --}}
            @endforeach
            
        </tbody>
    </table>

    <!-- Pickup/Delivery Information -->
    @if(isset($pickup_date) || isset($delivery_date) || isset($service_type))
    <table class="service-info">
        @if(isset($pickup_date))
        <tr>
            <td class="label">Pickup:</td>
            <td>{{ $pickup_date }} @if(isset($pickup_time)) {{ $pickup_time }} @endif</td>
        </tr>
        @endif
        @if(isset($delivery_date))
        <tr>
            <td class="label">Delivery:</td>
            <td>{{ $delivery_date }} @if(isset($delivery_time)) {{ $delivery_time }} @endif</td>
        </tr>
        @endif
        @if(isset($service_type))
        <tr>
            <td class="label">Service:</td>
            <td>{{ $service_type }} ({{ $turnaround_time ?? 'Standard' }})</td>
        </tr>
        @endif
    </table>
    @endif

    <!-- Special Instructions -->
    @if(isset($special_instructions))
    <div class="special-instructions">
        <span class="font-bold">Note:</span> {{ $special_instructions }}
    </div>
    @endif

    <div colspan="4" class="dash-line">-------------------------------------------</div>

    <!-- Summary -->
    <table class="summary-table">
        <tr>
            <td>Total Items</td>
            <td class="text-right">{{$total_items}}</td>
        </tr>
        <tr>
            <td>Subtotal:</td>
            <td class="text-right">{{ number_format($total_price, 2) }}</td>
        </tr>
        
        @if(isset($delivery_charge) && $delivery_charge > 0)
        <tr>
            <td>Delivery:</td>
            <td class="text-right">{{ number_format($delivery_charge, 2) }}</td>
        </tr>
        @endif
        
        @if(isset($pickup_charge) && $pickup_charge > 0)
        <tr>
            <td>Pickup:</td>
            <td class="text-right">{{ number_format($pickup_charge, 2) }}</td>
        </tr>
        @endif
        
        @foreach ($total_tax as $rate => $amount)
            @if($amount <= 0) @continue @endif
            <tr>
                <td>GST @ {{ (int)$rate }}%:</td>
                <td class="text-right">{{ number_format($amount, 2) }}</td>
            </tr>
        @endforeach
        
        @if(isset($order->discount_amount) && $order->discount_amount > 0)
        <tr>
            <td>Discount:</td>
            <td class="text-right">-{{ number_format($order->discount_amount, 2) }}</td>
        </tr>
        @endif
        
        <tr class="grand-total">
            <td class="font-bold">TOTAL</td>
            <td class="text-right font-bold">{{ number_format($order->total_amount, 2) }}</td>
        </tr>
    </table>
    

    <!-- Payment Status -->
    <div class="text-center">
        @if(isset($balance_due) && $balance_due <= 0)
            <div class="status-badge">PAID</div>            
        @else
            <div class="status-badge">PENDING</div>  
        @endif
    </div>

    <!-- Payment Details -->
    @if(isset($payment_method) || isset($advance_paid) || isset($balance_due))
    <table class="summary-table">
        @if(isset($payment_method))
        <tr>
            <td>Payment:</td>
            <td class="text-right">{{ $payment_method }}</td>
        </tr>
        @endif
        @if(isset($advance_paid) && $advance_paid > 0)
        <tr>
            <td>Advance:</td>
            <td class="text-right">{{ number_format($advance_paid, 2) }}</td>
        </tr>
        @endif
        @if(isset($balance_due) && $balance_due > 0)
        <tr>
            <td>Balance:</td>
            <td class="text-right">{{ number_format($balance_due, 2) }}</td>
        </tr>
        @endif
    </table>
    @endif

    <!-- Token Number -->
    @if(isset($token_number))
    <div class="barcode">
        ||| {{ $token_number }} |||
    </div>
    <div class="text-center text-xs">Token: {{ $token_number }}</div>
    @endif

    <!-- Footer -->
    <div class="laundry-footer">
        <div colspan="4" class="dash-line">-------------------------------------------</div>
        <div class="thanks-message">Thank you for choosing !</div>
        <div class="footer-note"></div>
        @if(isset($contact_number))
        <div class="footer-note">Call: {{ $contact_number }}</div>
        @endif
    </div>
</body>
</html>