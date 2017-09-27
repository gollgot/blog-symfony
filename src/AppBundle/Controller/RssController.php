<?php

namespace AppBundle\Controller;

use FeedIo\Feed;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class RssController extends Controller
{
	/**
	 * Lists all category entities.
	 *
	 * @Route("/rss/read", name="rss_read")
	 * @Method({"GET", "POST"})
	 */
    public function ReadAction(Request $request)
    {
		$em = $this->getDoctrine()->getManager();

		$lastPosts = $em->getRepository('AppBundle:Post')->findBy(array(), array('createdAt' => 'DESC'), 3);
		$categories = $em->getRepository('AppBundle:Category')->findBy(array(), array('name' => 'ASC'));

		$rssLink = $request->request->get("rss-link");

		// get it through the container
		$feedIo = $this->container->get('feedio');
		// read a feed
		$result = $feedIo->read($rssLink);
		// get feed title
		$feedTitle = $result->getFeed()->getTitle();
		$feedItems = $result->getFeed();


		return $this->render('rss/read_20min_rss.html.twig', [
			'lastPosts'  => $lastPosts,
			'categories' => $categories,
			'feedTitle'  => $feedTitle,
			'feedItems'  => $feedItems,
		]);


    }

}
