<?php

namespace App\Repository;

use App\Entity\Cliente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cliente>
 */
class ClienteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cliente::class);
    }

    /**
     * Buscar cliente por documento y celular
     */
    public function findByDocumentoAndCelular(string $documento, string $celular): ?Cliente
    {
        return $this->createQueryBuilder('c')
            ->where('c.documento = :documento')
            ->andWhere('c.celular = :celular')
            ->setParameter('documento', $documento)
            ->setParameter('celular', $celular)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Buscar por documento
     */
    public function findByDocumento(string $documento): ?Cliente
    {
        return $this->findOneBy(['documento' => $documento]);
    }

    /**
     * Buscar por email
     */
    public function findByEmail(string $email): ?Cliente
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Verificar si cliente existe
     */
    public function existsByDocumento(string $documento): bool
    {
        return $this->count(['documento' => $documento]) > 0;
    }

    public function existsByEmail(string $email): bool
    {
        return $this->count(['email' => $email]) > 0;
    }
}
