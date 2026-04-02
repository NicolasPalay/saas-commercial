<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function getAllClientsByCompany($company) : array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.company = :company')
            ->setParameter('company', $company)
            ->orderBy('c.raisonSocial', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByCompanySortedByAddress($company, $field = 'raison_social', $direction = 'asc'): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.address', 'a')
            ->addSelect('a')
            ->where('c.company = :company')
            ->setParameter('company', $company);

        $addressFields = ['codePostal', 'ville', 'mobilePhone'];

        if (in_array($field, $addressFields)) {
            $qb->orderBy('a.' . $field, $direction);
        } else {
            $qb->orderBy('c.' . $field, $direction);
        }

        return $qb->getQuery()
            ->setHint(\Doctrine\ORM\Query::HINT_REFRESH, true)
            ->getResult();
    }
}
