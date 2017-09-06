<?php

namespace App\UserBundle\Form;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class UserNewType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('username', TextType::class, [
				'label' => 'Utilisateur'
			])
			->add('password', RepeatedType::class, [
				'type' => PasswordType::class,
				'invalid_message' => 'Les mot de passes doivent correspondre',
				'options' => array('attr' => array('class' => 'password-field')),
				'required' => true,
				'first_options'  => array('label' => 'Mot de passe'),
				'second_options' => array('label' => 'Répétez le mot de passe'),
			])
			->add('role', EntityType::class, [
				'class'        => 'App\UserBundle\Entity\Role',
				'choice_label' => 'name[0]', // Role attribut, form will call role->getName() to display it
				'label'        => 'Rôle',
				'expanded'     => false,
				'multiple'     => false,
			]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserBundle\Entity\User'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_userbundle_user';
    }


}
