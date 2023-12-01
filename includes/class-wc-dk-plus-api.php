<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

use Curl\Curl;

class WC_DK_PLUS_API {

	private $username;
	private $password;

	public function __construct() {
		$this->username = get_option( 'wc_dk_plus_username' );
		$this->password = get_option( 'wc_dk_plus_password' );
	}

	public function http_request( $request, $payload ) {

		$curl = new Curl();
		$curl->setBasicAuthentication( $this->username, $this->password );
		$curl->setUserAgent( $request['user_agent'] );
		$curl->setHeader( 'X-Requested-With', 'XMLHttpRequest' );
        $curl->setHeader(  'Content-Type', 'application/json');
		$curl->setCookie( 'request_sender', 'aksurweb' );

		if ( $request['request_type'] === 'GET' ) {
			$curl->get( $request['endpoint'] );
		} elseif ( $request['request_type'] === 'POST' ) {
			$curl->post( $request['endpoint'], $payload );
		}

		if ( $curl->error ) {
            $response =  'Error: ' . $curl->errorMessage . "\n";
		} else {
            $response = $curl->response;
		}

        return $response;
	}
}
