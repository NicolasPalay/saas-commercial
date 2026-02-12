<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
                    'class' => 'input-ocean',
                    'placeholder' => "Entrez votre email",
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
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
            ->add('name', TextType::class, [
                'label' => "Nom de la nouvelle entreprise",
                'mapped' => false,
                'attr' => [
                    'class' => 'input-ocean',
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
                    'class' => 'input-devis',
                    'placeholder' => "Entrez la référence: 11100001 ou 101 ou d--000001...",
                ],
            ])
             ->add('refFacture',TextType::class, [
                'label' => "Confirmer vos factures",
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'input-devis',
                    'placeholder' => "Entrez la référence: 11100001 ou 101 ou F--000001...",
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
