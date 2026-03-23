<?php

namespace App\State;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\PlantOutput;
use App\Entity\Plant;
use App\Repository\PlantRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<PlantOutput> */
class PlantProvider implements ProviderInterface
{
    public function __construct(
        private PlantRepository $plantRepository,
        private Security $security,
    ) {
    }

    /** @return PlantOutput|array<int, PlantOutput> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PlantOutput|array
    {
        $user = $this->security->getUser();

        if ($operation instanceof GetCollection) {
            $plants = $this->plantRepository->findBy(['owner' => $user]);

            return array_map(fn (Plant $plant) => PlantOutput::fromEntity($plant), $plants);
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
