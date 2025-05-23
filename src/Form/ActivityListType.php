<?php

namespace App\Form;

use App\Entity\ActivityList;
use App\Entity\Internship;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use EmilePerron\TinymceBundle\Form\Type\TinymceType;

class ActivityListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('title')
            ->add('contained', TinymceType::class, [])
            ->add('is_verified')
            ->add('internship', EntityType::class, [
                'class' => Internship::class,
                'choice_label' => 'title',
            ]);

    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ActivityList::class,
        ]);
    }
}
