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

    // public function findActive($getQueryBuilder = false) {
    //     $queryBuilder = $this->createQueryBuilder('e')
    //         ->where('e.active = true');

    //     return $getQueryBuilder ? $queryBuilder : $queryBuilder->getQuery()->getResult();
    // }

    // public function findByCriteria(array $criteria) {
    //     $queryBuilder = $this->findActive(true);

    //     if (isset($criteria['terms'])) {
    //         $queryBuilder->andWhere(
    //             $queryBuilder->expr()->orX(
    //                 $queryBuilder->expr()->like('e.name', ':term'),
    //                 $queryBuilder->expr()->like('e.location', ':term'),
    //             )
    //         );
    //         $queryBuilder->setParameter('term', '%'.$criteria['terms'].'%'); 
    //     }

    //     if (isset($criteria['country'])) {
    //         $queryBuilder->join('country c');
    //         $queryBuilder->where();
    //         $queryBuilder->setParameter('term', '%'.$term.'%'); 
    //     }
    // }
    
    public function getPagination($paginator, $request, $query){
        $pagination = $paginator->paginate(
                    $query, // recibe la QUERY y no el resultado
                    $request->query->getInt('page', 1), // numero de paǵina por el que empieza por defecto
                    6 // limite de resultados por página
                    );
        return $pagination;
    }

    public function findActive(){

        $queryBuilder = $this->createQueryBuilder('a');
        $queryBuilder->where('a.active = true');
        $query = $queryBuilder->getQuery();

        return $query; // devuelve la query porque lo necesita el paginator
    }

    public function findByTerm(string $term) {
        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder->where(
            $queryBuilder->expr()->orX(
                $queryBuilder->expr()->like('e.name', ':term'),
                $queryBuilder->expr()->like('e.location', ':term'),
            )
        );
        $queryBuilder->andWhere('e.active = true');
        $queryBuilder->setParameter('term', '%'.$term.'%'); // % hace que en SQL busque el term en cuanlquier parte de la columna (si contiene, empieza, termina, etc)
        // $queryBuilder->orderBy('e.id', 'ASC');

        $query = $queryBuilder->getQuery();
        return $query; // devuelve la query porque lo necesita el paginator
    }

    public function findByDestination(int $destId) {
        $queryBuilder = $this->createQueryBuilder('s')->leftJoin('s.destination', 'd'); // declaramos destination en SINGULAR porque tiene relacion ManyToOne
        $queryBuilder->where('s.active = true');
        $queryBuilder->andWhere("d.id = $destId");
        $query = $queryBuilder->getQuery();
        dump($query);
        return $query; // devuelve la query porque lo necesita el paginator
    }

    public function findByLanguages(string $lang) {
        $queryBuilder = $this->createQueryBuilder('s')->leftJoin('s.languages', 'l'); // declaramos languages en PLURAL porque tiene relacion ManyToMany
        $queryBuilder->where('s.active = true');
        $queryBuilder->andWhere("l.countrycode = '$lang'"); //variable entre comillas simples porque recibe un string
        $query = $queryBuilder->getQuery();

        return $query; // devuelve la query porque lo necesita el paginator
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
