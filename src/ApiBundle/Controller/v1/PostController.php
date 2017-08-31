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
	 * @Route("/posts/")
	 * @Method("GET")
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
	 * @Route("/posts/{id}")
	 * @Method("GET")
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
