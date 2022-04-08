<?php
namespace EasyGCO\EasyGCOVouchers;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;

class API
{
    const API_URL = 'https://easygco.com/api/vouchers/v1/';

    private $endPoint = '';
    private $guzzleConfig = '';
    private $guzzleClient;
    
    private $lastQuery = '';
    
    public function __construct(array $_guzzleConfig = []) {

        // SET Default End Point ( You may use API->setEndPoint Method after construction to change the default End-Point )
        $this->setEndPoint(self::API_URL);
        
        // SET Guzzle Connection Config - Only If Providers or Default If Not Provided
        $this->guzzleConfig = !empty($_guzzleConfig)? $_guzzleConfig : [
            'verify' => false,
            'headers' => ['user-agent' => 'EasyGCO-Vouchers-API-Library v1'],
            'connect_timeout' => 20,
        ];

        // Construct Guzzle Client Interface
        try {
            $this->guzzleClient = new \GuzzleHttp\Client($this->guzzleConfig);
        } catch(\Exception $e) {
            throw new Exception('Construction failure: '. $e->getMessage());
        }

        return true;
        
    }

    public function setEndPoint(string $endPoint) {
        if(!filter_var($endPoint, FILTER_VALIDATE_URL)) return false;
        $this->endPoint = $endPoint;
        return true;
    }

    public function setConnectionConfig(string $paramKey, $paramValue) {
        $this->guzzleConfig[$paramKey] = $paramValue;
        try {
            $this->guzzleClient = new \GuzzleHttp\Client($this->guzzleConfig);
        } catch(\Exception $e) {
            throw new Exception($e->getMessage());
        }
        return true;
    }

    public function getEndPoint() {
        return $this->endPoint;
    }

    public function getLastQuery() {
        return $this->lastQuery? $this->lastQuery : null;
    }

    public function getConnectionConfig(string $paramKey = null) {
        return ( $paramKey === null ) ? $this->guzzleConfig 
            :  ( array_key_exists($paramKey, $guzzleConfig )? $guzzleConfig[$paramKey] : null );
    }

    public function doRequest(string $apiPath, array $dataInputs = []) {

        if(!strlen($apiPath)) return [
            'status' => 'failed',
            'message' => 'Invalid API Path',
        ];

        $apiPath = explode('/', $apiPath);

        $apiRequestData = [];

        foreach(['a', 'b', 'c', 'd', 'e', 'f', 'g'] as $index => $key) {
            if(!isset($apiPath[$index])) break;
            $apiRequestData[$key] = $apiPath[$index];
        }

        if(count($dataInputs)) $apiRequestData['data'] = $dataInputs;

        $apiRequestData = $this->signRequest($apiRequestData);

        $this->lastQuery = http_build_query($apiRequestData);
        
        try {
            $apiRequest = $this->guzzleClient->request('POST', $this->endPoint, ['form_params' => $apiRequestData]);
        } catch(\Exception $e) {
            return [
                'status' => 'failed',
                'message' => $e->getMessage(),
            ];
        }
        
		if(!$apiRequest->getStatusCode() || intval($apiRequest->getStatusCode()) !== 200) {
            $returnResult = [
                'status' => 'failed', 
                'message' => 'HTTP request failure',
            ];

            try {
                $returnResult['message'] = $apiRequest->getReasonPhrase();
            } catch(\Exception $e) {
                return $returnResult;
            }
            return $returnResult;
        }

        $apiResponse = null;

        try {
            $apiResponse = $apiRequest->getBody()->getContents();
        } catch(\Exception $e) {
            return [
                'status' => 'failed', 
                'message' => $e->getMessage(),
            ];
        }
        
        $apiResponse = $this->checkResponse($apiResponse);

        if(!$apiResponse || !is_array($apiResponse))
            return [
                'status' => 'failed', 
                'message' => 'Invalid API Response',
            ];

        return $apiResponse;
    }

    public function isSuccess($requestResponse) {
        if(!$this->checkResponse($requestResponse)) return false;
        return ($requestResponse['status'] === 'success')? true : false;
    }

    public function getMessage($requestResponse) {
        if(!$this->checkResponse($requestResponse)) return 'Unknown Error';
        return $requestResponse['message'];
    }

    public function getData($requestResponse) {
        if(!$this->checkResponse($requestResponse) || !isset($requestResponse['data'])) return null;
        return $requestResponse['data'];
    }

    private function checkResponse($apiResponse = null) {
        if(!$apiResponse) return false;

        if(!is_array($apiResponse)) {
            json_decode($apiResponse,true);
            if(json_last_error() !== JSON_ERROR_NONE) return false;
            $apiResponse = json_decode($apiResponse,true);
        }

        if(!is_array($apiResponse) || !isset($apiResponse['status']) || !array_key_exists('message', $apiResponse)) return false;

        return $apiResponse;
    }

  
}