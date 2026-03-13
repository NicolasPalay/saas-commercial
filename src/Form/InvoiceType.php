<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Devis;
use App\Entity\Invoice;
use App\Entity\User;
use App\Form\Field\ClientAutocompleteField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
             ->add('client', ClientAutocompleteField::class, [
        'autocomplete' => true,
        'attr' => ['class' => 'input-devis'],
    ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
        ]);
    }
}
