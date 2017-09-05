<?php

namespace ApiBundle\Controller\v1;

use ApiBundle\Helpers\apiHelpers;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryPostController extends Controller
{
	/**
	 * @Route("/categories/{id}/posts", name="api_v1_get_category_posts")
	 * @Method("GET")
	 */
	public function getCategoryPostsAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$category = $em->getRepository('AppBundle:Category')->find($request->get('id'));

		// Get the "Accept" param in the http headers request from the client
		switch($request->headers->get('accept')){
			// user want a XML response
			case 'application/xml':
				// The Categories doesn't exists => create response with error message and the status code 404
				if (empty($category)) {
					return apiHelpers::displayError('xml', 404, 'Category '.$request->get('id').' not found', new JsonResponse('', Response::HTTP_NOT_FOUND));
				}
				// The Category has 0 posts
				elseif (sizeof($category->getPosts()) == 0){
					return apiHelpers::displayError('xml', 404, 'Posts not found', new JsonResponse('', Response::HTTP_NOT_FOUND));
				}
				// The posts exists
				else{
					$posts = $category->getPosts();
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
				// The Categories doesn't exists => create response with error message and the status code 404
				if (empty($category)) {
					return apiHelpers::displayError('json', 404, 'Category '.$request->get('id').' not found', new JsonResponse('', Response::HTTP_NOT_FOUND));
				}
				// The Category has 0 posts
				elseif (sizeof($category->getPosts()) == 0){
					return apiHelpers::displayError('json', 404, 'Posts not found', new JsonResponse('', Response::HTTP_NOT_FOUND));
				}
				// The post exists
				else {
					$posts = $category->getPosts();
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
}
