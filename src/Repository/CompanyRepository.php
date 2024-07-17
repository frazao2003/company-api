<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }
    public function add(Company $entity, bool $flush = false):void
    {
        $this->getEntityManager()->persist($entity);
        if($flush){
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Company $entity, bool $flush = false):void
    {
        $this->getEntityManager()->remove($entity);
        if($flush){
            $this->getEntityManager()->flush();
        }
    }
    public function findOneByCpf(string $cpf): ?Company
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.cpf= :cpf')
            ->setParameter('cpf', $cpf)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
    public function existsByCnpj(string $cnpj): bool
    {
        // Cria um QueryBuilder para a entidade User, com o alias 'u'
        $qb = $this->createQueryBuilder('c');
        
        // Seleciona a contagem de IDs onde o email corresponde ao parâmetro fornecido
        $qb->select('count(c.id)')
           ->where('c.cnpj = :cnpj')
           ->setParameter('cnpj', $cnpj);

        // Executa a consulta e obtém o resultado
        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    //    /**
    //     * @return Company[] Returns an array of Company objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

}
