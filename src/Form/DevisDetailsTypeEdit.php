<?php

namespace App\Form;

use App\Entity\Devis;
use App\Entity\DevisDetails;
use App\Entity\Product;
use App\Entity\Taxe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DevisDetailsTypeEdit extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
         
         $builder
            ->add('label', TextType::class, [
                'label' => 'Libellé',
                'attr' => [
                    'class' => "form-control mb-2",
                    'placeholder' => 'Libellé',
                    
                ],
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Quantité',
                'attr' => [
                    'class' => "form-control mb-2",
                    'placeholder' => 'Quantité',
                    'min' => 1,
                ],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix Unitaire',
                'attr' => [
                    'class' => "form-control mb-2",
                    'placeholder' => 'Prix',
                    'step' => '0.01',
                ],
            ])
            ->add('reduce', NumberType::class, [
                'label' => 'Remise (%)',
                'required' => false,
                'attr' => [
                    'class' => "form-control",
                    'placeholder' => 'Remise (%)',
                    'min' => 0,
                ],
            ])
        ;
    
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DevisDetails::class,
        ]);
    }
}
