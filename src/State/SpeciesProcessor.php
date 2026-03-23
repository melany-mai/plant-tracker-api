<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\SpeciesInput;
use App\Dto\SpeciesOutput;
use App\Entity\Species;
use App\Repository\SpeciesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<SpeciesInput, SpeciesOutput> */
class SpeciesProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private SpeciesRepository $speciesRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): SpeciesOutput
    {
        /* @var SpeciesInput $data */

        if ($operation instanceof Post) {
            $species = new Species();
        } else {
            $species = $this->speciesRepository->find($uriVariables['id']);
            if (!$species) {
                throw new NotFoundHttpException('Species not found.');
            }
        }

        $species->setCommonName($data->commonName);
        $species->setLatinName($data->latinName);

        $this->em->persist($species);
        $this->em->flush();

        return SpeciesOutput::fromEntity($species);
    }
}
