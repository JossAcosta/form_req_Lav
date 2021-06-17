<?php

defined('BASEPATH') or exit('No direct script access allowed');
$dimensions = $pdf->getPageDimensions();

$pdf_logo_url = pdf_logo_url();
$pdf->writeHTMLCell(($dimensions['wk'] - ($dimensions['rm'] + $dimensions['lm'])), '', '', '', $pdf_logo_url, 0, 1, false, true, 'L', true);

$pdf->ln(4);
// Get Y position for the separation
$y = $pdf->getY();

$order_sample_info = '<div style="color:#424242;">';
$order_sample_info .= format_organization_info();
$order_sample_info .= '</div>';

$pdf->writeHTMLCell(($swap == '0' ? (($dimensions['wk'] / 2) - $dimensions['rm']) : ''), '', '', ($swap == '0' ? $y : ''), $order_sample_info, 0, 0, false, true, ($swap == '1' ? 'R' : 'J'), true);

$rowcount = max([$pdf->getNumLines($order_sample_info, 80)]);

// Order_sample to
$client_details = '<b>' . _l('order_sample_to') . '</b>';
$client_details .= '<div style="color:#424242;">';
$client_details .= format_order_sample_info($order_sample, 'pdf');
$client_details .= '</div>';

$pdf->writeHTMLCell(($dimensions['wk'] / 2) - $dimensions['lm'], $rowcount * 7, '', ($swap == '1' ? $y : ''), $client_details, 0, 1, false, true, ($swap == '1' ? 'J' : 'R'), true);

$pdf->ln(6);

$order_sample_date = _l('order_sample_date') . ': ' . _d($order_sample->date);
$open_till     = '';

if (!empty($order_sample->open_till)) {
    $open_till = _l('order_sample_open_till') . ': ' . _d($order_sample->open_till) . '<br />';
}

$qty_heading = _l('estimate_table_quantity_heading', '', false);

if ($order_sample->show_quantity_as == 2) {
    $qty_heading = _l($this->type . '_table_hours_heading', '', false);
} elseif ($order_sample->show_quantity_as == 3) {
    $qty_heading = _l('estimate_table_quantity_heading', '', false) . '/' . _l('estimate_table_hours_heading', '', false);
}

// The items table
$items = get_items_table_data($order_sample, 'order_sample', 'pdf')
    ->set_headings('estimate');

$items_html = $items->table();

$items_html .= '<br /><br />';
$items_html .= '';
$items_html .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';

$items_html .= '
<tr>
    <td align="right" width="85%"><strong>' . _l('estimate_subtotal') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($order_sample->subtotal, $order_sample->currency_name) . '</td>
</tr>';

if (is_sale_discount_applied($order_sample)) {
    $items_html .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('estimate_discount');
    if (is_sale_discount($order_sample, 'percent')) {
        $items_html .= '(' . app_format_number($order_sample->discount_percent, true) . '%)';
    }
    $items_html .= '</strong>';
    $items_html .= '</td>';
    $items_html .= '<td align="right" width="15%">-' . app_format_money($order_sample->discount_total, $order_sample->currency_name) . '</td>
    </tr>';
}

foreach ($items->taxes() as $tax) {
    $items_html .= '<tr>
    <td align="right" width="85%"><strong>' . $tax['taxname'] . ' (' . app_format_number($tax['taxrate']) . '%)' . '</strong></td>
    <td align="right" width="15%">' . app_format_money($tax['total_tax'], $order_sample->currency_name) . '</td>
</tr>';
}

