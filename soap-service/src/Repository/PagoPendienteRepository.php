<?php

namespace App\Repository;

use App\Entity\PagoPendiente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PagoPendiente>
 */
class PagoPendienteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PagoPendiente::class);
    }

    /**
     * Buscar pago pendiente por sessionId
     */
    public function findBySessionId(string $sessionId): ?PagoPendiente
    {
        return $this->findOneBy(['sessionId' => $sessionId]);
    }

    /**
     * Obtener pagos pendientes activos (no usados y no expirados)
     */
    public function findActivoPendientes(): array
    {
        $now = new \DateTime();
        return $this->createQueryBuilder('p')
            ->where('p.usado = false')
            ->andWhere('p.expiraEn > :now')
            ->setParameter('now', $now)
            ->orderBy('p.expiraEn', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtener pagos expirados
     */
    public function findExpired(): array
    {
        $now = new \DateTime();
        return $this->createQueryBuilder('p')
            ->where('p.expiraEn < :now')
            ->andWhere('p.usado = false')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    /**
     * Contar pagos pendientes sin usar
     */
    public function countPendientes(): int
    {
        return $this->count([
            'usado' => false
        ]);
    }
}
