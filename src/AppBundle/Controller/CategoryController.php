<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Category controller.
 *
 * @Route("categories")
 */
class CategoryController extends Controller
{
	/**
	 * Lists all category entities.
	 *
	 * @Route("/", name="categories_index")
	 * @Method("GET")
	 * @Security("has_role('ROLE_WRITER')")
	 */
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();

		$categories = $em->getRepository('AppBundle:Category')->findAll();

		return $this->render('category/index.html.twig', array(
			'categories' => $categories,
		));
	}

	/**
	 * Creates a new category entity.
	 *
	 * @Route("/new", name="categories_new")
	 * @Method({"GET", "POST"})
	 * @Security("has_role('ROLE_WRITER')")
	 */
	public function newAction(Request $request)
	{
		$category = new Category();
		$form = $this->createForm('AppBundle\Form\CategoryType', $category);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($category);
			$em->flush();

			return $this->redirectToRoute('categories_index');
		}

		return $this->render('category/new.html.twig', array(
			'category' => $category,
			'form'     => $form->createView(),
		));
	}

	/**
	 * Finds and displays a post entity.
	 *
	 * @Route("/{id}", name="categories_show")
	 * @Method({"GET"})
	 */
	public function showAction(Category $category)
	{
		$postsOfCategory = $category->getPosts();

		$em = $this->getDoctrine()->getManager();

		// variables used in the right container (separated twig included in main twig)
		$lastPosts = $em->getRepository('AppBundle:Post')->findBy(array(), array('createdAt' => 'DESC'), 3);
		$categories = $em->getRepository("AppBundle:Category")->findBy(array(), array('name' => 'ASC'));

		return $this->render('category/show.html.twig', array(
			'category'        => $category,
			'posts' => $postsOfCategory,
			'lastPosts'       => $lastPosts,
			'categories'      => $categories,
		));
	}

	/**
	 * Displays a form to edit an existing category entity.
	 *
	 * @Route("/{id}/edit", name="categories_edit")
	 * @Method({"GET", "POST"})
	 * @Security("has_role('ROLE_WRITER')")
	 */
	public function editAction(Request $request, Category $category)
	{
		$deleteForm = $this->createDeleteForm($category);
		$editForm = $this->createForm('AppBundle\Form\CategoryType', $category);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->getDoctrine()->getManager()->flush();

			return $this->redirectToRoute('categories_index');
		}

		return $this->render('category/edit.html.twig', array(
			'category'    => $category,
			'edit_form'   => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
		));
	}

	/**
	 * Deletes a category entity.
	 *
	 * @Route("/{id}", name="categories_delete")
	 * @Method("DELETE")
	 * @Security("has_role('ROLE_WRITER')")
	 */
	public function deleteAction(Request $request, Category $category)
	{
		$form = $this->createDeleteForm($category);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->remove($category);
			$em->flush();
		}

		return $this->redirectToRoute('categories_index');
	}

	/**
	 * Creates a form to delete a category entity.
	 *
	 * @param Category $category The category entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createDeleteForm(Category $category)
	{
		return $this->createFormBuilder()
			->setAction($this->generateUrl('categories_delete', array('id' => $category->getId())))
			->setMethod('DELETE')
			->getForm();
	}
}
