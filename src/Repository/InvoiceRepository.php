<?php

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invoice>
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    /**
     * Trouver le revenu mensuel (factures payées ce mois)
     */
    public function findMonthlyRevenue($company): float
    {
        $now = new \DateTime();
        $firstDayOfMonth = new \DateTime($now->format('Y-m-01'));
        $lastDayOfMonth = new \DateTime($now->format('Y-m-t'));

        $result = $this->createQueryBuilder('i')
            ->select('SUM(i.totalTTC)')
            ->where('i.company = :company')
            ->andWhere('i.isPay = :isPay')
            ->andWhere('i.createdAt >= :start')
            ->andWhere('i.createdAt <= :end')
            ->setParameter('company', $company)
            ->setParameter('isPay', true)
            ->setParameter('start', $firstDayOfMonth)
            ->setParameter('end', $lastDayOfMonth)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    /**
     * Trouver le revenu annuel (factures payées cette année)
     */
    public function findAnnualRevenue($company): float
    {
        $now = new \DateTime();
        $year = $now->format('Y');
        $firstDayOfYear = new \DateTime("$year-01-01");
        $lastDayOfYear = new \DateTime("$year-12-31");

        $result = $this->createQueryBuilder('i')
            ->select('SUM(i.totalTTC)')
            ->where('i.company = :company')
            ->andWhere('i.isPay = :isPay')
            ->andWhere('i.createdAt >= :start')
            ->andWhere('i.createdAt <= :end')
            ->setParameter('company', $company)
            ->setParameter('isPay', true)
            ->setParameter('start', $firstDayOfYear)
            ->setParameter('end', $lastDayOfYear)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    /**
     * Compter les factures impayées
     */
    public function findUnpaidCount($company): int
    {
        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.company = :company')
            ->andWhere('i.isPay = :isPay')
            ->setParameter('company', $company)
            ->setParameter('isPay', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Montant total des factures impayées
     */
    public function findUnpaidAmount($company): float
    {
        $result = $this->createQueryBuilder('i')
            ->select('SUM(i.totalTTC)')
            ->where('i.company = :company')
            ->andWhere('i.isPay = :isPay')
            ->setParameter('company', $company)
            ->setParameter('isPay', false)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    /**
     * Compter les factures payées cette année
     */
    public function findCountPaidThisYear($company): int
    {
        $now = new \DateTime();
        $year = $now->format('Y');
        $firstDayOfYear = new \DateTime("$year-01-01");
        $lastDayOfYear = new \DateTime("$year-12-31");

        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.company = :company')
            ->andWhere('i.isPay = :isPay')
            ->andWhere('i.createdAt >= :start')
            ->andWhere('i.createdAt <= :end')
            ->setParameter('company', $company)
            ->setParameter('isPay', true)
            ->setParameter('start', $firstDayOfYear)
            ->setParameter('end', $lastDayOfYear)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouver les factures récentes
     */
    public function findRecentByCompany($company, int $limit = 5): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.company = :company')
            ->setParameter('company', $company)
            ->orderBy('i.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compter par entreprise (toutes)
     */
    public function findCountByCompany($company): int
    {
        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compter les factures payées annuellement (ancien nom, garder pour compatibilité)
     */
    public function countInvoicesByCompanyAnnual($company): int
    {
        return $this->findCountPaidThisYear($company);
    }
}
