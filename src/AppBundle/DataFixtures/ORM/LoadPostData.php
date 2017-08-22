<?php
/**
 * Created by PhpStorm.
 * User: loic
 * Date: 17.08.2017
 * Time: 15:18
 */

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\post;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadPostData implements FixtureInterface {

    public function load(ObjectManager $manager){

        for($i = 0; $i < 4; $i++){
            $post = new Post();
            $title = "Titre ".($i+1);
            $content = "Content ".($i+1);
            $post->setTitle($title);
            $post->setContent($content);
            $post->setCreatedAt(new \DateTime());
            $manager->persist($post);
        }

        $manager->flush();
    }

}