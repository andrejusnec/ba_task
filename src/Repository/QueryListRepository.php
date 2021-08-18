<?php

namespace App\Repository;

use App\Entity\QueryList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method QueryList|null find($id, $lockMode = null, $lockVersion = null)
 * @method QueryList|null findOneBy(array $criteria, array $orderBy = null)
 * @method QueryList[]    findAll()
 * @method QueryList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QueryListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QueryList::class);
    }

    // /**
    //  * @return QueryList[] Returns an array of QueryList objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('q.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?QueryList
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
