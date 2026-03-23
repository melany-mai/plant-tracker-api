<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class SpeciesInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $commonName = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $latinName = null;
}
