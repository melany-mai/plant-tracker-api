<?php

namespace App\Repository;

use App\Entity\Species;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Species>
 */
class SpeciesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Species::class);
    }

    /**
     * Returns a paginated subset of all species.
     *
     * The Doctrine Paginator executes two queries automatically:
     * - a COUNT(*) to get the total (used by API Platform for totalItems)
     * - a SELECT with LIMIT/OFFSET for the current page items
     *
     * @return Paginator<Species>
     */
    public function findPaginated(int $offset, int $limit): Paginator
    {
        $query = $this->createQueryBuilder('s')
            ->orderBy('s.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        return new Paginator($query, fetchJoinCollection: false);
    }
}
