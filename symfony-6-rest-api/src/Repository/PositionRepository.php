<?php

namespace App\Repository;

use App\Entity\Position;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Position>
 *
 * @method Position|null find($id, $lockMode = null, $lockVersion = null)
 * @method Position|null findOneBy(array $criteria, array $orderBy = null)
 * @method Position[]    findAll()
 * @method Position[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Position::class);
    }

    public function save(Position $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Position $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Position[] Returns an array of Position objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Position
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
   
   /**
    * findWithSmallestId
    * Find the smallest id for update tests
    *
    * @return array
    */
   public function findWithSmallestId(): array
   {
       return $this->createQueryBuilder('p')
           ->orderBy('p.id', 'DESC')
           ->setMaxResults(1)
           ->getQuery()
           ->getResult()
       ;
   }
   
   /**
    * findByName
    * findByName and set the max returned results
    * default is near infinity
    *
    * @param  mixed $value
    * @param  mixed $max
    * @return array
    */
   public function findByName($value,$max=999999999): array
   {
       return $this->createQueryBuilder('p')
           ->andWhere('p.name like :val')
           ->setParameter('val', '%'.$value.'%')
           ->orderBy('p.name', 'ASC')
           ->setMaxResults($max)
           ->getQuery()
           ->getResult()
       ;
   }


}
