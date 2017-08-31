<?php

namespace ApiBundle\Controller\v2;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends Controller
{
	/**
	 * @Route("/posts/", name="api_v2_get_posts")
	 * @Method("GET")
	 */
	public function getPostsAction()
	{
		dump("v2 in progress");
		die();
	}

	/**
	 * @Route("/posts/{id}", name="api_v2_get_post")
	 * @Method("GET")
	 */
	public function getPostAction(Request $request)
	{
		dump("v2 in progress");
		die();
	}

}
