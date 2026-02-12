<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Client;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nameStreet', TextType::class, [
                'label' => "Nom de la nouvelle entreprise",
                'mapped' => false,])
            ->add('nameStreet2', TextType::class, [
                'label' => "Nom de la nouvelle entreprise suite",
                'mapped' => false,])
            ->add('codePostal', TextType::class, [
                'label' => "Nom de la nouvelle entreprise",
                'mapped' => false,])
            ->add('businessPhone', TextType::class, [
                'label' => "Téléphone fixe",
                'mapped' => false,])
            ->add('mobilePhone', TextType::class, [
                'label' => "Téléphone mobile",
                'mapped' => false,])
            ->add('email', TextType::class, [
                'label' => "Email",
                'mapped' => false,])
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
