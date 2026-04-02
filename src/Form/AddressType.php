<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Client;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nameStreet', TextType::class, [
                'label' => "Adresse",
                'required' => false,])
            ->add('nameStreet2', TextType::class, [
                'label' => "Adresse suite",
                'required' => false,])
            ->add('codePostal', TextType::class, [
                'label' => "Code postal",
                'required' => false,])
            ->add('ville', TextType::class, [
                'label' => "Ville",
                'required' => false,])
            ->add('businessPhone', TextType::class, [
                'label' => "Téléphone fixe",
                'required' => false,])
            ->add('mobilePhone', TextType::class, [
                'label' => "Téléphone portable de contact",
                'required' => false,])
            ->add('email', TextType::class, [
                'label' => "Email de contact",
                'required' => false,])
            ->add('isDefault', CheckboxType::class, [
                'label' => "Adresse principale",
                'required' => false,
            ])
            ->add('isDelivery', CheckboxType::class, [
                'label' => "Adresse de livraison",
                'required' => false,
            ])
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
