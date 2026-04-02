<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Taxe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
           ->add('reference', null, [
                'label' => '🏷️ Référence',
            ])

            ->add('name', null, [
                'label' => '📝 Nom du produit',
            ])

            ->add('price', MoneyType::class, [
                'label' => '💶 Prix (HT)',
                'currency' => false,
                'required' => false,
            ])

            ->add('costPrice', MoneyType::class, [
                'label' => '💰 Prix de revient',
                'currency' => false,
                'required' => false,
            ])

            ->add('stock', null, [
                'label' => '📦 Stock',
            ])

            ->add('barcode', null, [
                'label' => '🔢 Code-barres',
                'required' => false,
            ])
            ->add('category', EntityType::class, [
                 'class' => Category::class,
                'label' => '🧾 Categorie',
                'choice_label' => 'nameCategory',
                'placeholder' => 'Sans',
                'required' => false,
                'expanded'=>true
            ])

            ->add('taxe', EntityType::class, [
                 'class' => Taxe::class,
                'label' => '🧾 Taxe applicable',
                'choice_label' => 'name',
                'placeholder' => 'Sélectionnez une taxe',
                'required' => false,
                'expanded'=>true
            ])
            ->add('image', FileType::class, [
                'mapped' => false,
                'required' => false,
               
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF).',
                    ])
                ],
            ])
            ->add('isActive', null, [
                'label' => 'Produit actif',
                'required' => false,
            ])

            ->add('isService', null, [
                'label' => 'Service',
                'required' => false,
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
