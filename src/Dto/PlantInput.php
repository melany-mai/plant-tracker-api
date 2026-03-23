<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class PlantInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[Assert\NotNull]
    #[Assert\Positive]
    public ?int $wateringFrequencyDays = null;

    #[Assert\NotNull]
    public ?\DateTimeImmutable $lastWateredAt = null;

    #[Assert\Length(max: 255)]
    public ?string $photo = null;

    public ?string $notes = null;

    #[Assert\NotNull]
    public ?\DateTimeImmutable $acquiredAt = null;

    #[Assert\NotBlank]
    public ?string $species = null;
}
