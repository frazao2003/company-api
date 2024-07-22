<?php

namespace App\Repository;

use App\Entity\Partner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Dto\PartnerFilter;

/**
 * @extends ServiceEntityRepository<Partner>
 */
class PartnerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Partner::class);
    }
    public function add(Partner $entity, bool $flush = false):void
    {
        $this->getEntityManager()->persist($entity);
        if($flush){
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Partner $entity, bool $flush = false):void
    {
        $this->getEntityManager()->remove($entity);
        if($flush){
            $this->getEntityManager()->flush();
        }
    }
    public function findByFilter(PartnerFilter $filter): array
    {
        $qb = $this->createQueryBuilder('s');
        $hasFilters = false;
        if ($filter->getName()) {
            $qb->andWhere('s.name LIKE :name')
               ->setParameter('name', '%' . $filter->getName() . '%');
               $hasFilters = true;
        }

        if ($filter->getCpf()) {
            $qb->andWhere('s.cnpj LIKE :cnpj')
               ->setParameter('cnpj', '%' . $filter->getCpf() . '%');
               $hasFilters = true;
        }
        if (!$hasFilters)
        {
            $this->findAll();
        }

        return $qb->getQuery()->getResult();
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
    //     * @return Partner[] Returns an array of Partner objects
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

    //    public function findOneBySomeField($value): ?Partner
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
