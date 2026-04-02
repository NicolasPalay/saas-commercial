<?php

namespace App\Form;

use App\Entity\Client;
use BcMath\Number;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('raisonSocial', TextType::class, [
                'label' => "Raison Social",
               'attr' => [
                    'class' => "form-control mb-2",
                    'placeholder' => 'Inscrivez le nom de l\'entreprise',
                    
                ]])
            ->add('nameStreet', TextType::class, [
                'label' => "adresse ",
                'mapped' => false,
               'attr' => [
                    'class' => "form-control mb-2",
                    'placeholder' => 'Inscrivez son adresse',
                    
                ]])
            ->add('nameStreet2', TextType::class, [
                'label' => "adresse suite",
                'mapped' => false,
                'required'=> false,
                 'attr' => [
                    'class' => "form-control mb-2",
                    'placeholder' => 'Inscrivez son adresse suite',
                    
                ]])
            ->add('codePostal', NumberType::class, [
                'label' => "Code postal",
                'mapped' => false,
                 'attr' => [
                    'class' => "form-control mb-2",
                    'placeholder' => 'Inscrivez son code postal',
                    
                ]])
            ->add('ville', TextType::class, [
                'label' => "Ville",
                'mapped' => false,
                'attr' => [
                    'class' => "form-control mb-2",
                    'placeholder' => 'Inscrivez sa ville',
                    
                ]])
           
            ->add('businessPhone', TextType::class, [
                'label' => "Téléphone fixe",
                'mapped' => false,
                'required'=> false,
                 'attr' => [
                    'class' => "form-control mb-2",
                    'placeholder' => 'Inscrivez son numéro de ligne fixe',
                    
                ]])
            ->add('mobilePhone', TextType::class, [
                'label' => "Téléphone mobile",
                'mapped' => false,
                'required'=> false,
                 'attr' => [
                    'class' => "form-control mb-2",
                    'placeholder' => 'Inscrivez son numéro de mobile',
                    
                ]])
            ->add('email', TextType::class, [
                'label' => "Email",
                'mapped' => false,
                'required'=> false,
                'attr' => [
                    'class' => "form-control mb-2",
                    'placeholder' => 'Inscrivez son email',
                    
                ]])
                
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
