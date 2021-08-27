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
}
