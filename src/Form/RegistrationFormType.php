<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
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
                'label' => "Email",
                'attr' => [
                    'class' => 'form-control mb-2',
                    'placeholder' => "Entrez votre email",
                ],
            ])
            ->add('firstname', TextType::class, [
                'label' => "Prénom",
                'attr' => [
                    'class' => 'form-control mb-2',
                    'placeholder' => "Entrez votre prénom",
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => "Nom",
                'attr' => [
                    'class' => 'form-control mb-2',
                    'placeholder' => "Entrez votre nom",
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => "Mot de passe",
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password',
                    'class' => 'form-control  mb-2',
                    'placeholder' => "Entrez votre nouveau mot de passe",
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('separator', HiddenType::class, [
                'mapped' => false,
                'attr' => [
                    'class' => 'separator'
                ]
            ])
            ->add('name', TextType::class, [
                'label' => "Nom de la nouvelle entreprise",
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control  mb-2',
                    'placeholder' => "Entrez le nom de votre entreprise",],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a campony\'s name',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your campony\'s name should be at least {{ limit }} characters',
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('refDevis',TextType::class, [
                'label' => "Configurer vos devis",
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control  mb-2',
                    'placeholder' => "Entrez la référence: 11100001 ou 101 ou D-000001...",
                ],
            ])
            ->add('refOrder',TextType::class, [
                'label' => "Configurer vos commandes",
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control  mb-2',
                    'placeholder' => "Entrez la référence: 11100001 ou 101 ou C-000001...",
                ],
            ])
             ->add('refFacture',TextType::class, [
                'label' => "Confirmer vos factures",
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control  mb-2',
                    'placeholder' => "Entrez la référence: 11100001 ou 101 ou F-000001...",
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => "J'accepte les conditions",
                'label_attr' => [
                    'class' => 'mt-2 px-3',
                ],
                'attr' => [
                    'class' => ' mt-3',
                ],
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
