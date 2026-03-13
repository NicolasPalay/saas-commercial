<?php

namespace App\Form;

use App\Entity\Devis;
use App\Form\Field\ClientAutocompleteField; 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DevisTypeEdit extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', TextType::class, [
                'disabled' => true, 
                'label' => 'Référence du devis',
                'label_attr' => ['class' => 'd-block mb-1'],
                'attr' => ['class' => 'form-control mb-3']])
            ->add('deliveryLabel', TextType::class, [    
                'label' => 'Libellé de livraison',
                'label_attr' => ['class' => 'd-block mb-1'],
                'required' => false,
                'attr' => ['class' => 'form-control mb-3']])
            ->add('deliveryStreet', TextType::class, [
                'label' => 'Adresse de livraison',
                'label_attr' => ['class' => 'd-block mb-1'],
                'required' => false,
                'attr' => ['class' => 'form-control mb-3']])
            ->add('deliveryPostalCode', NumberType::class, [
                'label' => 'Code Postal de livraison',
                'label_attr' => ['class' => 'd-block mb-1'],
                'required' => false,
                'attr' => ['class' => 'form-control postalCode mb-3']])
            ->add('deliveryCity', TextType::class, [
                'label' => 'Ville de livraison',
                'label_attr' => ['class' => 'd-block mb-1'],
                'required' => false,
                'attr' => ['class' => 'form-control ville mb-3']])
            ->add('deliveryPhone', NumberType::class, [
                'label' => 'Téléphone de livraison',
                'label_attr' => ['class' => 'd-block mb-1'],
                'required' => false,
                'attr' => ['class' => 'form-control mb-3']])
            ->add('isInvoiced', null, [
                 'disabled' => true, 
                'label' => 'Facturé',
                'required' => false,
                'attr' => ['class' => 'mx-3 mb-3']])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Devis::class,
        ]);
    }
}
