<?php

namespace App\Repository;

use App\Entity\Advertisement;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Advertisement>
 *
 * @method Advertisement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Advertisement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Advertisement[]    findAll()
 * @method Advertisement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdvertisementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advertisement::class);
    }

    public function findAllByDate()
    {
        return $this->createQueryBuilder('a')
            ->addSelect('category')
            ->orderBy('a.createdAt', 'ASC')
            ->leftJoin('a.category', 'category')
            ->getQuery()
            ->getResult();
    }

    public function queryAllByDate(string $search): Query
    {
        return $this->createQueryBuilder('a')
            ->addSelect('category')
            ->orderBy('a.createdAt', 'ASC')
            ->leftJoin('a.category', 'category')
            ->where('LOWER(a.title) LIKE :search OR LOWER(a.description) LIKE :search')
            ->andWhere('a.currentState = :state')
            ->setParameter('search', '%'.strtolower($search).'%')
            ->setParameter('state', Advertisement::STATE_PUBLISHED)
            ->getQuery();
    }

    public function queryAllByDateAndUser(User $user): Query
    {
        return $this->createQueryBuilder('a')
            ->addSelect('category')
            ->orderBy('a.createdAt', 'ASC')
            ->leftJoin('a.category', 'category')
            ->where('a.owner = :id')
            ->andWhere('a.currentState = :state')
            ->setParameter('id', $user)
            ->setParameter('state', Advertisement::STATE_PUBLISHED)
            ->getQuery();
    }

    public function findByCategory(Category $category)
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'ASC')
            ->andWhere('a.category = :val')
            ->andWhere('a.currentState = :state')
            ->setParameter('val', $category)
            ->setParameter('state', Advertisement::STATE_PUBLISHED)
            ->getQuery()
            ->getResult();
    }

    public function queryByCategory(Category $category): Query
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'ASC')
            ->andWhere('a.category = :val')
            ->setParameter('val', $category)
            ->getQuery();
    }

    public function findWithCategory(int $id): Advertisement
    {
        return $this->createQueryBuilder('a')
            ->addSelect('category')
            ->leftJoin('a.category', 'category')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()[0];
    }

    public function findByNotOwnedUser(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.owner != :user')
            ->andWhere('a.currentState = :state')
            ->setParameter('user', $user)
            ->setParameter('state', Advertisement::STATE_PUBLISHED)
            ->getQuery()
            ->getResult();
    }

    public function queryLikedByUser(User $user): Query
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.likes', 'l')
            ->where('l.owner = :user')
            ->andWhere('a.currentState = :state')
            ->setParameter('user', $user)
            ->setParameter('state', Advertisement::STATE_PUBLISHED)
            ->getQuery();
    }

    public function queryDraftByUser(User $user): Query
    {
        return $this->createQueryBuilder('a')
            ->where('a.owner = :user')
            ->andWhere('a.currentState = :state')
            ->setParameter('user', $user)
            ->setParameter('state', Advertisement::STATE_DRAFT)
            ->getQuery();
    }

    //    /**
    //     * @return Advertisement[] Returns an array of Advertisement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Advertisement
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
