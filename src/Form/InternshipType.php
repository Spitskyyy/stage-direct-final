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
                'label' => 'Date de début'
            ])
            ->add('end_date', null, [
                'widget' => 'single_text',
                'label' => 'Date de fin'
            ]);

        // Ajouter le champ intern seulement si ce n'est pas un nouveau stage
        if (!$options['is_new']) {
            $builder->add('intern', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'disabled' => true
            ]);
        }

        $builder
            ->add('school', EntityType::class, [
                'class' => School::class,
                'choice_label' => 'name',
            ])
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'choice_label' => 'name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Internship::class,
            'is_new' => false,  // Option par défaut
        ]);
    }
}
