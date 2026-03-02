<?php

namespace App\Form;

use App\Entity\Product;
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
            ->add('reference')
            ->add('name')
            ->add('price', MoneyType::class, [
                'currency' => false,
                'required' => false,
            ])
            ->add('stock')
            ->add('image', FileType::class, [
                'label' => 'Image (image, etc.)',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'accept' => 'application/pdf,image/*', 
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF).',  ])
                ],
            ])
            ->add('isActive')
            ->add('taxe', null, [
                'choice_label' => 'name', // Affiche le nom de la taxe dans le select
                'placeholder' => 'Sélectionnez une taxe',
                'required' => false,])
            ->add('isService')
            ->add('costPrice', MoneyType::class, [
                'currency' => false,
                'required' => false,
            ])
            ->add('barcode')
            // ->add('createdAt') --- IGNORE ---





        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
