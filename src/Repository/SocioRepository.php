<?php

namespace App\Repository;

use App\Entity\Socio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Socio>
 */
class SocioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Socio::class);
    }
    public function add(Socio $entity, bool $flush = false):void
    {
        $this->getEntityManager()->persist($entity);
        if($flush){
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Socio $entity, bool $flush = false):void
    {
        $this->getEntityManager()->remove($entity);
        if($flush){
            $this->getEntityManager()->flush();
        }
    }
    public function findOneByCpf(string $cpf): ?Socio
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.cpf= :cpf')
            ->setParameter('cpf', $cpf)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
    public function existsByCpf(string $cpf): bool
    {
        $qb = $this->createQueryBuilder('s');
        $qb->select('count(s.id)')
           ->where('s.cpf = :cpf')
           ->setParameter('cpf', $cpf);

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    //    /**
    //     * @return Socio[] Returns an array of Socio objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Socio
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
