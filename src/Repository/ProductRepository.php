<?php

namespace App\Repository;

use App\Entity\Product;
use App\Filter\ProductFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private PaginatorInterface $paginator
    ) {
        parent::__construct($registry, Product::class);
    }

    public function createListShop(bool $includeDisable = false, int $page = 1, int $maxPerPage = 6): PaginationInterface
    {
        $query = $this->createQueryBuilder('p')
            ->select('p, c, t')
            ->leftJoin('p.categories', 'c')
            ->join('p.taxe', 't');

        if (!$includeDisable) {
            $query->andWhere('p.enable = true');
        }

        $query
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery();

        return $this->paginator->paginate(
            $query,
            $page,
            $maxPerPage
        );
    }

    public function createFilterListShop(ProductFilter $filter, int $maxPerPage = 6): array
    {
        $query = $this->createQueryBuilder('p')
            ->select('p', 'c', 't')
            ->leftJoin('p.categories', 'c')
            ->join('p.taxe', 't');

        if ($filter->getQuery()) {
            $query
                ->andWhere('p.title LIKE :query')
                ->setParameter('query', '%' . $filter->getQuery() . '%');
        }

        if ($filter->getMin()) {
            $query
                ->andWhere('p.priceHT * (1 + t.rate) >= :min')
                ->setParameter('min', $filter->getMin());
        }

        if ($filter->getMax()) {
            $query
                ->andWhere('p.priceHT * (1 + t.rate) <= :max')
                ->setParameter('max', $filter->getMax());
        }

        if ($filter->getTags()) {
            $query
                ->andWhere('c.id IN (:tags)')
                ->setParameter('tags', $filter->getTags());
        }


        $query
            ->orderBy($filter->getSort(), $filter->getDirection())
            ->getQuery();

        $paginate = $this->paginator->paginate(
            $query,
            $filter->getPage(),
            $maxPerPage
        );

        $subQuery = $query->select('MIN(p.priceHT * (1 + t.rate)) as min', 'MAX(p.priceHT * (1 + t.rate)) as max')
            ->getQuery()
            ->getScalarResult();

        return [
            'data' => $paginate,
            'min' => (int)$subQuery[0]['min'],
            'max' => (int)$subQuery[0]['max'],
        ];
    }

    public function findLatest(int $limit, bool $includeDisable = false): array
    {
        $query = $this->createQueryBuilder('p')
            ->select('p, c, t')
            ->leftJoin('p.categories', 'c')
            ->join('p.taxe', 't');

        if (!$includeDisable) {
            $query->andWhere('p.enable = true');
        }

        return $query
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->groupBy('p.id')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
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

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
