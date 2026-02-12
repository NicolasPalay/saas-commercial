<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Company;
use App\Entity\Devis;
use App\Entity\User;
use App\Form\Field\ClientAutocompleteField; 
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DevisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference',TextType::class, [
                'label' => "Référence du devis",
                'attr' => ['class' => 'input-devis']])
            ->add('client', ClientAutocompleteField::class, [
        'autocomplete' => true,
        'attr' => ['class' => 'input-devis'],
    ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Devis::class,
        ]);
    }
}
