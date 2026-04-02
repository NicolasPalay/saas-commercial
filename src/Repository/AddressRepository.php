<?php

namespace App\Repository;

use App\Entity\Address;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Address>
 */
class AddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }


    public function findDefaultAddressesByCompany($company)
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.client', 'c')
            ->addSelect('c')
            ->where('c.company = :company')
            ->andWhere('a.isDefault = true')
            ->setParameter('company', $company)
            ->orderBy('a.codePostal', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByClientSortedByAddress($company, $client, $field = 'nameStreet', $direction = 'asc'): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.client', 'c')
            ->addSelect('c')
            ->where('c.company = :company')
            ->setParameter('company', $company)
            ->andWhere('a.client = :client')
            ->setParameter('client', $client);

        $addressFields = ['codePostal', 'ville', 'mobilePhone'];

        if (in_array($field, $addressFields)) {
            $qb->orderBy('a.' . $field, $direction);
        }    
        return $qb->getQuery()
            ->setHint(\Doctrine\ORM\Query::HINT_REFRESH, true)
            ->getResult();
    }

    //    /**
    //     * @return Address[] Returns an array of Address objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Address
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
