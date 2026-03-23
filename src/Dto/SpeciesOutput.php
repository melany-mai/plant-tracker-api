<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Species;

#[ApiResource(
    shortName: 'Species',
    operations: [],
    types: ['Species'],
)]
class SpeciesOutput
{
    #[ApiProperty(identifier: true)]
    public int $id;
    public string $commonName;
    public string $latinName;
    public ?\DateTimeInterface $createdAt;
    public ?\DateTimeInterface $updatedAt;

    public static function fromEntity(Species $species): self
    {
        $output = new self();
        $output->id = $species->getId();
        $output->commonName = $species->getCommonName();
        $output->latinName = $species->getLatinName();
        $output->createdAt = $species->getCreatedAt();
        $output->updatedAt = $species->getUpdatedAt();

        return $output;
    }
}
