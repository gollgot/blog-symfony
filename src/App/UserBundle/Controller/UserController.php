<?php

namespace App\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


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

}
