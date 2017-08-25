<?php

namespace App\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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

}
