<?php

namespace ApiBundle\Controller\v1;

use ApiBundle\Helpers\apiHelpers;
use AppBundle\Entity\Category;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
	/**
	 * @Route("/categories/", name="api_v1_get_categories")
	 * @Method("GET")
	 */
	public function getCategoriesAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$categories = $em->getRepository('AppBundle:Category')->findAll();

		// Get the "Accept" param in the http headers request from the client
		switch($request->headers->get('accept')){
			// user want a XML response
			case 'application/xml':
				// The Categories doesn't exists => create response with error message and the status code 404
				if (empty($categories)) {
					return apiHelpers::displayError('xml', 404, 'Categories not found', new Response('',Response::HTTP_NOT_FOUND));
				}
				// The categories exists
				else{
					// Header
					$xml = '<?xml version="1.0" encoding="UTF-8"?>';
					$xml .= '<categories>';
					foreach ($categories as $category) {
						$xml .= '<categorie><id>'.$category->getId().'</id><name>'.$category->getName().'</name><posts-number>'.sizeof($category->getPosts()).'</posts-number></categorie>';
					}
					$xml .= '</categories>';
					$response = new Response($xml, Response::HTTP_OK);
					// Set the header "Content-Type" to the http response
					$response->headers->set('Content-Type', 'application/xml');
					return $response;
				}
				break;
			// Default response in JSON
			default:
				// The Categories doesn't exists => create response with error message and the status code 404
				if (empty($categories)) {
					return apiHelpers::displayError('json', 404, 'Categories not found', new JsonResponse('', Response::HTTP_OK));
				}
				// The post exists
				else {
					foreach ($categories as $category) {
						// All formated datas
						$json[] = [
							'id'           => $category->getId(),
							'name'         => $category->getName(),
							'posts-number' => sizeof($category->getPosts()),
						];
					}
					$response = new JsonResponse($json, Response::HTTP_OK);
					// Set the header "Content-Type" to the http response
					$response->headers->set('Content-Type', 'application/json');
					return $response;
				}
				break;
		}
	}

	/**
	 * Creates a new category entity.
	 *
	 * @Route("/categories/", name="api_v1_post_category")
	 * @Method({"POST"})
	 */
	public function postCategoryAction(Request $request)
	{
		$name = $request->get('name');
		// Error name is empty
		if(empty($name)){
			// Get the "Accept" param in the http headers request from the client
			switch($request->headers->get('accept')){
				// user want a XML response
				case 'application/xml':
					$xml = '<?xml version="1.0" encoding="UTF-8"?>';
					$xml .= '<errors><error><type>blank</type><attribut>name</attribut><message>name cannot be blank</message></error></errors>';
					$response = new Response($xml, Response::HTTP_BAD_REQUEST);
					// Set the header "Content-Type" to the http response
					$response->headers->set('Content-Type', 'application/xml');
					break;
				// Default response in JSON
				default:
					$json['errors'][] = [
						'type'     => 'blank',
						'attribut' => 'name',
						'message'  => 'name cannot be blank',
					];
					$response = new JsonResponse($json, Response::HTTP_BAD_REQUEST);
					// Set the header "Content-Type" to the http response
					$response->headers->set('Content-Type', 'application/json');
					break;
			}
		}
		// Success, no errors
		else{
			$category = new Category();
			$category->setName($name);
			$em = $this->getDoctrine()->getManager();
			$em->persist($category);
			$em->flush();
			// Get the "Accept" param in the http headers request from the client
			switch($request->headers->get('accept')){
				// user want a XML response
				case 'application/xml':
					$xml = '<?xml version="1.0" encoding="UTF-8"?>';
					$xml .= '<success><code>201</code><message>New category has been created</message></success>';
					$response = new Response($xml, Response::HTTP_CREATED);
					// Set the header "Content-Type" to the http response
					$response->headers->set('Content-Type', 'application/xml');
					break;
				// Default response in JSON
				default:
					$json = [
						'success' => [
							'code'    => 201,
							'message' => 'New category has been created',
						],
					];
					$response = new JsonResponse($json, Response::HTTP_CREATED);
					// Set the header "Content-Type" to the http response
					$response->headers->set('Content-Type', 'application/json');
					break;
			}
		}

		return $response;
	}

}
