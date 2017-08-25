<?php
/**
 * Created by PhpStorm.
 * User: loic
 * Date: 24.08.2017
 * Time: 15:46
 */

namespace AppBundle\DataFixtures\ORM;


use App\UserBundle\Entity\Role;
use App\UserBundle\Entity\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadUserData implements FixtureInterface {




	public function load(ObjectManager $manager){

		function generateRandomString($length = 10) {
			return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
		}

		$roleAdmin = new Role();
		$roleAdmin->setName(array('ROLE_ADMIN'));
		$manager->persist($roleAdmin);

		$roleWriter = new Role();
		$roleWriter->setName(array('ROLE_WRITER'));
		$manager->persist($roleWriter);


		$listNames = array('jean', 'marine', 'anne');
		foreach ($listNames as $name) {
			$user = new User();
			$user->setUsername($name);
			$user->setCreatedAt(new \DateTime());
			$user->setSalt(generateRandomString(20));
			$rawPassword = $name;
			// Symfony used rawPassword{salt} to generate password
			$user->setPassword(hash('sha512', $rawPassword.'{'.$user->getSalt().'}'));
			$user->setRole($roleWriter);
			$manager->persist($user);
		}

		$user = new User();
		$user->setUsername('admin');
		$user->setCreatedAt(new \DateTime());
		$user->setSalt(generateRandomString(20));
		$user->setRole($roleAdmin);
		$rawPassword = 'admin';
		// Symfony used rawPassword{salt} to generate password
		$user->setPassword(hash('sha512', $rawPassword.'{'.$user->getSalt().'}'));
		$manager->persist($user);


		$manager->flush();

	}

}