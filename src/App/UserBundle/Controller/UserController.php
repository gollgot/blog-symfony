<?php

namespace App\UserBundle\Controller;

use App\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;


/**
 * User controller
 *
 * @Route("/users")
 */
class UserController extends Controller
{
	/**
	 * Lists all user entities.
	 *
	 * @Route("/", name="users_index")
	 * @Method("GET")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
    public function indexAction()
    {
    	$em = $this->getDoctrine()->getManager();
    	$users = $em->getRepository('UserBundle:User')->findBy(array(), array("username" => "ASC"));

        return $this->render('UserBundle:user:index.html.twig',[
			'users' => $users,
		]);
    }

	/**
	 * Creates a new user entity.
	 *
	 * @Route("/new", name="users_new")
	 * @Method({"GET", "POST"})
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function newAction(Request $request)
	{
		$user = new User();
		$userform = $this->createForm('App\UserBundle\Form\UserType', $user);
		$userform->handleRequest($request);

		// If the form has been subitted, and data is valid, create the new user and redirect
		if ($userform->isSubmitted() && $userform->isValid()) {
			$em = $this->getDoctrine()->getManager();

			$user->setCreatedAt(new \DateTime());
			// generate a 20 length random salt in same method (sha 512) as symfony security encoders I defined
			$user->setSalt($this->generateRandomString(20));
			// Set and hash the password + salt
			$user->setPassword(hash('sha512', $user->getPassword().'{'.$user->getSalt().'}'));

			$em->persist($user);
			$em->flush();

			return $this->redirectToRoute('users_index');
		}

		// If no Submitted form (just want to access to the form) or form error validation, display the view with form
		return $this->render('UserBundle:user:new.html.twig', array(
			'user' => $user,
			'userForm' => $userform->createView(),
		));
	}

	/**
	 * Displays a form to edit an existing user entity.
	 *
	 * @Route("/{id}/edit", name="users_edit")
	 * @Method({"GET", "POST"})
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function editAction(Request $request, User $user)
	{
		// Load a special form with delete method etc..
		$deleteForm = $this->createDeleteForm($user);

		// Edit form
		$editForm = $this->createForm('App\UserBundle\Form\UserType', $user);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {

			// generate a 20 length random salt in same method (sha 512) as symfony security encoders I defined
			$user->setSalt($this->generateRandomString(20));
			// Set and hash the password + salt
			$user->setPassword(hash('sha512', $user->getPassword().'{'.$user->getSalt().'}'));

			// Get the entityManager and flush the user object
			$this->getDoctrine()->getManager()->flush();

			return $this->redirectToRoute('users_index');
		}

		return $this->render('@User/user/edit.html.twig', [
			'editForm'   => $editForm->createView(),
			'deleteForm' => $deleteForm->createView(),
			'user'       => $user,
		]);
	}

	/**
	 * Deletes a user entity.
	 *
	 * @Route("/{id}", name="users_delete")
	 * @Method("DELETE")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function deleteAction(Request $request, User $user)
	{
		$deleteForm = $this->createDeleteForm($user);
		$deleteForm->handleRequest($request);

		if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->remove($user);
			$em->flush();
		}

		return $this->redirectToRoute('users_index');
	}




	/**
	 * Creates a form to delete a post entity.
	 *
	 * @param User $user The post entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createDeleteForm(User $user)
	{
		return $this->createFormBuilder()
			->setAction($this->generateUrl('users_delete', array('id' => $user->getId())))
			->setMethod('DELETE')
			->getForm();
	}

	/**
	 * Return a generate String
	 *
	 * @param int $length
	 * @return bool|string
	 */
	private function generateRandomString($length = 10) {
		return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
	}

}
