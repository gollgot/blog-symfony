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

	private function generateRandomString($length = 10) {
		return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
	}

}
