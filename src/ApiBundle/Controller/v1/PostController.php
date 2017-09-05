<?php

namespace ApiBundle\Controller\v1;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use ApiBundle\Helpers\apiHelpers;

class PostController extends Controller
{
	/**
	 * @Route("/posts/", name="api_v1_get_posts")
	 * @Method("GET")
	 */
	public function getPostsAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$posts = $em->getRepository('AppBundle:Post')->findBy(array(), array('createdAt' => 'DESC'));

		// Get the "Accept" param in the http headers request from the client
		switch($request->headers->get('accept')){
			// user want a XML response
			case 'application/xml':
				// The Post doesn't exists => create response with error message and the status code 404
				if (empty($posts)) {
					return apiHelpers::displayError('xml', 404, 'Posts not found', new Response('',Response::HTTP_NOT_FOUND));
				}
				// The posts exists
				else{
					// Header
					$xml = '<?xml version="1.0" encoding="UTF-8"?>';
					$xml .= '<posts>';
					foreach ($posts as $post) {
						// Author of the post
						if (!empty($post->getAuthor())) {
							$author = $post->getAuthor();
							$xmlAuthor = '<author><username>'.$author->getUsername().'</username></author>';
						}else{
							$xmlAuthor = '<author></author>';
						}
						$xml .= '<post><id>'.$post->getId().'</id><title>'.$post->getTitle().'</title><created_at>'.$post->getCreatedAt()->format('Y-m-d H:i').'</created_at>'.$xmlAuthor.'</post>';
					}
					$xml .= '</posts>';
					$response = new Response($xml, Response::HTTP_OK);
					// Set the header "Content-Type" to the http response
					$response->headers->set('Content-Type', 'application/xml');
					return $response;
				}
				break;
			// Default response in JSON
			default:
				// The Post doesn't exists => create response with error message and the status code 404
				if (empty($posts)) {
					return apiHelpers::displayError('json', 404, 'Posts not found', new JsonResponse('', Response::HTTP_OK));
				}
				// The post exists
				else {
					$json = [];
					foreach ($posts as $post) {
						// Author of the post
						if (!empty($post->getAuthor())) {
							$author = $post->getAuthor();
							$authorArray = [
								'name' => $author->getUsername()
							];
						} else {
							$authorArray = null;
						}

						// All formated datas
						$json[] = [
							'id'         => $post->getId(),
							'title'      => $post->getTitle(),
							'created_at' => $post->getCreatedAt()->format('Y-m-d H:i'),
							'author'     => $authorArray,
						];
					}
					$response = new JsonResponse($json, Response::HTTP_OK);
					// Set the header "Content-Type" to the http response
					$response->headers->set('Content-Type', 'application/json');
					return $response;
				}
				break;
		}
	}

	/**
	 * @Route("/posts/{id}", name="api_v1_get_post")
	 * @Method("GET")
	 */
	public function getPostAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$post = $em->getRepository('AppBundle:Post')->find($request->get('id'));

		// Get the "Accept" param in the http headers request from the client
		switch($request->headers->get('accept')){
			// user want a XML response
			case 'application/xml':
				// The Post doesn't exists => create response with error message and the status code 404
				if (empty($post)) {
					return apiHelpers::displayError('xml', 404, 'Post with id '.$request->get('id').' not found.', new Response('', Response::HTTP_NOT_FOUND));
				}
				// The post exists
				else{
					// Author of the post
					if (!empty($post->getAuthor())) {
						$author = $post->getAuthor();
						$xmlAuthor = '<author><username>'.$author->getUsername().'</username></author>';
					}else{
						$xmlAuthor = '<author></author>';
					}
					// Header
					$xml = '<?xml version="1.0" encoding="UTF-8"?>';
					// Content
					$xml .= '<post><id>'.$post->getId().'</id><title>'.$post->getTitle().'</title><created_at>'.$post->getCreatedAt()->format('Y-m-d H:i').'</created_at>'.$xmlAuthor.'</post>';
					$response = new Response($xml, Response::HTTP_OK);
					// Set the header "Content-Type" to the http response
					$response->headers->set('Content-Type', 'application/xml');
					return $response;
				}
				break;
			// Default response in JSON
			default:
				// The Post doesn't exists => create response with error message and the status code 404
				if (empty($post)) {
					return apiHelpers::displayError('json', 404, 'Post with id '.$request->get('id').' not found.', new JsonResponse('5858', Response::HTTP_NOT_FOUND));
				}
				// The post exists
				else {
					// Author of the post
					if (!empty($post->getAuthor())) {
						$author = $post->getAuthor();
						$authorArray = [
							'name' => $author->getUsername()
						];
					} else {
						$authorArray = null;
					}

					// All formated datas
					$json = [
						'id'         => $post->getId(),
						'title'      => $post->getTitle(),
						'created_at' => $post->getCreatedAt()->format('Y-m-d H:i'),
						'author'     => $authorArray,
					];

					$response = new JsonResponse($json, Response::HTTP_OK);
					// Set the header "Content-Type" to the http response
					$response->headers->set('Content-Type', 'application/json');
					return $response;
				}
				break;
		}
	}

}
