<?php

include("btpaygate.php");

session_start();
//var_dump($_SESSION);
$payment = new BTPayment();
$payment->setAmount($_GET['amount']);
$payment->setCurrency($_GET['currency']);
$payment->setRambursare(intval($_SESSION['action']['rambursare']));
$payment->setDesc($_SESSION['action']['desc']);
$payment->setOrder($_GET['order']);

$void = new BTPayGate();
$void->setConfigData('test_mode', $_SESSION['action']['testmode']);
$void->setConfigData('encryption_key', $_SESSION['action']['encryptionkey']);
$void->setConfigData('merchant_name', $_SESSION['action']['merchantname']);
$void->setConfigData('merchant_url', $_SESSION['action']['merchanturl']);
$void->setConfigData('terminal', $_SESSION['action']['terminal']);
$void->setConfigData('merchant_email', $_SESSION['action']['merchantemail']);


$_SESSION['backref'] = $_SERVER['HTTP_REFERER'];

$backref = 'http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['SCRIPT_NAME'], 0, -8) . 'redirect.php';
$rrn = $_GET['rrn'];
$intref = $_GET['intref'];

$partial = $_GET['amount'] < $_SESSION['action']['total'];
$c_params = $void->getActionParams($payment, 'void', $backref, $rrn, $intref, $partial);

die($void->renderForm($c_params, true, true, true));
?>
