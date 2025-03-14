<?php

namespace App\Form;

use App\Entity\ActivityList;
use App\Entity\Internship;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ActivityListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


        $builder
            ->add('contained', TextareaType::class, [
                'attr' => ['class' => 'tinymce'],
            ])
            ->add('is_verified')
            ->add('internship', EntityType::class, [
                'class' => Internship::class,
                'choice_label' => 'id',
            ]);

    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ActivityList::class,
        ]);
    }
}
