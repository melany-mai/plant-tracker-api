<?php

namespace App\State;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Dto\PlantOutput;
use App\Entity\User;
use App\Repository\PlantRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<PlantOutput> */
readonly class PlantProvider implements ProviderInterface
{
    public function __construct(
        private PlantRepository $plantRepository,
        private Security $security,
        private Pagination $pagination,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PlantOutput|TraversablePaginator
    {
        $user = $this->security->getUser();
        assert($user instanceof User);

        if ($operation instanceof GetCollection) {
            $offset = $this->pagination->getOffset($operation, $context);
            $limit = $this->pagination->getLimit($operation, $context);

            $doctrinePaginator = $this->plantRepository->findByOwnerPaginated($user, $offset, $limit);

            $items = [];
            foreach ($doctrinePaginator as $plant) {
                $items[] = PlantOutput::fromEntity($plant);
            }

            return new TraversablePaginator(
                new \ArrayIterator($items),
                $this->pagination->getPage($context),
                $limit,
                count($doctrinePaginator),
            );
        }

        $plant = $this->plantRepository->findOneBy([
            'id' => $uriVariables['id'],
            'owner' => $user,
        ]);

        if (!$plant) {
            throw new NotFoundHttpException('Plant not found.');
        }

        return PlantOutput::fromEntity($plant);
    }
}
