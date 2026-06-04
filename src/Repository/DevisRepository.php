<?php

namespace App\Repository;

use App\Entity\Devis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Devis>
 */
class DevisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Devis::class);
    }

    /**
     * Compter les devis créés ce mois
     */
    public function findCountThisMonth($company): int
    {
        $now = new \DateTime();
        $firstDayOfMonth = new \DateTime($now->format('Y-m-01'));
        $lastDayOfMonth = new \DateTime($now->format('Y-m-t'));

        return (int) $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.company = :company')
            ->andWhere('d.createdAt >= :start')
            ->andWhere('d.createdAt <= :end')
            ->setParameter('company', $company)
            ->setParameter('start', $firstDayOfMonth)
            ->setParameter('end', $lastDayOfMonth)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouver les devis récents
     */
    public function findRecentByCompany($company, int $limit = 5): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.company = :company')
            ->setParameter('company', $company)
            ->orderBy('d.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compter tous les devis par entreprise
     */
    public function CountDevisByCompany($company): int
    {
        return (int) $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouver par reference et company (pour les routes UUID)
     */
    public function findByReferenceAndCompany(string $reference, $company): ?Devis
    {
        return $this->findOneBy([
            'reference' => $reference,
            'company' => $company
        ]);
    }
}
