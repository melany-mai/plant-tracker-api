<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\PlantInput;
use App\Dto\PlantOutput;
use App\Entity\Plant;
use App\Entity\User;
use App\Repository\PlantRepository;
use App\Repository\SpeciesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<PlantInput, PlantOutput> */
class PlantProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private PlantRepository $plantRepository,
        private SpeciesRepository $speciesRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PlantOutput
    {
        /* @var PlantInput $data */

        if ($operation instanceof Post) {
            $user = $this->security->getUser();
            assert($user instanceof User);
            $plant = new Plant();
            $plant->setOwner($user);
        } else {
            $user = $this->security->getUser();
            $plant = $this->plantRepository->findOneBy([
                'id' => $uriVariables['id'],
                'owner' => $user,
            ]);
            if (!$plant) {
                throw new NotFoundHttpException('Plant not found.');
            }
        }

        $speciesId = (int) basename($data->species);
        $species = $this->speciesRepository->find($speciesId);
        if (!$species) {
            throw new BadRequestHttpException(sprintf('Species "%s" not found.', $data->species));
        }

        $plant->setName($data->name);
        $plant->setWateringFrequencyDays($data->wateringFrequencyDays);
        $plant->setLastWateredAt($data->lastWateredAt);
        $plant->setPhoto($data->photo);
        $plant->setNotes($data->notes);
        $plant->setAcquiredAt($data->acquiredAt);
        $plant->setSpecies($species);

        $this->em->persist($plant);
        $this->em->flush();

        return PlantOutput::fromEntity($plant);
    }
}
