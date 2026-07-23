<?php

namespace App\Repository;

use App\Entity\Plant;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Plant>
 */
class PlantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plant::class);
    }

    /**
     * Returns a paginated subset of plants belonging to the given user.
     *
     * The WHERE owner = :user clause enforces data isolation at the query level,
     * in addition to the filtering already done in PlantProvider.
     * The Doctrine Paginator handles COUNT(*) + LIMIT/OFFSET automatically.
     *
     * @return Paginator<Plant>
     */
    public function findByOwnerPaginated(User $user, int $offset, int $limit): Paginator
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.owner = :owner')
            ->setParameter('owner', $user)
            ->orderBy('p.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        return new Paginator($query, fetchJoinCollection: false);
    }
}
