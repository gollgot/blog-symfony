<?php

namespace App\UserBundle\Controller;

use App\UserBundle\Entity\Role;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/roles")
 */
class RoleController extends Controller
{
	/**
	 * Lists all role entities.
	 *
	 * @Route("/", name="roles_index")
	 * @Method("GET")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();
		$roles = $em->getRepository('UserBundle:Role')->findBy(array(), array("name" => "ASC"));

		return $this->render('UserBundle:role:index.html.twig', [
			'roles' => $roles,
		]);
	}

	/**
	 * Creates a new role entity.
	 *
	 * @Route("/new", name="roles_new")
	 * @Method({"GET", "POST"})
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function newAction(Request $request)
	{
		$role = new Role();
		$roleForm = $this->createForm('App\UserBundle\Form\RoleNewType', $role);
		$roleForm->handleRequest($request);

		// If the form has been submitted, and data is valid, create the new user and redirect
		if ($roleForm->isSubmitted() && $roleForm->isValid()) {
			$em = $this->getDoctrine()->getManager();

			// I have to stock the role in an array, symfony want that
			$role->setName(array($role->getName()));

			$em->persist($role);
			$em->flush();
			// Success flash message
			$request->getSession()->getFlashBag()->add('success', 'Votre nouveau rôle à bien été créé');
			return $this->redirectToRoute('roles_index');
		}

		// If no Submitted form (just want to access to the form) or form error validation, display the view with form
		return $this->render('UserBundle:role:new.html.twig', array(
			'role'     => $role,
			'roleForm' => $roleForm->createView(),
		));
	}

	/**
	 * Displays a form to edit an existing role entity.
	 *
	 * @Route("/{id}/edit", name="roles_edit")
	 * @Method({"GET", "POST"})
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function editAction(Request $request, Role $role)
	{
		// Load a special form with delete method etc..
		$deleteForm = $this->createDeleteForm($role);

		// Edit form
		$editForm = $this->createForm('App\UserBundle\Form\RoleEditType', $role);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {

			// I have to stock the role in an array, symfony want that. but in the RoleEditType form this is a CollectionType, so it's ok

			// Get the entityManager and flush the user object
			$this->getDoctrine()->getManager()->flush();
			// Success flash message
			$request->getSession()->getFlashBag()->add('success', 'Le rôle à bien été bien mis à jour');
			return $this->redirectToRoute('roles_index');
		}

		return $this->render('@User/role/edit.html.twig', [
			'editForm'   => $editForm->createView(),
			'deleteForm' => $deleteForm->createView(),
			'role'       => $role,
		]);
	}

	/**
	 * Deletes a role entity.
	 *
	 * @Route("/{id}", name="roles_delete")
	 * @Method("DELETE")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function deleteAction(Request $request, Role $role)
	{
		$deleteForm = $this->createDeleteForm($role);
		$deleteForm->handleRequest($request);

		if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->remove($role);
			$em->flush();
		}

		return $this->redirectToRoute('roles_index');
	}


	/**
	 * Creates a form to delete a role entity.
	 *
	 * @param Role $role The role entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createDeleteForm(Role $role)
	{
		return $this->createFormBuilder()
			->setAction($this->generateUrl('roles_delete', array('id' => $role->getId())))
			->setMethod('DELETE')
			->getForm();
	}

}
