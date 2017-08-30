<?php

namespace ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class PostController extends Controller
{
	/**
	 * @Route("/posts", name="api_posts_list")
	 * @Method({"GET"})
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
}
