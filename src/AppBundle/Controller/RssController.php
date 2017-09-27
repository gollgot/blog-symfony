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
	 * Read a Feed RSS Url and display the feed
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
		$feedTitle = $result->getFeed()->getTitle()." / ".$result->getFeed()->getDescription();
		$feedItems = $result->getFeed();


		return $this->render('rss/read_20min_rss.html.twig', [
			'lastPosts'  => $lastPosts,
			'categories' => $categories,
			'feedTitle'  => $feedTitle,
			'feedItems'  => $feedItems,
		]);

    }

	/**
	 *
	 *
	 * @Route("/rss/feed", name="rss_feed")
	 * @Method({"GET"})
	 */
    public function FeedAction()
	{
		$em = $this->getDoctrine()->getManager();
		$posts = $em->getRepository('AppBundle:Post')->findAll();

		// build the feed
		$feedIo = $this->container->get('feedio');

		// build the feed
		$feed = new Feed;
		$feed->setTitle('Mon titre');

		foreach($posts as $post){
			$item = $feed->newItem();
			$item->setTitle($post->getTitle());
			$item->setDescription(substr($post->getContent(), 0, 40)."...");
			$item->setLink($this->generateUrl('post_show', array('id' => $post->getId())));
			$feed->add($item);
		}

		// convert it into Atom
		$atomString = $feedIo->toAtom($feed);
		dump($atomString);
		die();
	}

}
