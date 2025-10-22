<?php

namespace App\Repository;

use App\Entity\Transaccion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaccion>
 */
class TransaccionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaccion::class);
    }

    /**
     * Obtener histórico de transacciones de una billetera
     */
    public function findByBilleteraId(int $billeteraId, int $limit = 50): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.billetera = :billeteraId')
            ->setParameter('billeteraId', $billeteraId)
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtener transacciones por tipo (recarga o pago)
     */
    public function findByTipo(string $tipo, int $limit = 100): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.tipo = :tipo')
            ->setParameter('tipo', $tipo)
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtener total de dinero movido en un período
     */
    public function getTotalByTipoAndDate(string $tipo, \DateTime $desde, \DateTime $hasta): string
    {
        $result = $this->createQueryBuilder('t')
            ->select('SUM(t.monto) as total')
            ->where('t.tipo = :tipo')
            ->andWhere('t.createdAt >= :desde')
            ->andWhere('t.createdAt <= :hasta')
            ->setParameter('tipo', $tipo)
            ->setParameter('desde', $desde)
            ->setParameter('hasta', $hasta)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? '0';
    }
}
