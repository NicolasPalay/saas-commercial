<?php

namespace App\Form\Field;

use App\Entity\Product;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class ProductAutocompleteField extends AbstractType
{
    public function __construct(private Security $security)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Product::class,
            'choice_label' => 'name',
            'label' => false,
            'placeholder' => 'Ajouter un produit',

            'query_builder' => function (EntityRepository $er) {

                $user = $this->security->getUser();

                return $er->createQueryBuilder('p')
                    ->where('p.company = :company')
                    ->setParameter('company', $user->getCompany());
            },
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}