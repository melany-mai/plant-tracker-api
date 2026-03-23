<?php

namespace App\State;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\SpeciesOutput;
use App\Entity\Species;
use App\Repository\SpeciesRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<SpeciesOutput> */
class SpeciesProvider implements ProviderInterface
{
    public function __construct(
        private SpeciesRepository $speciesRepository,
    ) {
    }

    /** @return SpeciesOutput|array<int, SpeciesOutput> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): SpeciesOutput|array
    {
        if ($operation instanceof GetCollection) {
            $species = $this->speciesRepository->findAll();

            return array_map(fn (Species $s) => SpeciesOutput::fromEntity($s), $species);
        }

        $species = $this->speciesRepository->find($uriVariables['id']);

        if (!$species) {
            throw new NotFoundHttpException('Species not found.');
        }

        return SpeciesOutput::fromEntity($species);
    }
}
