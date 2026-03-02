<?php

namespace App\Form\Field;

use App\Entity\Client;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class ClientAutocompleteField extends AbstractType
{
        public function __construct(private Security $security)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Client::class,
            'placeholder' => 'Choose a Client',
            'choice_label' => 'raisonSocial',
            'searchable_fields' => ['raisonSocial'],
            'autocomplete' => true,

            'query_builder' => function (EntityRepository $er) {
                $user = $this->security->getUser();

                return $er->createQueryBuilder('c')
                    ->where('c.company = :company')
                    ->setParameter('company', $user->getCompany());
            },


        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
