<?php

namespace App\Form;

use App\Entity\Internship;
use App\Entity\VisitReport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use EmilePerron\TinymceBundle\Form\Type\TinymceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VisitReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add(child: 'title')
        ->add('contained', TinymceType::class, [])
            ->add('is_verified')
            ->add('internship', EntityType::class, [
                'class' => Internship::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VisitReport::class,
        ]);
    }
}
