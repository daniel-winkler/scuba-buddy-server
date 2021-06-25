<?php

namespace App\Repository;

use App\Entity\Divelog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Divelog|null find($id, $lockMode = null, $lockVersion = null)
 * @method Divelog|null findOneBy(array $criteria, array $orderBy = null)
 * @method Divelog[]    findAll()
 * @method Divelog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DivelogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Divelog::class);
    }

    // /**
    //  * @return Divelog[] Returns an array of Divelog objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Divelog
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
