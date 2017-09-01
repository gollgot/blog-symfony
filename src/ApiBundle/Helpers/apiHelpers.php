<?php
/**
 * Created by PhpStorm.
 * User: loic
 * Date: 01.09.2017
 * Time: 13:24
 */

namespace ApiBundle\Controller\v1;


class apiHelpers
{
	public static function displayError($format, $code, $message, $response){
		switch ($format){
			// user want a XML response
			case 'xml':
				// Header
				$xml = '<?xml version="1.0" encoding="UTF-8"?>';
				// Content
				$xml .= '<error><code>$code</code><message>$message</message></error>';
				$response->setContent($xml);
				// Set the header "Content-Type" to the http response
				$response->headers->set('Content-Type', 'application/xml');
				return $response;
				break;
			// Default response in JSON
			default:
				// Content
				$json = [
					'error' => [
						'code'    => $code,
						'message' => $message,
					],
				];
				$response->setContent($json);
				// Set the header "Content-Type" to the http response
				$response->headers->set('Content-Type', 'application/json');
				return $response;
				break;
		}

	}
}