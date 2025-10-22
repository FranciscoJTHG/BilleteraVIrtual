<?php

namespace App\Repository;

use App\Entity\Billetera;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Billetera>
 */
class BilleteraRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Billetera::class);
    }

    /**
     * Buscar billetera por ID de cliente
     */
    public function findByClienteId(int $clienteId): ?Billetera
    {
        return $this->createQueryBuilder('b')
            ->where('b.cliente = :clienteId')
            ->setParameter('clienteId', $clienteId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Obtener billeteras con saldo bajo
     */
    public function findWithLowBalance(string $threshold): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.saldo < :threshold')
            ->setParameter('threshold', $threshold)
            ->orderBy('b.saldo', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
