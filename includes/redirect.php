<?php
session_start();
$params = $_SESSION['params_c'] ;
unset($_SESSION['params_c']);


$html = '';


    $html .= '<!DOCTYPE html>
        <html>
        <head>
            <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
            <title>BT Pay - Tranzactie in curs</title>
        </head>
        <body>';



$html .= '<div style="margin: 0 auto;  min-height: 450px; text-align: center;">';

$html .= '<p>Tranzactie in curs ...</p>';
//Debug only
//$html .= "<pre>$params = ".print_r($_REQUEST,1)."</pre>";

$html .= '<div id="payment_form_block" style="display:block !important; border:0 !important; margin:0 !important; padding:0 !important; font-size:0 !important; line-height:0 !important; width:0 !important; height:0 !important; overflow:hidden !important;">';

$html .= '<form id="btpay" action="' . $params['gateway_url'] . '" method="post">';
if(!isset($params['CURRENCY'])){
    $params['CURRENCY'] = 'EUR';
}
if(!in_array($params['CURRENCY'],array('USD','EUR','RON'))){
    $params['CURRENCY'] = 'EUR';
}
foreach ($params as $_name => $_value) {
    $html .= '<input type="hidden" name="' . $_name . '" value="' . stripslashes($_value) . '" />';
}

$html .= '<input type="submit" value="Executa plata" />';
$html .= '</form>';

$html .= '</div>';


    $html .= '<script>document.getElementById("btpay").submit();</script>';


$html .= '</div>';

    $html .= '</body></html>';
echo $html;