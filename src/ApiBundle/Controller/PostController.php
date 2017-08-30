<?php

namespace ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
	/**
	 * @Get("/posts/")
	 */
	public function getPostsAction()
	{
		$em = $this->getDoctrine()->getManager();
		$posts = $em->getRepository('AppBundle:Post')->findBy(array(), array('createdAt' => 'DESC'));

		$formatted = [];
		foreach ($posts as $post) {
			// Author
			if(!empty($post->getAuthor())){
				$author = $post->getAuthor();
				$authorArray[] = [
					'id' => $author->getId(),
					'name' => $author->getUsername()
				];
			}else{
				$authorArray = null;
			}

			// All formated datas
			$formatted[] = [
				'id' => $post->getId(),
				'title' => $post->getTitle(),
				'created_at' => $post->getCreatedAt(),
				'author' => $authorArray,
			];
		}

		return new JsonResponse($formatted);

	}

	/**
	 * @Get("/posts/{id}")
	 */
	public function getPostAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$post = $em->getRepository('AppBundle:Post')->find($request->get('id'));

		// The Post doesn't exists => return error message with the status code 404
		if (empty($post)) {
			return new JsonResponse(['message' => 'Post not found'], Response::HTTP_NOT_FOUND);
		}

		// Author
		if(!empty($post->getAuthor())){
			$author = $post->getAuthor();
			$authorArray[] = [
				'id' => $author->getId(),
				'name' => $author->getUsername()
			];
		}else{
			$authorArray = null;
		}

		// All formated datas
		$formatted[] = [
			'id' => $post->getId(),
			'title' => $post->getTitle(),
			'created_at' => $post->getCreatedAt(),
			'author' => $authorArray,
		];

		// Default JsonResponse used a 200 status code
		return new JsonResponse($formatted);
	}

}
