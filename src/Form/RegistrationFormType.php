<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Grade;
use App\Entity\Speciality;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Enter your email'
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => true,
                'first_options' => [
                    'label' => 'Password',
                    'attr' => ['autocomplete' => 'new-password']
                ],
                'second_options' => [
                    'label' => 'Repeat Password',
                    'attr' => ['autocomplete' => 'new-password']
                ],
                'invalid_message' => 'The password fields must match.',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('firstname', TextType::class, [
                'required' => true,
                'label' => 'First Name',
                'attr' => [
                    'placeholder' => 'Enter your first name'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your first name'
                    ])
                ]
            ])
            ->add('lastname', TextType::class, [
                'required' => true,
                'label' => 'Last Name',
                'attr' => [
                    'placeholder' => 'Enter your last name'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your last name'
                    ])
                ]
            ])
            ->add('grade', EntityType::class, [
                'class' => Grade::class,
                'choice_label' => 'name',
                'required' => true,
                'label' => 'Grade'
            ])
            ->add('speciality', EntityType::class, [
                'class' => Speciality::class,
                'choice_label' => 'name',
                'required' => true,
                'label' => 'Speciality'
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
                'label' => 'Agree to terms'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
