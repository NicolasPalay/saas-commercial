<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('raison_social')
            ->add('nameStreet', TextType::class, [
                'label' => "Nom de la nouvelle entreprise",
                'mapped' => false,
                'attr' => ['class' => 'input-ocean']])
            ->add('nameStreet2', TextType::class, [
                'label' => "Nom de la nouvelle entreprise suite",
                'mapped' => false,
                'required'=> false,
                'attr' => ['class' => 'input-ocean']])
            ->add('codePostal', TextType::class, [
                'label' => "Code postal",
                'mapped' => false,
                'attr' => ['class' => 'input-ocean']])
            ->add('ville', TextType::class, [
                'label' => "Ville",
                'mapped' => false,
                'attr' => ['class' => 'input-ocean']])
           
            ->add('businessPhone', TextType::class, [
                'label' => "Téléphone fixe",
                'mapped' => false,
                'required'=> false,
                'attr' => ['class' => 'input-ocean']])
            ->add('mobilePhone', TextType::class, [
                'label' => "Téléphone mobile",
                'mapped' => false,
                'required'=> false,
                'attr' => ['class' => 'input-ocean']])
            ->add('email', TextType::class, [
                'label' => "Email",
                'mapped' => false,
                'required'=> false,
                'attr' => ['class' => 'input-ocean']])
                
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
