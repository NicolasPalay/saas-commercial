<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Devis;
use App\Enum\StatusEnum;
use App\Form\Field\ClientAutocompleteField;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class DevisTypeEdit extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
         $company = $options['company'];
        $currentClient = $options['currentClient'] ?? null;

        if (!$company) {
            return;
        }

        $builder
            ->add('reference', TextType::class, [
                'disabled' => true, 
                'label' => 'Référence du devis',
                'label_attr' => ['class' => 'd-block mb-1'],
                'attr' => ['class' => 'form-control mb-3']])
            ->add('status', EnumType::class, [
                'class' => StatusEnum::class,
                'placeholder' => 'Choisir un statut',
                'required' => false,
                'choice_label' => fn(StatusEnum $status) => $status->label(),
                'label' => 'Statut du devis',
                'label_attr' => [
                    'class' => 'd-block mb-1',

                ],
                'attr' => [
                    'class' => 'form-control mb-3',

                ]
            ])
            ->add('deliveryLabel', TextType::class, [    
                'label' => 'Libellé de facturation',
               
                'label_attr' => ['class' => 'd-block mb-1'],
                'required' => false,
                'attr' => ['class' => 'form-control mb-3']])
            ->add('address', EntityType::class, [
            'class' => Address::class,
            'choice_label' => 'nameStreet',  
            'placeholder' => 'Choisir une adresse existante',
            'required' => false,
            'mapped' => false,
            'query_builder' => function (EntityRepository $er) use ($company, $currentClient) {
                return $er->createQueryBuilder('c')
                    ->innerJoin('c.company', 'cc')
                     ->where('c.company = :company')
                    ->andWhere('c != :currentClient')
                    ->setParameter('company', $company)
                    ->setParameter('currentClient',  $currentClient)
                    ->orderBy('c.id', 'ASC');
            },  
        ])
            ->add('deliveryStreet', TextType::class, [
                'label' => 'Adresse de facturation',
                'label_attr' => ['class' => 'd-block mb-1'],
                'required' => false,
                'attr' => ['class' => 'form-control mb-3']])
            ->add('deliveryStreet2', TextType::class, [
               'label' => 'Adresse de facturation (complément)',
                'label_attr' => ['class' => 'd-block mb-1'],
                'required' => false,
                'attr' => ['class' => 'form-control mb-3']])
            ->add('deliveryPostalCode', NumberType::class, [
                'label' => 'Code Postal de facturation',
                'label_attr' => ['class' => 'd-block mb-1'],
                'required' => false,
                'attr' => ['class' => 'form-control postalCode mb-3']])
            ->add('deliveryCity', TextType::class, [
                'label' => 'Ville de facturation',
                'label_attr' => ['class' => 'd-block mb-1'],
                'required' => false,
                'attr' => ['class' => 'form-control ville mb-3']])
            ->add('deliveryPhone', TelType::class, [
                'label' => 'Téléphone de facturation',
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
            'company' => null,
            'currentClient' => null,
        ]);
    }
}
