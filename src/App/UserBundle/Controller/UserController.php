<?php

namespace App\UserBundle\Controller;

use App\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
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
		$userform = $this->createForm('App\UserBundle\Form\UserNewType', $user);
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
		// Edit form
		$editForm = $this->createForm('App\UserBundle\Form\UserEditType', $user);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->getDoctrine()->getManager()->flush();
			return $this->redirectToRoute('users_index');
		}

		// Load a special form with delete method etc..
		$deleteForm = $this->createDeleteForm($user);

		return $this->render('@User/user/edit.html.twig', [
			'editForm'   => $editForm->createView(),
			'deleteForm' => $deleteForm->createView(),
			'user'       => $user,
		]);
	}

	/**
	 * Displays a form to edit an existing user's password.
	 *
	 * @Route("/{id}/edit/password", name="users_edit_password")
	 * @Method({"GET", "POST"})
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function editPasswordAction(Request $request, User $user)
	{
		// Mandatory, because when I post the form, $user->getPassword() take form passform field data...
		$userHashPassword = $user->getPassword();

		// Edit form
		$passwordForm = $this->createForm('App\UserBundle\Form\UserPasswordType', $user);
		$passwordForm->handleRequest($request);

		if ($passwordForm->isSubmitted()) {
			/* CUSTOM ERROR HANDLING -> check if the old password field match to the user's password */
			$rawOldPassword = $request->request->get('app_userbundle_user')['oldPassword'];
			$oldPassword = hash('sha512', $rawOldPassword.'{'.$user->getSalt().'}');
			if($oldPassword != $userHashPassword){
				$passwordForm->addError(new FormError('Mot de passe actuel ne correspond pas à votre mot de passe'));
			}

			if($passwordForm->isValid()){
				// generate a 20 length random salt in same method (sha 512) as symfony security encoders I defined
				$user->setSalt($this->generateRandomString(20));

				// Set and hash the password + salt
				$user->setPassword(hash('sha512', $user->getPassword().'{'.$user->getSalt().'}'));

				// Get the entityManager and flush the user object
				$this->getDoctrine()->getManager()->flush();

				return $this->redirectToRoute('users_index');
			}
		}

		return $this->render('@User/user/editPassword.html.twig', [
			'passwordForm'   => $passwordForm->createView(),
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
	 * Displays a form to edit the user connected profile
	 *
	 * @Route("/profile", name="users_profile")
	 * @Method({"GET", "POST"})
	 * @Security("has_role('ROLE_WRITER')")
	 */
	public function profileAction(Request $request)
	{
		// Connected user
		$user = $this->get('security.token_storage')->getToken()->getUser();
		$oldRole = $user->getRole();
		// Edit form
		$profileForm = $this->createForm('App\UserBundle\Form\UserEditType', $user);
		$profileForm->handleRequest($request);

		if ($profileForm->isSubmitted() && $profileForm->isValid()) {
			$this->getDoctrine()->getManager()->flush();
			// If we change the role FROM admin TO an other role as admin -> logout to prevent cache problems
			if($oldRole->getName()[0] == 'ROLE_ADMIN' && $user->getRole()->getName()[0] != 'ROLE_ADMIN'){
				return $this->redirectToRoute('logout');
			}else{
				return $this->redirectToRoute('users_index');
			}
			return $this->redirectToRoute('users_profile');
		}

		// Load a special form with delete method etc..
		$deleteForm = $this->createDeleteForm($user);

		return $this->render('@User/user/profile.html.twig', [
			'profileForm'   => $profileForm->createView(),
			'deleteForm' => $deleteForm->createView(),
			'user'       => $user,
		]);
	}

	/**
	 * Displays a form to edit the profile (user connected) password.
	 *
	 * @Route("/profile/password", name="users_profile_password")
	 * @Method({"GET", "POST"})
	 * @Security("has_role('ROLE_WRITER')")
	 */
	public function profilePasswordAction(Request $request)
	{
		// Connected user
		$user = $this->get('security.token_storage')->getToken()->getUser();
		// Mandatory, because when I post the form, $user->getPassword() take form passform field data...
		$userHashPassword = $user->getPassword();

		// Edit form
		$passwordForm = $this->createForm('App\UserBundle\Form\UserPasswordType', $user);
		$passwordForm->handleRequest($request);

		if ($passwordForm->isSubmitted()) {
			/* CUSTOM ERROR HANDLING -> check if the old password field match to the user's password */
			$rawOldPassword = $request->request->get('app_userbundle_user')['oldPassword'];
			$oldPassword = hash('sha512', $rawOldPassword.'{'.$user->getSalt().'}');
			if($oldPassword != $userHashPassword){
				$passwordForm->addError(new FormError('Mot de passe actuel ne correspond pas à votre mot de passe'));
			}

			if($passwordForm->isValid()){
				// generate a 20 length random salt in same method (sha 512) as symfony security encoders I defined
				$user->setSalt($this->generateRandomString(20));

				// Set and hash the password + salt
				$user->setPassword(hash('sha512', $user->getPassword().'{'.$user->getSalt().'}'));

				// Get the entityManager and flush the user object
				$this->getDoctrine()->getManager()->flush();

				return $this->redirectToRoute('users_profile');
			}
		}

		return $this->render('@User/user/editPassword.html.twig', [
			'passwordForm'   => $passwordForm->createView(),
			'user'       => $user,
		]);
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
