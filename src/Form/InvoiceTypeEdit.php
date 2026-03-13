<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Devis;
use App\Entity\Invoice;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceTypeEdit extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('raisonSocial')
            ->add('address')
            ->add('codePostal')
            ->add('ville')
            ->add('priceTotalHt')
            ->add('taxeTotal')
            ->add('priceTotalTTC')
            ->add('isPay')
            ->add('devis', EntityType::class, [
                'class' => Devis::class,
                'choice_label' => 'id',
            ])
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'choice_label' => 'id',
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
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
