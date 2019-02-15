<?php

include("btpaygate.php");
session_start();

$payment = new BTPayment();
$payment->setAmount($_GET['amount']);
$payment->setCurrency($_GET['currency']);
$payment->setRambursare(intval($_SESSION['action']['rambursare']));
$payment->setDesc($_SESSION['action']['desc']);
$payment->setOrder($_GET['order']);

$capture = new BTPayGate();
$capture->setConfigData('test_mode', $_SESSION['action']['testmode']);
$capture->setConfigData('encryption_key', $_SESSION['action']['encryptionkey']);
$capture->setConfigData('merchant_name', $_SESSION['action']['merchantname']);
$capture->setConfigData('merchant_url', $_SESSION['action']['merchanturl']);
$capture->setConfigData('terminal', $_SESSION['action']['terminal']);
$capture->setConfigData('merchant_email', $_SESSION['action']['merchantemail']);

$_SESSION['backref'] = $_SERVER['HTTP_REFERER'];

$backref = 'http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['SCRIPT_NAME'], 0, -11) . 'redirect.php';
$rrn = $_GET['rrn'];
$intref = $_GET['intref'];
$c_params = $capture->getActionParams($payment, 'capture', $backref, $rrn, $intref, false);

die($capture->renderForm($c_params, true, true, true));
?>