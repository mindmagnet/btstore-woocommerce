<?php

/*
 * script for proper backref redirect, 
 * btpay portal strips possible GET params
 * 
 */
session_start();
//var_dump($_SERVER);

$ref = $_SESSION['backref'];

if (strpos($ref, 'post=') !== false)
    $ref .='&message=4';

$ref .='&' . $_SERVER['QUERY_STRING'] . '&method=' . $_SESSION['method'];


unset($_SESSION['action']);
header('Location: ' . $ref);
