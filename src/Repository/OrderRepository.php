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

    public function findOrder(): array
    {
        return $this->createQueryBuilder('o')
            ->select('o, p, u')
            ->andWhere('o.status != :status')
            ->setParameter('status', Order::STATUS_CART)
            ->join('o.user', 'u')
            ->join('o.payments', 'p')
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getCaThisMonth(): float
    {
        $ca = $this->createQueryBuilder('o')
            ->select('(SUM(p.priceHT) * (1 + t.rate)) * item.quantity as ca')
            ->andWhere('o.status = :status')
            ->andWhere('o.createdAt > :date')
            ->join('o.items', 'item')
            ->join('item.product', 'p')
            ->join('p.taxe', 't')
            ->setParameter('status', Order::STATUS_PAID)
            ->setParameter('date', new \DateTime('first day of this month'))
            ->getQuery()
            ->getOneOrNullResult();

        return $ca['ca'] ?? 0;
    }

    public function getCaThisYear(): float
    {
        $ca = $this->createQueryBuilder('o')
            ->select('(SUM(p.priceHT) * (1 + t.rate)) * item.quantity as ca')
            ->andWhere('o.status = :status')
            ->andWhere('o.createdAt > :date')
            ->join('o.items', 'item')
            ->join('item.product', 'p')
            ->join('p.taxe', 't')
            ->setParameter('status', Order::STATUS_PAID)
            ->setParameter('date', new \DateTime('first day of this year'))
            ->getQuery()
            ->getOneOrNullResult();

        return $ca['ca'] ?? 0;
    }

    public function getCAByMonth(\DateTime $start): float
    {
        $start = $start->modify('first day of this month');
        $end = (new \DateTime($start->format('Y-m-d')))->modify('last day of this month');

        $query = $this->createQueryBuilder('o')
            ->select('(SUM(p.priceHT) * (1 + t.rate)) * item.quantity as ca')
            ->andWhere('o.status = :status')
            ->andWhere('o.createdAt BETWEEN :start AND :end')
            ->join('o.items', 'item')
            ->join('item.product', 'p')
            ->join('p.taxe', 't')
            ->setParameter('status', Order::STATUS_PAID)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();

        $amount = 0;

        foreach ($query as $invoiceAmount) {
            $amount += $invoiceAmount['ca'];
        }

        return $amount;
    }

    public function getNbOrderThisYear(): int
    {
        $nbOrder = $this->createQueryBuilder('o')
            ->select('COUNT(o.id) as nbOrder')
            ->andWhere('o.status = :status')
            ->andWhere('o.createdAt > :date')
            ->setParameter('status', Order::STATUS_PAID)
            ->setParameter('date', new \DateTime('first day of this year'))
            ->getQuery()
            ->getOneOrNullResult();

        return $nbOrder['nbOrder'] ?? 0;
    }

    public function getAverageCart(): float
    {
        return $this->getCaThisYear() / $this->getNbOrderThisYear();
    }

    public function findAmountTTCByProduct(int $max): array
    {
        $query = $this->createQueryBuilder('o')
            ->select('p.title as name, SUM(p.priceHT * (1 + t.rate) * item.quantity) as amountTTC')
            ->join('o.items', 'item')
            ->join('item.product', 'p')
            ->join('p.taxe', 't')
            ->andWhere('o.status = :status')
            ->setParameter('status', Order::STATUS_PAID)
            ->groupBy('p.id')
            ->orderBy('amountTTC', 'DESC')
            ->setMaxResults($max)
            ->getQuery()
            ->getResult();

        return array_map(function (array $product): array {
            return [
                'label' => $product['name'],
                'data' => $product['amountTTC'],
            ];
        }, $query);
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
