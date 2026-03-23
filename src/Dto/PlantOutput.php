<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Plant;

#[ApiResource(
    shortName: 'Plant',
    operations: [],
    types: ['Plant'],
    normalizationContext: ['skip_null_values' => false],
)]
class PlantOutput
{
    #[ApiProperty(identifier: true)]
    public int $id;
    public string $name;
    public int $wateringFrequencyDays;
    public \DateTimeImmutable $lastWateredAt;
    public ?string $photo;
    public ?string $notes;
    public \DateTimeImmutable $acquiredAt;
    public string $speciesCommonName;
    public string $speciesLatinName;
    public ?\DateTimeInterface $createdAt;
    public ?\DateTimeInterface $updatedAt;

    public static function fromEntity(Plant $plant): self
    {
        $output = new self();
        $output->id = $plant->getId();
        $output->name = $plant->getName();
        $output->wateringFrequencyDays = $plant->getWateringFrequencyDays();
        $output->lastWateredAt = $plant->getLastWateredAt();
        $output->photo = $plant->getPhoto();
        $output->notes = $plant->getNotes();
        $output->acquiredAt = $plant->getAcquiredAt();
        $output->speciesCommonName = $plant->getSpecies()->getCommonName();
        $output->speciesLatinName = $plant->getSpecies()->getLatinName();
        $output->createdAt = $plant->getCreatedAt();
        $output->updatedAt = $plant->getUpdatedAt();

        return $output;
    }
}
