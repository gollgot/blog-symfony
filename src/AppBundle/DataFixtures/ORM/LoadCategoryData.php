<?php
/**
 * Created by PhpStorm.
 * User: loic
 * Date: 17.08.2017
 * Time: 15:18
 */

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\Category;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCategoryData implements FixtureInterface {

    public function load(ObjectManager $manager){

        for($i = 0; $i < 4; $i++){
            $category = new Category();
            $name = "Catégorie ".($i+1);
            $category->setName($name);
            $manager->persist($category);
        }

        $manager->flush();
    }

}