<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Trouver par reference et UUID de la company
     */
    public function findOneByReferenceAndCompanyUuid(string $reference, string $uuid): ?Order
    {
        return $this->createQueryBuilder('o')
            ->join('o.company', 'c')
            ->where('o.reference = :reference')
            ->andWhere('c.uuid = :uuid')
            ->setParameter('reference', $reference)
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Compter les commandes créées ce mois
     */
    public function findCountThisMonth($company): int
    {
        $now = new \DateTime();
        $firstDayOfMonth = new \DateTime($now->format('Y-m-01'));
        $lastDayOfMonth = new \DateTime($now->format('Y-m-t'));

        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.company = :company')
            ->andWhere('o.createdAt >= :start')
            ->andWhere('o.createdAt <= :end')
            ->setParameter('company', $company)
            ->setParameter('start', $firstDayOfMonth)
            ->setParameter('end', $lastDayOfMonth)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouver les commandes récentes
     */
    public function findRecentByCompany($company, int $limit = 5): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.company = :company')
            ->setParameter('company', $company)
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compter toutes les commandes par entreprise
     */
    public function countByCompany($company): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouver par reference et company (pour les routes UUID)
     */
    public function findByReferenceAndCompany(string $reference, $company): ?Order
    {
        return $this->findOneBy([
            'reference' => $reference,
            'company' => $company
        ]);
    }

    /**
     * Trouver le revenu mensuel des commandes
     */
    public function findMonthlyRevenue($company): float
    {
        $now = new \DateTime();
        $firstDayOfMonth = new \DateTime($now->format('Y-m-01'));
        $lastDayOfMonth = new \DateTime($now->format('Y-m-t'));

        $result = $this->createQueryBuilder('o')
            ->select('SUM(o.totalTTC)')
            ->where('o.company = :company')
            ->andWhere('o.createdAt >= :start')
            ->andWhere('o.createdAt <= :end')
            ->setParameter('company', $company)
            ->setParameter('start', $firstDayOfMonth)
            ->setParameter('end', $lastDayOfMonth)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }
}
