<?php

namespace ApiBundle\Controller\Authentication;

use App\UserBundle\Entity\ApiToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use ApiBundle\Helpers\apiHelpers;

class AuthController extends Controller
{
	/**
	 * @Route("/auth", name="api_get_auth")
	 * @Method("GET")
	 */
	public function getAuthAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		// Bad request, no "Authorization" header found
		if(empty($request->headers->get('Authorization'))){
			// Get the "Accept" param in the http headers request from the client
			switch($request->headers->get('accept')){
				// user want a XML response
				case 'application/xml':
					return apiHelpers::displayError('xml', 400, 'Bad request, maybe missing Authorization header in your request', new Response('', Response::HTTP_BAD_REQUEST));
					break;
				// Default response in JSON
				default:
					return apiHelpers::displayError('json', 400, 'Bad request, maybe missing Authorization header in your request', new JsonResponse('', Response::HTTP_BAD_REQUEST));
					break;
			}
		}
		// Good request
		else{
			// Get the user in DB
			$username_url = $request->headers->get('php-auth-user');
			$raw_password_url = $request->headers->get('php-auth-pw');
			$user = $em->getRepository("UserBundle:User")->findBy(array('username' => $username_url));
			// User wanted doesn't exists
			if(empty($user)){
				// Get the "Accept" param in the http headers request from the client
				switch($request->headers->get('accept')){
					// user want a XML response
					case 'application/xml':
						return apiHelpers::displayError('xml', 404, 'User '.$username_url.' not found', new Response('', Response::HTTP_NOT_FOUND));
						break;
					// Default response in JSON
					default:
						return apiHelpers::displayError('json', 404, 'User '.$username_url.' not found', new JsonResponse('', Response::HTTP_NOT_FOUND));
						break;
				}
			}
			// User exists
			else{
				// Compare password in DB with password from URL
				$user = $user[0]; // Because $user is an array of one .. ?
				$password_url = hash('sha512', $raw_password_url.'{'.$user->getSalt().'}');
				// Bad credentials
				if($password_url != $user->getPassword()){
					// Get the "Accept" param in the http headers request from the client
					switch($request->headers->get('accept')){
						// user want a XML response
						case 'application/xml':
							return apiHelpers::displayError('xml', 401, 'Bad credentials', new Response('', Response::HTTP_UNAUTHORIZED));
							break;
						// Default response in JSON
						default:
							return apiHelpers::displayError('json', 401, 'Bad credentials', new JsonResponse('', Response::HTTP_UNAUTHORIZED));
							break;
					}
				}
				// Good credentials -> create Token
				else{
					// User already have an API token
					if(!empty($user->getApiToken())){
						$apiToken = $user->getApiToken(); // Get the old token to update it
					}
					// User doesn't have an API token
					else{
						$apiToken = new ApiToken();
					}
					// Token creation
					$token = $this->generateToken();
					$apiToken->setToken($token);
					$apiToken->setCreatedAt(new \DateTime());
					$apiToken->setExpireAt($apiToken->getCreatedAt()->getTimestamp() + apiHelpers::$TOKEN_EXPIRATION_TIME);
					$apiToken->setUser($user);
					// Save it
					$em->persist($apiToken);
					$em->flush();

					// RESPONSE
					// Get the "Accept" param in the http headers request from the client
					switch($request->headers->get('accept')){
						// user want a XML response
						case 'application/xml':
							// Header
							$xml = '<?xml version="1.0" encoding="UTF-8"?>';
							$xml .= '<success>true</success><token>'.$token.'</token><expire_at>'.date('d-m-Y H:i', $apiToken->getExpireAt()).'</expire_at>';
							$response = new Response($xml, Response::HTTP_OK);
							// Set the header "Content-Type" to the http response
							$response->headers->set('Content-Type', 'application/xml');
							return $response;
							break;
						// Default response in JSON
						default:
							$json = [
								'success'   => true,
								'token'     => $token,
								'expire_at' => date('d-m-Y H:i', $apiToken->getExpireAt())
							];
							$response = new JsonResponse($json, Response::HTTP_OK);
							// Set the header "Content-Type" to the http response
							$response->headers->set('Content-Type', 'application/json');
							return $response;
							break;
					}
				}
			}
		}
	}



	// Generate a unique 35 string token AlphaNumeric
	private function generateToken(){
		return md5(uniqid(rand(), true));
	}

}
