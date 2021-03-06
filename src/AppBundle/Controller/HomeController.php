<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller
{
	/**
	 * @Route("/", name="homepage")
	 */
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();

		$posts = $em->getRepository('AppBundle:Post')->findBy(array(), array('createdAt' => 'DESC'));
		$lastPosts = $em->getRepository('AppBundle:Post')->findBy(array(), array('createdAt' => 'DESC'), 3);
		$categories = $em->getRepository('AppBundle:Category')->findBy(array(), array('name' => 'ASC'));

		return $this->render('home/index.html.twig', [
			'posts'      => $posts,
			'lastPosts'  => $lastPosts,
			'categories' => $categories,
		]);
	}
}
