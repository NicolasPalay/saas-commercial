<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Company;
use App\Entity\Devis;
use App\Entity\Invoice;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceTypeEdit extends AbstractType
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
                'label' => 'Référence de facturation',
                'label_attr' => ['class' => 'd-block mb-1'],
                'attr' => ['class' => 'form-control mb-3']])
            ->add('raisonSocial', TextType::class, [    
                'label' => 'Libellé de facturation',
                'label_attr' => ['class' => 'd-block mb-1'],
                'required' => false,
                'attr' => ['class' => 'form-control mb-3']])
            ->add('address', EntityType::class, [
                'class' => Address::class,
                'choice_label' => 'nameStreet', // ou fullname
                'multiple' => false,
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
            ->add('nameStreet', TextType::class, [
                'label' => 'Adresse de facturation',
                'label_attr' => ['class' => 'd-block mb-1'],
                'required' => false,
                'attr' => ['class' => 'form-control mb-3']])
            ->add('nameStreet2', TextType::class, [
                'label' => 'Adresse de facturation (complément)',
                'label_attr' => ['class' => 'd-block mb-1'],
                'required' => false,
                'attr' => ['class' => 'form-control mb-3']])
            ->add('codePostal', NumberType::class, [
                'label' => 'Code Postal de facturation',
                'label_attr' => ['class' => 'd-block mb-1'],
                'required' => false,
                'attr' => ['class' => 'form-control postalCode mb-3']])
            ->add('ville', TextType::class, [
                'label' => 'Ville de facturation',
                'label_attr' => ['class' => 'd-block mb-1'],
                'required' => false,
                'attr' => ['class' => 'form-control ville mb-3']])
            ->add('email', TelType::class, [
                'label' => 'email',
                'label_attr' => ['class' => 'd-block mb-1'],
                'required' => false,
                'attr' => ['class' => 'form-control mb-3']])
            ->add('isPay', null, [
                'disabled' => true, 
                'label' => 'Payé ?',
                'required' => false,
                'attr' => ['class' => 'mx-3 mb-3']])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
            'company' => null,
            'currentClient' => null,
        ]);
    }
}
