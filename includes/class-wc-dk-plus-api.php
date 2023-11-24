<?php

if ( !defined( 'ABSPATH' ) )
   exit;

require_once __DIR__ . '/../vendor/autoload.php';

use Curl\Curl;

class WC_DK_PLUS_API {

	private $username;
	private $password;

	public function __construct() {
		$this->username = get_option( 'wc_dk_plus_username' );
		$this->password = get_option( 'wc_dk_plus_password' );
	}

	public function http_request( $request ) {

		$curl = new Curl();
		$curl->setBasicAuthentication( $this->username, $this->password );
		$curl->setUserAgent( $request['user_agent'] );
		$curl->setHeader( 'X-Requested-With', 'XMLHttpRequest' );
		$curl->setCookie( 'request_sender', 'aksurweb' );
		$curl->get( $request['endpoint'] );

		if ( $curl->error ) {
			echo 'Error: ' . $curl->errorMessage . "\n";
		} else {
			echo 'Response:' . "\n";
			var_dump( $curl->response );
		}

		var_dump( $curl->response );
		exit;
	}
}
