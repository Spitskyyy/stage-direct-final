<?php

namespace App\Repository;

use App\Entity\Speciality;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Speciality>
 */
class SpecialityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Speciality::class);
    }

    /**
    * @return Speciality[] Returns an array of Company objects
    */

    public function findAll(int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;  // Calcul de l'offset
    
        return $this->createQueryBuilder('c')
            ->orderBy('c.id', 'ASC')  // Trier par ID ou tout autre champ
            ->setFirstResult($offset)  // Définir l'offset
            ->setMaxResults($limit)   // Limiter le nombre de résultats par page
            ->getQuery()
            ->getResult();
    }

    public function searchBy($criteria): ?array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.name LIKE :val')
            ->setParameter('val', '%' . $criteria['name'] . '%')
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    //    /**
    //     * @return Speciality[] Returns an array of Speciality objects
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

    //    public function findOneBySomeField($value): ?Speciality
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
