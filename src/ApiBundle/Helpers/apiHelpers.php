<?php
/**
 * Created by PhpStorm.
 * User: loic
 * Date: 01.09.2017
 * Time: 13:24
 */

namespace ApiBundle\Helpers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class apiHelpers
{
	// The token expire after this time (24hours)
	public static $TOKEN_EXPIRATION_TIME = 24 * 3600;

	/**
	 * @param $format "The return formats (xml or json)"
	 * @param $code "The http code"
	 * @param $message "The message will display"
	 * @param $response "The Response object"
	 * @return mixed "The Response object with all datas"
	 */
	public static function displayError($format, $code, $message, $response){
		switch ($format){
			// user want a XML response
			case 'xml':
				// Header
				$xml = '<?xml version="1.0" encoding="UTF-8"?>';
				// Content
				$xml .= '<error><code>'.$code.'</code><message>'.$message.'</message></error>';
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
				$response->setContent(json_encode($json));
				// Set the header "Content-Type" to the http response
				$response->headers->set('Content-Type', 'application/json');
				return $response;
				break;
		}

	}

	/**
	 * Check if a Token is pass in the http header and check if his valid
	 *
	 * If valid return : null (no error), if invalid : return the Response error
	 *
	 * @param $request "The request from the controller"
	 * @param $em "The entity manager from the controller"
	 * @return mixed|null "Response / Json Reesponse | null"
	 */
	public static function getTokenError($request, $em){
		// Missing X-Auth-Token in the http header
		if(empty($request->headers->get('X-Auth-Token'))){
			// Get the "Accept" param in the http headers request from the client
			switch($request->headers->get('accept')){
				// user want a XML response
				case 'application/xml':
					return apiHelpers::displayError('xml', 400, 'Bad request, maybe missing X-Auth-Token header in your request', new Response('', Response::HTTP_BAD_REQUEST));
					break;
				// Default response in JSON
				default:
					return apiHelpers::displayError('json', 400, 'Bad request, maybe missing X-Auth-Token header in your request', new JsonResponse('', Response::HTTP_BAD_REQUEST));
					break;
			}
		}
		// X-Auth-Token exists
		else{
			$apiToken = $em->getRepository('UserBundle:ApiToken')->findBy(array('token' => $request->headers->get('X-Auth-Token')), array(), 1);
			// Bad Token
			if(empty($apiToken)){
				// Get the "Accept" param in the http headers request from the client
				switch($request->headers->get('accept')){
					// user want a XML response
					case 'application/xml':
						return apiHelpers::displayError('xml', 401, 'Bad token', new Response('', Response::HTTP_UNAUTHORIZED));
						break;
					// Default response in JSON
					default:
						return apiHelpers::displayError('json', 401, 'Bad token', new JsonResponse('', Response::HTTP_UNAUTHORIZED));
						break;
				}
			}
			// Token exists
			else{
				$apiToken = $apiToken[0]; // Because findBy return an array ...
				$now = new \DateTime();
				// Token expired
				if ($now->getTimestamp() >= $apiToken->getExpireAt()){
					// Get the "Accept" param in the http headers request from the client
					switch($request->headers->get('accept')){
						// user want a XML response
						case 'application/xml':
							return apiHelpers::displayError('xml', 401, 'token expired', new Response('', Response::HTTP_UNAUTHORIZED));
							break;
						// Default response in JSON
						default:
							return apiHelpers::displayError('json', 401, 'token expired', new JsonResponse('', Response::HTTP_UNAUTHORIZED));
							break;
					}
				}
				// Token is OK
				else{
					return null;
				}
			}
		}
	}
}