<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Crée une QueryBuilder pour sélectionner les utilisateurs non vérifiés depuis une date donnée.
     *
     * @param \DateTimeImmutable $date Date depuis laquelle sélectionner les utilisateurs
     */
    private function getUnverifiedUsersQuery(\DateTimeImmutable $date): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isVerified = :verified')
            ->andWhere('u.registeredAt <= :date')
            ->setParameter('verified', false)
            ->setParameter('date', $date);
    }

    /**
     * Sélectionne les utilisateurs non vérifiés depuis un nombre de jours donné.
     *
     * @param int $days Nombre de jours écoulés
     *
     * @return User[] Liste des utilisateurs non vérifiés depuis le nombre de jours spécifié
     */
    public function findUnverifiedUsersSince(int $days = 0): array
    {
        $date = new \DateTimeImmutable("-$days days");

        return $this->getUnverifiedUsersQuery($date)
            ->getQuery()
            ->getResult();
    }

    /**
     * Supprime les utilisateurs non vérifiés depuis un nombre de jours donné.
     *
     * @param int $days Nombre de jours écoulés
     *
     * @return int Nombre d'utilisateurs supprimés
     */
    public function deleteUnverifiedUsersSince(int $days = 0): int
    {
        $date = new \DateTimeImmutable("-$days days");

        $query = $this->getUnverifiedUsersQuery($date);
        $usersToDelete = $query->getQuery()->getResult();

        foreach ($usersToDelete as $user) {
            $this->_em->remove($user);
        }

        $this->_em->flush();

        return count($usersToDelete);
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
