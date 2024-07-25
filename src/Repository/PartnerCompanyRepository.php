<?php

namespace App\Repository;

use App\Entity\Partner;
use App\Entity\Company;
use App\Entity\PartnerCompany;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @extends ServiceEntityRepository<PartnerCompany>
 */
class PartnerCompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PartnerCompany::class);
    }
    public function add(PartnerCompany $entity, bool $flush = false):void
    {
        $this->getEntityManager()->persist($entity);
        if($flush){
            $this->getEntityManager()->flush();
        }

        
    }
    public function findAllByPartner(Partner $partner): ?ArrayCollection
    {
        $result = $this->createQueryBuilder('c')
            ->andWhere('c.Partner= :Partner')
            ->setParameter('Partner', $partner)
            ->getQuery()
            ->getResult(); // Use getResult() para obter uma coleção de entidades Partner

            return new ArrayCollection($result);
            
    }
    public function findAllByCompany(Company $company): ?ArrayCollection
    {
        $result = $this->createQueryBuilder('c')
            ->andWhere('c.company= :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult(); // Use getResult() para obter uma coleção de entidades Partner

            return new ArrayCollection($result);
            
    }

    //    /**
    //     * @return PartnerCompaine[] Returns an array of PartnerCompaine objects
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

    //    public function findOneBySomeField($value): ?PartnerCompaine
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
