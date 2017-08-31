<?php

namespace ApiBundle\Controller\v1;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
				// Header
				$xml = '<?xml version="1.0" encoding="UTF-8"?>';
				// The Post doesn't exists => create response with error message and the status code 404
				if (empty($posts)) {
					$xml .= '<error><code>404</code><message>Posts not found</message></error>';
					$response = new Response($xml, Response::HTTP_NOT_FOUND);
				}
				// The posts exists
				else{
					$xml .= '<posts>';
					foreach ($posts as $post) {
						// Author of the post
						if (!empty($post->getAuthor())) {
							$author = $post->getAuthor();
							$xmlAuthor = '<author><id>'.$author->getId().'</id><username>'.$author->getUsername().'</username></author>';
						}else{
							$xmlAuthor = '<author></author>';
						}
						$xml .= '<post><id>'.$post->getId().'</id><title>'.$post->getTitle().'</title><created_at>'.$post->getCreatedAt()->format('Y-m-d H:i').'</created_at>'.$xmlAuthor.'</post>';
					}
					$xml .= '</posts>';
					$response = new Response($xml, Response::HTTP_OK);
				}

				// Set the header "Content-Type" to the http response
				$response->headers->set('Content-Type', 'application/xml');
				return $response;
				break;
			// Default response in JSON
			default:
				// The Post doesn't exists => create response with error message and the status code 404
				if (empty($posts)) {
					$json = [
						'error' => [
							'code'    => '404',
							'message' => 'Posts not found',
						],
					];

					$response = new JsonResponse($json, Response::HTTP_NOT_FOUND);
				}
				// The post exists
				else {
					foreach ($posts as $post) {
						// Author of the post
						if (!empty($post->getAuthor())) {
							$author = $post->getAuthor();
							$authorArray = [
								'id'   => $author->getId(),
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

						$response = new JsonResponse($json, Response::HTTP_OK);
					}
				}
				// Set the header "Content-Type" to the http response
				$response->headers->set('Content-Type', 'application/json');
				return $response;
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
				// Header
				$xml = '<?xml version="1.0" encoding="UTF-8"?>';
				// The Post doesn't exists => create response with error message and the status code 404
				if (empty($post)) {
					$xml .= '<error><code>404</code><message>Post not found</message></error>';
					$response = new Response($xml, Response::HTTP_NOT_FOUND);
				}
				// The post exists
				else{
					// Author of the post
					if (!empty($post->getAuthor())) {
						$author = $post->getAuthor();
						$xmlAuthor = '<author><id>'.$author->getId().'</id><username>'.$author->getUsername().'</username></author>';
					}else{
						$xmlAuthor = '<author></author>';
					}
					$xml .= '<post><id>'.$post->getId().'</id><title>'.$post->getTitle().'</title><created_at>'.$post->getCreatedAt()->format('Y-m-d H:i').'</created_at>'.$xmlAuthor.'</post>';
					$response = new Response($xml, Response::HTTP_OK);
				}

				// Set the header "Content-Type" to the http response
				$response->headers->set('Content-Type', 'application/xml');
				return $response;
				break;
			// Default response in JSON
			default:
				// The Post doesn't exists => create response with error message and the status code 404
				if (empty($post)) {
					$json = [
						'error' => [
							'code'    => '404',
							'message' => 'Post not found',
						],
					];

					$response = new JsonResponse($json, Response::HTTP_NOT_FOUND);
				}
				// The post exists
				else {
					// Author of the post
					if (!empty($post->getAuthor())) {
						$author = $post->getAuthor();
						$authorArray = [
							'id'   => $author->getId(),
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
				}
				// Set the header "Content-Type" to the http response
				$response->headers->set('Content-Type', 'application/json');
				return $response;
				break;
		}
	}

}
