<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Post controller.
 *
 * @Route("posts")
 */
class PostController extends Controller
{
	/**
	 * Lists all post entities.
	 *
	 * @Route("/", name="post_index")
	 * @Method("GET")
	 * @Security("has_role('ROLE_WRITER')")
	 */
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();
		$posts = $em->getRepository('AppBundle:Post')->findBy(array(), array('createdAt' => 'DESC'));

		return $this->render('post/index.html.twig', array(
			'posts' => $posts,
		));
	}

	/**
	 * Creates a new post entity.
	 *
	 * @Route("/new", name="post_new")
	 * @Method({"GET", "POST"})
	 * @Security("has_role('ROLE_WRITER')")
	 */
	public function newAction(Request $request)
	{
		$post = new Post();
		$form = $this->createForm('AppBundle\Form\PostType', $post);
		$form->handleRequest($request);

		// If the form has been subitted, and data is valid, create the new Post and redirect
		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$post->setCreatedAt(new \DateTime);

			/* IMAGE UPLOAD */
			// The file object
			$file = $post->getImage();
			// Create a unique name, and use symfony fuessEtension method to prevent fake extension (will detect with mime type)
			$fileName = md5(uniqid()) . '.' . $file->guessExtension();
			// Store the file in the folder configure in the config yml
			$file->move(
				$this->getParameter('posts_images_directory'),
				$fileName
			);
			// Set the unique name as name in the field
			$post->setImage($fileName);

			$em->persist($post);
			$em->flush();

			return $this->redirectToRoute('post_index');
		}

		// If no Submitted form (just want to access to the form) or form error validation, display the view with form
		return $this->render('post/new.html.twig', array(
			'post' => $post,
			'form' => $form->createView(),
		));
	}

	/**
	 * Search a post include specific terms.
	 * IMPORTANT : Have to be above showAction, because under it bug, it think that the /search is the /{id}
	 *
	 * @Route("/search", name="post_search")
	 * @Method({"POST"})
	 */
	public function searchAction(Request $request)
	{
		$searchTerm = $request->request->get("term");

		$em = $this->getDoctrine()->getManager();
		// variables used in the right container (separated twig included in main twig)
		$lastPosts = $em->getRepository('AppBundle:Post')->findBy(array(), array('createdAt' => 'DESC'), 3);
		$categories = $em->getRepository("AppBundle:Category")->findBy(array(), array('name' => 'ASC'));

		// Custom query, search in post.title the term %searchTerm% and I order by date, most recent first
		$query = $em
			->createQuery("SELECT u FROM AppBundle:Post u WHERE u.title LIKE :searchTerm OR u.content LIKE :searchTerm ORDER BY u.createdAt DESC")
			->setParameter('searchTerm', '%'.$searchTerm."%");
		$posts = $query->getResult();

		return $this->render('post/search.html.twig', array(
			'searchTerm' => $searchTerm,
			'posts'      => $posts,
			'lastPosts'  => $lastPosts,
			'categories' => $categories,
		));
	}

	/**
	 * Finds and displays a post entity.
	 *
	 * @Route("/{id}", name="post_show")
	 * @Method({"GET", "POST"})
	 */
	public function showAction(Post $post, Request $request)
	{
		$comment = new Comment();
		$newCommentForm = $this->createForm('AppBundle\Form\CommentType', $comment);
		$newCommentForm->handleRequest($request);

		$em = $this->getDoctrine()->getManager();

		// variables used in the right container (separated twig included in main twig)
		$lastPosts = $em->getRepository('AppBundle:Post')->findBy(array(), array('createdAt' => 'DESC'), 3);
		$categories = $em->getRepository("AppBundle:Category")->findBy(array(), array('name' => 'ASC'));

		if ($newCommentForm->isSubmitted() && $newCommentForm->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$comment->setCreatedAt(new \DateTime);
			$comment->setPost($post);
			$em->persist($comment);
			$em->flush();

			return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
		}

		return $this->render('post/show.html.twig', array(
			'post'           => $post,
			'newCommentForm' => $newCommentForm->createView(),
			'lastPosts'      => $lastPosts,
			'categories'     => $categories,
		));
	}

	/**
	 * Displays a form to edit an existing post entity.
	 *
	 * @Route("/{id}/edit", name="post_edit")
	 * @Method({"GET", "POST"})
	 * @Security("has_role('ROLE_WRITER')")
	 */
	public function editAction(Request $request, Post $post)
	{
		// Load a special form with delete method etc..
		$deleteForm = $this->createDeleteForm($post);

		// Get the oldImageName, before we load on our object the form data values ($editForm->handleRequest($request);)
		$oldImageName = $post->getImage();

		// Edit form
		$editForm = $this->createForm('AppBundle\Form\PostType', $post);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			// We edit the post, so delete old image (will add the new after) if exists
			if (!empty($oldImageName)) {
				if (file_exists($this->getParameter('posts_images_directory') . '/' . $oldImageName)) {
					unlink($this->getParameter('posts_images_directory') . '/' . $oldImageName);
				}
			}

			/* IMAGE UPLOAD */
			// The file object
			$file = $post->getImage();
			// Create a unique name, and use symfony fuessEtension method to prevent fake extension (will detect with mime type)
			$fileName = md5(uniqid()) . '.' . $file->guessExtension();
			// Store the file in the folder configure in the config yml
			$file->move(
				$this->getParameter('posts_images_directory'),
				$fileName
			);
			// Set the unique name as name in the field
			$post->setImage($fileName);

			// Get the entityManager and flush the post object
			$this->getDoctrine()->getManager()->flush();

			return $this->redirectToRoute('post_index');
		}

		return $this->render('post/edit.html.twig', array(
			'form'        => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
			'post'        => $post,
		));
	}

	/**
	 * Deletes a post entity.
	 *
	 * @Route("/{id}", name="post_delete")
	 * @Method("DELETE")
	 * @Security("has_role('ROLE_WRITER')")
	 */
	public function deleteAction(Request $request, Post $post)
	{
		$form = $this->createDeleteForm($post);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->remove($post);
			$em->flush();
		}

		return $this->redirectToRoute('post_index');
	}

	/**
	 * Creates a form to delete a post entity.
	 *
	 * @param Post $post The post entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createDeleteForm(Post $post)
	{
		return $this->createFormBuilder()
			->setAction($this->generateUrl('post_delete', array('id' => $post->getId())))
			->setMethod('DELETE')
			->getForm();
	}


}
