<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Curl\Curl;




class WC_DK_PLUS{
    
    private $api_base_url;
    private $api_key;
    
    public function __construct() {
        // $this->api_base_url = $api_base_url;
        // $this->api_key = $api_key;
    }   
    
    public function init(){
        $curl = new Curl();
        $curl->get('https://www.example.com/');

        if ($curl->error) {
            echo 'Error: ' . $curl->errorMessage . "\n";
            $curl->diagnose();
        } else {
            echo 'Response:' . "\n";
            var_dump($curl->response);
        }
        die;
    }
}
