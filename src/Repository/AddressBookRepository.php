<?php

namespace App\Repository;

use App\Entity\AddressBook;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AddressBook|null find($id, $lockMode = null, $lockVersion = null)
 * @method AddressBook|null findOneBy(array $criteria, array $orderBy = null)
 * @method AddressBook[]    findAll()
 * @method AddressBook[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AddressBookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AddressBook::class);
    }

    public function getAllWithSearchQueryBuilder(?string $text, User $user): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ab')
            ->andWhere('ab.user = :user')
            ->setParameter('user', $user);

        if ($text) {
            $qb->andWhere('ab.name LIKE :text OR ab.number LIKE :text')
                    ->setParameter('text', '%'.$text.'%');
        }

        return $qb
                ->orderBy('ab.name', 'ASC');
    }

    // /**
    //  * @return AddressBook[] Returns an array of AddressBook objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AddressBook
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
