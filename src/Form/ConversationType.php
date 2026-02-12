<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Conversation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ConversationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $company = $options['company'];
        $currentUser = $options['current_user'] ?? null;

        if (!$company) {
            return;
        }
        $builder
          ->add('users', EntityType::class, [
            'class' => User::class,
            'choice_label' => 'email', // ou fullname
            'multiple' => true,
            'expanded' => true,
            'query_builder' => function (EntityRepository $er) use ($company, $currentUser) {
                return $er->createQueryBuilder('u')
                    ->innerJoin('u.company', 'c')
                     ->where('u.company = :company')
                    ->andWhere('u != :currentUser')
                    ->setParameter('company', $company)
                    ->setParameter('currentUser', $currentUser)
                    ->orderBy('u.email', 'ASC');
            },  
        ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conversation::class,
            'company' => null,
            'current_user' => null,
        ]);
    }
}
