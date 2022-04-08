<?php
include(__DIR__ . './../vendor/autoload.php');

$vouchersClient = new EasyGCO\EasyGCOVouchers\API();

$testApiPath = 'transactions/list';

$testInputData = [
    'code' => 'YOUR_VOUCHER_CODE',     //  Voucher Code
    'limit' => '50',                    // Results Max: 100
];

$apiResponse = $vouchersClient->doRequest($testApiPath, $testInputData);

if(!$vouchersClient->isSuccess($apiResponse)) 
    exit($vouchersClient->getMessage($apiResponse));

var_dump($vouchersClient->getData($apiResponse));
exit();