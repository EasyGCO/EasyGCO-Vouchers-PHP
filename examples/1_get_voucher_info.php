<?php
include(__DIR__ . './../vendor/autoload.php');

$vouchersClient = new EasyGCO\EasyGCOVouchers\API();

$testApiPath = 'vouchers/get';

$testInputData = [
    'code' => 'YOUR_VOUCHER_CODE',      // Voucher Code
];

$apiResponse = $vouchersClient->doRequest($testApiPath, $testInputData);

if(!$vouchersClient->isSuccess($apiResponse)) 
    exit($vouchersClient->getMessage($apiResponse));

var_dump($vouchersClient->getData($apiResponse));
exit();