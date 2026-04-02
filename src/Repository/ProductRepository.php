<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    function findAllByCompany($companyId)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.company = :companyId')
            ->setParameter('companyId', $companyId)
            ->getQuery()
            ->getResult();
    }

    public function createQueryBuilderForCompany(?Company $company): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.name', 'ASC');

        if ($company) {
            $qb->andWhere('p.company = :company')
            ->setParameter('company', $company);
        } else {
            $qb->andWhere('1 = 0'); // sécurité : aucun résultat
        }

        return $qb;
    }

    public function findByCompanySorted($company, $field, $direction)
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c') // ⚠️ adapte selon ta relation
            ->addSelect('c')
            ->where('p.company = :company')
            ->setParameter('company', $company);

        if ($field === 'nameCategory') {
            $qb->orderBy('c.nameCategory', $direction);
        } else {
            $qb->orderBy('p.' . $field, $direction);
        }

        return $qb->getQuery()->getResult();
    }
      

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
