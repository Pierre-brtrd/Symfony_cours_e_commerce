<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom:',
                'required' => false,
                'attr' => [
                    'placeholder' =>  'John',
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom:',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Doe',
                ]
            ])
            ->add('email', EmailType::class, [
                'label' =>  'Email:',
                'required' => false,
                'attr' => [
                    'placeholder' => 'john@example.com'
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'mapped' => false,
                'invalid_message' =>  "Les mot de passe ne correspondent pas",
                'first_options' => [
                    'label' => "Mot de passe:",
                    'attr' => [
                        'placeholder' => "S3CR3T",
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Length([
                            'max' => 4096
                        ]),
                        new Assert\Regex(
                            pattern: '/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^\w\d\s:])([^\s]){8,16}$/',
                            message: 'Le mot de passe doit contenir au minimum 1 lettre majuscule, minuscule, 1 chiffre et caractère spéciale'
                        )
                    ],
                    'help' => "Le mot de passe doit contenir au minimum 1 lettre majuscule, minuscule, 1 chiffre et caractère spéciale"
                ],
                'second_options' => [
                    'label' => "Confirmation mot de passe:",
                    'attr' => [
                        'placeholder' => "S3CR3T",
                    ],
                ]
            ]);

        if ($options['isAdmin']) {
            $builder->remove('password')
                ->add('enable', CheckboxType::class, [
                    'label' => 'Activé',
                    'required' => false,
                ])
                ->add('roles', ChoiceType::class, [
                    'label' => 'Roles:',
                    'placeholder' => 'Sélectionner un role',
                    'choices' => [
                        'Utilisateur' => 'ROLE_USER',
                        'Administrateur' => 'ROLE_ADMIN',
                    ],
                    'expanded' => true,
                    'multiple' => true,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'isAdmin' => false,
            'sanitize_html' => true,
        ]);
    }
}
