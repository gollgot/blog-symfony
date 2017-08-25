<?php

namespace App\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleEditType extends AbstractType
{
    /**
	 * I have to do a special Edit Form because for EDIT, we have to load a collection, because in the entity
	 * the "name" attribut is an array
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('name', CollectionType::class, [
				// each entry in the array will be an "text type" field
				'entry_type'   => TextType::class,
				'label' => 'Nom',
				// these options are passed to each "email" type
				'entry_options'  => array(
					'label'      => false
				),
			]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserBundle\Entity\Role'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_userbundle_role';
    }


}
