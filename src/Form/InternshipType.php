<?php

namespace App\Form;

use App\Entity\ActivityList;
use App\Entity\Company;
use App\Entity\Internship;
use App\Entity\School;
use App\Entity\User;
use App\Entity\VisitReport;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InternshipType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('start_date', null, [
                'widget' => 'single_text',
            ])
            ->add('end_date', null, [
                'widget' => 'single_text',
            ])
            ->add('is_verified')
            ->add('intern', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('school', EntityType::class, [
                'class' => School::class,
                'choice_label' => 'id',
            ])
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'choice_label' => 'id',
            ])
            ->add('visitreport', EntityType::class, [
                'class' => VisitReport::class,
                'choice_label' => 'id',
            ])
            ->add('activitylist', EntityType::class, [
                'class' => ActivityList::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Internship::class,
        ]);
    }
}
