<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Curl\Curl;




class WC_DK_PLUS_API{
    
    private $username;
    private $password;
    
    public function __construct() {
        $this->username =  get_option( 'wc_dk_plus_username' );
        $this->password =  get_option( 'wc_dk_plus_password' );
    }   
    
    public function init(){
        $curl = new Curl();
        $curl->setBasicAuthentication( $this->username, $this->password );
        $curl->setUserAgent('MyUserAgent/0.0.1 (+https://www.example.com/bot.html)');
        $curl->setHeader('X-Requested-With', 'XMLHttpRequest');
        $curl->setCookie('key', 'value');
        $curl->get('https://api.dkplus.is/api/v1/sales/invoice/2');
        
        
        if ($curl->error) {
            echo 'Error: ' . $curl->errorMessage . "\n";
        } else {
            echo 'Response:' . "\n";
            var_dump($curl->response);
        }
        
    }
}
