<?php
/**
 * Created by PhpStorm.
 * User: loic
 * Date: 24.08.2017
 * Time: 15:46
 */

namespace AppBundle\DataFixtures\ORM;


use App\UserBundle\Entity\User;
use AppBundle\Entity\Category;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadUserData implements FixtureInterface {




	public function load(ObjectManager $manager){

		function generateRandomString($length = 10) {
			return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
		}

		$listNames = array('jean', 'marine', 'anne');
		foreach ($listNames as $name) {
			$user = new User();
			$user->setUsername($name);
			$user->setSalt(generateRandomString(20));
			$rawPassword = $name;
			// Symfony used rawPassword{salt} to generate password
			$user->setPassword(hash('sha512', $rawPassword.'{'.$user->getSalt().'}'));
			$user->setRoles(array('ROLE_WRITER'));
			$manager->persist($user);
		}

		$user = new User();
		$user->setUsername('admin');
		$user->setSalt(generateRandomString(20));
		$user->setRoles(array('ROLE_ADMIN'));
		$rawPassword = 'admin';
		// Symfony used rawPassword{salt} to generate password
		$user->setPassword(hash('sha512', $rawPassword.'{'.$user->getSalt().'}'));
		$manager->persist($user);


		$manager->flush();

	}

}