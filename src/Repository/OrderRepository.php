<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findCartsNotModifiedSince(\DateTime $limitDate, int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.updatedAt < :limitDate')
            ->andWhere('c.status = :status')
            ->setParameter('limitDate', $limitDate)
            ->setParameter('status', Order::STATUS_CART)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLastCartUser(User $user): ?Order
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->andWhere('c.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', Order::STATUS_CART)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Order[] Returns an array of Order objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Order
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
