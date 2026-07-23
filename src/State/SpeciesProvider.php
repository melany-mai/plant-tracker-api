<?php

namespace App\State;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Dto\SpeciesOutput;
use App\Repository\SpeciesRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<SpeciesOutput> */
readonly class SpeciesProvider implements ProviderInterface
{
    public function __construct(
        private SpeciesRepository $speciesRepository,
        private Pagination $pagination,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): SpeciesOutput|TraversablePaginator
    {
        if ($operation instanceof GetCollection) {
            // The Pagination service reads ?page=N from the URL and computes
            // offset and limit from the resource's paginationItemsPerPage setting.
            $offset = $this->pagination->getOffset($operation, $context);
            $limit = $this->pagination->getLimit($operation, $context);

            $doctrinePaginator = $this->speciesRepository->findPaginated($offset, $limit);

            $items = [];
            foreach ($doctrinePaginator as $species) {
                $items[] = SpeciesOutput::fromEntity($species);
            }

            // TraversablePaginator wraps our items and tells API Platform:
            // - which page we're on      → generates previous/next links
            // - how many items per page  → used in view metadata
            // - the total item count     → shown as totalItems in the response
            return new TraversablePaginator(
                new \ArrayIterator($items),
                $this->pagination->getPage($context),
                $limit,
                count($doctrinePaginator),
            );
        }

        $species = $this->speciesRepository->find($uriVariables['id']);

        if (!$species) {
            throw new NotFoundHttpException('Species not found.');
        }

        return SpeciesOutput::fromEntity($species);
    }
}
