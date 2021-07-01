<?php

namespace App\Repository;

use App\Entity\Shop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Shop|null find($id, $lockMode = null, $lockVersion = null)
 * @method Shop|null findOneBy(array $criteria, array $orderBy = null)
 * @method Shop[]    findAll()
 * @method Shop[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shop::class);
    }

    public function findByTerm(string $term) {
        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder->where(
            $queryBuilder->expr()->orX(
                $queryBuilder->expr()->like('e.name', ':term'),
                $queryBuilder->expr()->like('e.location', ':term'),
            )
        );
        $queryBuilder->setParameter('term', '%'.$term.'%'); // % hace que en SQL busque el term en cuanlquier parte de la columna (si contiene, empieza, termina, etc)
        // $queryBuilder->orderBy('e.id', 'ASC');

        $query = $queryBuilder->getQuery();
        $result = $query->getResult();

        return $result;
    }

    // /**
    //  * @return Shop[] Returns an array of Shop objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Shop
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
