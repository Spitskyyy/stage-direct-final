<?php

namespace App\Form;

use App\Entity\Grade;
use App\Entity\speciality;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            // ->add('roles')
            ->add('firstname')
            ->add(child: 'lastname')
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Étudiant' => 'ROLE_STUDENT', 
                    'Professeur' => 'ROLE_TEACHER',
                    'Modérateur' => 'ROLE_MODERATOR',
                    'Admin' => 'ROLE_ADMIN'
                ],
                'multiple' => true,
                'expanded' => true, // Affiche sous forme de cases à cocher
                'label' => 'Rôles',
            ])            
            ->add('grade', EntityType::class, [
                'class' => Grade::class,
                'choice_label' => 'name',
            ])
            ->add('speciality', EntityType::class, [
                'class' => Speciality::class,
                'choice_label' => 'name',
            ])
            ->add('isVerified');

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
