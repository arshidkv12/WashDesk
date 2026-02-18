<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use App\Models\Order;
use Mpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UtilityController extends Controller
{

    public function print($order_id)
    {   
        $user = Auth::user();
        $order = Invoice::with('items')->with('customer')->findOrFail( $order_id );
        $currency_symbol = $user->currency_symbol;
        $company_name = $user->company_name;
        $data = [
            'invoiceNumber' => $order->invoice_no,
            'date' => $order->created_at->format('M d,Y'),
            'time' => $order->created_at->format('h:iA'),
            'items' => $order->items,
            'order' => $order,
            'currency_symbol' => $currency_symbol,
            'company_name' => $company_name,
            'company_logo' => $user->company_logo,
            'company_name' => $company_name,
            'site_description' => nl2br($user->company_address),
            'balance_due' => $order->total_amount - $order->paid_amount,
            'balance_due' => $order->total_amount - $order->paid_amount,
        ];

        $html = view('invoices.3-invoice', $data)->render();

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
        
        $mpdf = new Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                public_path(''),
            ]),
            'fontdata' => $fontData + [  
                'terminus' => [
                    'R' => 'Terminus.ttf',
                ]
            ],
            'default_font' => 'terminus',
            'mode' => 'utf-8',
            'shrink_tables_to_fit' => 0,
            'format' => [75, 200],  
            'orientation' => 'P',
            'margin_left' => 3,
            'margin_right' => 3,
            'margin_top' => 3,
            'margin_bottom' => 3,
        ]);

        $mpdf->WriteHTML($html);
        return $mpdf->Output('invoice-'.$order_id.'.pdf', 'I');
    }
}