if ((int)$order_sample->adjustment != 0) {
    $items_html .= '<tr>
    <td align="right" width="85%"><strong>' . _l('estimate_adjustment') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($order_sample->adjustment, $order_sample->currency_name) . '</td>
</tr>';
}
$items_html .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('estimate_total') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($order_sample->total, $order_sample->currency_name) . '</td>
</tr>';
$items_html .= '</table>';
$pdf_custom_fields = get_custom_fields('order_sample', array('show_on_pdf' => 0));
$items_html .=   '<br></br><br></br> ';
foreach ($pdf_custom_fields as $field) {

    $value = get_custom_field_value($order_sample->id, $field['id'], 'order_sample');

    if ($value == 'N/A') {
        continue;
    }
 

    if ($field['id'] == 42){

    function show_columns($array_values, $chunk){
                foreach($chunk as $option){
                    $columns =         ' <td width="5%"><strong> <div  style="border:black solid 1px ; border-radius: 12px; background-color:' .compare_array($option, $array_values). ';"></div></strong></td>';
                    $columns .=   ' <td width="20%"><div >'. $option.' </div></td>';
                    return  $columns;
                }
    }
        function compare_array($option, $array_values){
            foreach($array_values as $array_value){
                if($option  == $array_value){
                    return '#343c44';
                }
            }
        }
    function show_rows($value, $chunks, $n){
        foreach($chunks as $chunk ){
            $tr = '<tr>';
            $columns ='<tr>'.show_columns($value, $chunk).'</tr>';
            $tre= '</tr>';
            $rows = $columns;
            return $rows;
        }
    }
    function get_options($id_customfield){
        $result =get_custom_field_options($id_customfield);
        $result_array =  explode(',', $result->options);
        return $result_array;
    }

    $options =get_options(42);
    $chunks =array_chunk( $options, 4);
    $array_values = explode(',', $value);
    $items_html .=   '<br></br> ';
    $items_html .=  '<table cellpadding="4" >';
    $items_html .= '<tr> <th style="font-size:' . ($font_size + 10) . 'px";  width="100%">' . '<b>' .'III. '.strtoupper ($field['name']) . '</b>:' . '</th>  </tr>';

    foreach($chunks as $chunk ){
        $items_html .= '<tr>';
            foreach($chunk as $option){
                $items_html .= ' <td width="5%"><strong> <div style="border:gray solid 1px ;  background-color:' .compare_array($option, $array_values). ';"></div></strong></td>';
                $items_html .= ' <td width="20%"> <div >'. $option.' </div></td>';
            }
        $items_html .= '</tr>';
    }
            $items_html .= '</tr>';
        // }

    $items_html .= '</table>';
    $items_html .=   '<br></br><br></br> ';

    }elseif ($field['id'] == 43) {

    $options= ['Especificaciones en Español', 'MS DS', ' CC Español',  ' Precio venta', 'Especificaciones en Inglés', 'HTS', ' CC Inglés', 'Otro'];
        // $options =get_options(43);
            // $result =get_custom_field_options($id_customfield);
            // $options_ =  explode(',', $result->options);

    $chunks =array_chunk($options, 4);
    $array_values = explode(',', $value);
        // $items_html .=   '<br></br> ';
        $items_html .=  '<table cellpadding="4" >';
        $items_html .= '<tr> <th style="font-size:' . ($font_size + 10) . 'px";  width="100%">' . '<b>' .'IV. '.strtoupper ($field['name']) . '</b>:' . '</th>  </tr>';


        foreach($chunks as $chunk ){
            $items_html .= '<tr>';
                foreach($chunk as $option){
                    $items_html .= ' <td width="5%"><div style="border:gray solid 1px ; background-color:' .compare_array($option, $array_values). ';"></div></td>';
                    $items_html .= ' <td width="20%"><div >'. $option.' </div></td>';
                }
            $items_html .= '</tr>';
        }

        $items_html .= '</table>';
        $items_html .=   '<br></br><br></br> ';
    }elseif ($field['id'] == 44){

        $options= ['Sabor', 'Aceite Esencial/Terpenos', 'Concentrado',  ' Oleorresina / Tecnorresina', 'Emulsión', 'Extrato', 'Base', 'Fragancia', 'Color', 'Otro:__________', 'Natural', 'Sintético', 'Natural Idéntico', 'Otro_____'];
        // $options =get_options(44);
        $array_values = explode(',', $value);
        $chunks =array_chunk( $options, 2);
        $items_html .=   '<br></br> ';
        $items_html .=  '<table cellpadding="4" >';
        $title_batch ="VII. ELEMENTOS ENTRADA DE MUESTRA";
        $items_html .= '<tr> <th style="font-size:' . ($font_size + 10) . 'px";  width="100%">' . '<b>' .strtoupper ($title_batch) . '</b>:' . '</th>  </tr>';
        $items_html .=   '<br></br> ';
        $items_html .= '<tr> <th style="font-size:' . ($font_size + 4) . 'px";  width="100%">' . strtoupper ($field['name']) . '</th>  </tr>';
        $items_html .=   '<br></br> ';

        foreach($chunks as $chunk ){
            $items_html .= '<tr>';
                foreach($chunk as $option){
                    $items_html .= ' <td width="5%"><div style="border:gray solid 1px ; background-color:' .compare_array($option, $array_values). ';"></div></td>';
                    $items_html .= ' <td width="20%"><div >'.' '. $option.' </div></td>';
                }
            $items_html .= '</tr>';
        }

        $items_html .= '</table>';
        $items_html .=   '<br></br><br></br> ';

    }elseif ($field['id'] == 45){

        // $options= ['Líquido', 'Con color', 'Polvo', 'Sin color', 'Turbio', 'Otro:__________'];
        $options =get_options(45);
        $array_values = explode(',', $value);
        $chunks =array_chunk( $options, 2);

        $items_html .=   '<br></br> ';
        $items_html .=  '<table cellpadding="2" >';
        $items_html .= '<tr> <th style="font-size:' . ($font_size + 4) . 'px";  width="100%">' . strtoupper ($field['name']) . '</th>  </tr>';
        $items_html .=   '<br></br> ';
        foreach($chunks as $chunk ){
            $items_html .= '<tr>';
                foreach($chunk as $option){
                    $items_html .= ' <td width="5%"><div style="border:gray solid 1px ; background-color:' .compare_array($option, $array_values). ';"></div></td>';
                    $items_html .= ' <td width="20%"><div >'.' '. $option.' </div></td>';
                }
            $items_html .= '</tr>';
        }

        $items_html .= '</table>';
        $items_html .=   '<br></br><br></br> ';

    }elseif ($field['id'] == 46){

        $options= ['Agua', 'Alcohol etílico', 'Aceite', 'Otro:__________'];
        $options =get_options(46);
        $array_values = explode(',', $value);
        $chunks =array_chunk( $options, 2);

        $items_html .=   '<br></br> ';
        $items_html .=  '<table cellpadding="4" >';
        $items_html .= '<tr> <th style="font-size:' . ($font_size + 4) . 'px";  width="100%">' . strtoupper ($field['name']) . '</th>  </tr>';
        $items_html .=   '<br></br> ';

        foreach($chunks as $chunk ){
            $items_html .= '<tr>';
                foreach($chunk as $option){
                    $items_html .= ' <td width="5%"><div style="border:gray solid 1px ; background-color:' .compare_array($option, $array_values). ';"></div></td>';
                    $items_html .= ' <td width="20%"><div >'.' '. $option.' </div></td>';
                }
            $items_html .= '</tr>';
        }

        $items_html .= '</table>';
        $items_html .=   '<br></br><br></br> ';

    }elseif ($field['id'] == 47){

        $items_html .= '<br></br> ';
        $items_html .= '<table  cellpadding="6" >';
        $items_html .= '<tr> <th style="font-size:' . ($font_size + 4) . 'px";  width="100%">' . strtoupper ($field['name']) . '</th>  </tr>';
        $items_html .= '<tr><td style="border:0.1px solid #424242;" width="100%"></td></tr>';
        $items_html .= '<tr><td style="border:0.1px solid #424242;" width="100%"></td></tr>';
        $items_html .= '<tr><td style="border:0.1px solid #424242;" width="100%"></td></tr>';
        $items_html .= '<tr><td style="border:0.1px solid #424242;" width="100%"></td></tr>';
        $items_html .= '<tr><td style="border:0.1px solid #424242;" width="100%"></td></tr>';
        $items_html .= '<tr><td style="border:0.1px solid #424242;" width="100%"></td></tr>';
        $items_html .= '</table>';
        $items_html .=   '<br></br><br></br> ';

    }elseif ($field['id'] == 48){

        $options= ['Sí', 'No'];
        // $options =get_options(48);
        $array_values = explode(',', $value);
        $chunks =array_chunk( $options, 1);

        $items_html .=   '<br></br> ';
        $items_html .=  '<table cellpadding="2" >';
        $items_html .= '<tr> <th style="font-size:' . ($font_size + 4) . 'px";  width="100%">' . strtoupper ($field['name']) . '</th>  </tr>';

        foreach($chunks as $chunk ){
            $items_html .= '<tr>';
                foreach($chunk as $option){
                    $items_html .= ' <td width="5%"><div style="border:gray solid 1px ; background-color:' .compare_array($option, $array_values). ';"></div></td>';
                    $items_html .= ' <td width="20%"><div >'.' '. $option.' </div></td>';
                }
            $items_html .= '</tr>';
        }

        $items_html .= '</table>';
        $items_html .=   '<br></br><br></br> ';

    }elseif ($field['id'] == 49){
        
        $options= ['Sí', 'No'];
        // $options =get_options(49);
        $array_values = explode(',', $value);
        $chunks =array_chunk( $options, 1);

        $items_html .=   '<br></br> ';
        $items_html .=  '<table cellpadding="2" >';
        $items_html .= '<tr> <th style="font-size:' . ($font_size + 4) . 'px";  width="100%">' . strtoupper ($field['name']) . '</th>  </tr>';

        foreach($chunks as $chunk ){
            $items_html .= '<tr>';
                foreach($chunk as $option){
                    $items_html .= ' <td width="5%"><div style="border:gray solid 1px ; background-color:' .compare_array($option, $array_values). ';"></div></td>';
                    $items_html .= ' <td width="20%"><div >'.' '. $option.' </div></td>';
                }
            $items_html .= '</tr>';
        }

        $items_html .= '</table>';
        $items_html .=   '<br></br><br></br> ';


    }elseif ($field['id'] == 50){

        $options= ['Sí', 'No'];
        $array_values = explode(',', $value);
        $chunks =array_chunk( $options, 1);

        $items_html .=   '<br></br> ';
        $items_html .=  '<table cellpadding="2" >';
        $items_html .= '<tr> <th style="font-size:' . ($font_size + 4) . 'px";  width="100%">' . strtoupper ($field['name']) . '</th>  </tr>';

        foreach($chunks as $chunk ){
            $items_html .= '<tr>';
                foreach($chunk as $option){
                    $items_html .= ' <td width="5%"><div style="border:gray solid 1px ; background-color:' .compare_array($option, $array_values). ';"></div></td>';
                    $items_html .= ' <td width="20%"><div >'.' '. $option.' </div></td>';
                }
            $items_html .= '</tr>';
        }


        $items_html .= '</table>';
        $items_html .=   '<br></br><br></br> ';

    }elseif ($field['id'] == 51){
        
        $options= ['Sí', 'No'];
        // $options =get_options(51);
        $array_values = explode(',', $value);
        $chunks =array_chunk( $array_values, 1);

        $items_html .=   '<br></br> ';
        $items_html .=  '<table cellpadding="2" >';
        $items_html .= '<tr> <th style="font-size:' . ($font_size + 4) . 'px";  width="100%">' . strtoupper ($field['name']) . '</th>  </tr>';

        foreach($chunks as $chunk ){
            $items_html .= '<tr>';
                foreach($chunk as $option){
                    $items_html .= ' <td width="5%"><div style="border:gray solid 1px ; background-color:' .compare_array($option, $array_values). ';"></div></td>';
                    $items_html .= ' <td width="20%"><div >'.' '. $option.' </div></td>';
                }
            $items_html .= '</tr>';
        }

        $items_html .= '</table>';
        $items_html .=   '<br></br><br></br> ';

    }elseif ($field['id'] == 52){

        // $options= ['Si', 'No'];
        $options =get_options(52);
        $array_values = explode(',', $value);
        $chunks =array_chunk( $options, 1);
        $items_html .=   '<br></br> ';
        $items_html .=  '<table cellpadding="2" >';
        $items_html .= '<tr> <th style="font-size:' . ($font_size + 4) . 'px";  width="100%">' . strtoupper ($field['name']) . '</th>  </tr>';

        foreach($chunks as $chunk ){
            $items_html .= '<tr>';
                foreach($chunk as $option){
                    $items_html .= ' <td width="5%"><div style="border:gray solid 1px ; background-color:' .compare_array($option, $array_values). ';"></div></td>';
                    $items_html .= ' <td width="20%"><div >'.' '. $option.' </div></td>';
                }
            $items_html .= '</tr>';
        }

        $items_html .= '</table>';
        $items_html .=   '<br></br><br></br> ';

    }elseif ($field['id'] == 53){
        // $options= ['Sí', 'No'];
        $options =get_options(53);
        $array_values = explode(',', $value);
        $chunks =array_chunk( $array_values, 1);

        $items_html .=   '<br></br> ';
        $items_html .=  '<table cellpadding="2" >';
        $items_html .= '<tr> <th style="font-size:' . ($font_size + 4) . 'px";  width="100%">' . strtoupper ($field['name']) . '</th>  </tr>';

        foreach($chunks as $chunk ){
            $items_html .= '<tr>';
                foreach($chunk as $option){
                    $items_html .= ' <td width="5%"><div style="border:gray solid 1px ; background-color:' .compare_array($option, $array_values). ';"></div></td>';
                    $items_html .= ' <td width="20%"><div >'.' '. $option.' </div></td>';
                }
            $items_html .= '</tr>';
        }

        $items_html .= '</table>';
        $items_html .=   '<br></br><br></br> ';

    }elseif ($field['id'] == 54){
        // $options= ['Sí', 'No'];
        $options =get_options(54);
        $array_values = explode(',', $value);
        $chunks =array_chunk( $array_values, 1);

        $items_html .=   '<br></br> ';
        $items_html .=  '<table cellpadding="2" >';
        $items_html .= '<tr> <th style="font-size:' . ($font_size + 4) . 'px";  width="100%">' . strtoupper ($field['name']) . '</th>  </tr>';
        foreach($chunks as $chunk ){
            $items_html .= '<tr>';
                foreach($chunk as $option){
                    $items_html .= ' <td width="5%"><div style="border:gray solid 1px ; background-color:' .compare_array($option, $array_values). ';"></div></td>';
                    $items_html .= ' <td width="20%"><div >'.' '. $option.' </div></td>';
                }
            $items_html .= '</tr>';
        }


        $items_html .= '</table>';
        $items_html .=   '<br></br><br></br> ';

    }elseif ($field['id'] == 55){

        $array_values = explode(',', $value);
        $items_html .= '<br></br> ';
        $items_html .= '<table  cellpadding="6" >';
        $items_html .= '<tr> <th style="font-size:' . ($font_size + 4) . 'px";  width="100%">' . strtoupper ($field['name']) . '</th>  </tr>';
        foreach($array_values as $array_value){
            $items_html .= '<tr><td style="border:0.1px solid #424242;" width="100%">'.$array_value.'</td></tr>';
        }
        $items_html .= '<tr><td style="border:0.1px solid #424242;" width="100%"></td></tr>';
        $items_html .= '<tr><td style="border:0.1px solid #424242;" width="100%"></td></tr>';
        $items_html .= '<tr><td style="border:0.1px solid #424242;" width="100%"></td></tr>';
        $items_html .= '</table>';
        $items_html .=   '<br></br><br></br> ';

    }else {
        // $items_html .= $field['id'] . '<b>' . $field['name'] . '</b>: ' . $value . '<br />';
    }
}


$items_html .= '<div>Firma de Gerente de Comercial: <br><img src="' . get_upload_path_by_type('proposal') . $order_sample->id . '/' . $order_sample->signature_boss . '"> <br>Firma de Cobranza: <br><img src="' . get_upload_path_by_type('proposal') . $order_sample->id . '/' . $order_sample->signature_finance . '"></div>';

if (get_option('total_to_words_enabled') == 1) {
    $items_html .= '<br /><br /><br />';
    $items_html .= '<strong style="text-align:center;">' . _l('num_word') . ': ' . $CI->numberword->convert($order_sample->total, $order_sample->currency_name) . '</strong>';
}

$order_sample->content = str_replace('{order_sample_items}', $items_html, $order_sample->content);


$html = <<<EOF
<p style="font-size:20px;"># $number
<br /><span style="font-size:15px;">$order_sample->subject</span>
</p>
$order_sample_date
<br />
$open_till
<div style="width:675px !important;">
$order_sample->content
</div>
EOF;

$pdf->writeHTML($html, true, false, true, false, '');
