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

		return $this->render('UserBundle:role:index.html.twig',[
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
		$roleForm = $this->createForm('App\UserBundle\Form\RoleType', $role);
		$roleForm->handleRequest($request);

		// If the form has been submitted, and data is valid, create the new user and redirect
		if ($roleForm->isSubmitted() && $roleForm->isValid()) {
			$em = $this->getDoctrine()->getManager();

			// I have to stock the role in an array, symfony want that
			$role->setName(array($role->getName()));

			$em->persist($role);
			$em->flush();

			return $this->redirectToRoute('roles_index');
		}

		// If no Submitted form (just want to access to the form) or form error validation, display the view with form
		return $this->render('UserBundle:role:new.html.twig', array(
			'role' => $role,
			'roleForm' => $roleForm->createView(),
		));
	}

}
