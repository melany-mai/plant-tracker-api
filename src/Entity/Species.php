<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Dto\SpeciesInput;
use App\Dto\SpeciesOutput;
use App\Repository\SpeciesRepository;
use App\State\SpeciesProcessor;
use App\State\SpeciesProvider;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: SpeciesRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            provider: SpeciesProvider::class,
        ),
        new Get(
            security: "is_granted('ROLE_USER')",
            provider: SpeciesProvider::class,
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            input: SpeciesInput::class,
            output: SpeciesOutput::class,
            processor: SpeciesProcessor::class,
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            input: SpeciesInput::class,
            output: SpeciesOutput::class,
            provider: SpeciesProvider::class,
            processor: SpeciesProcessor::class,
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
        ),
    ],
    output: SpeciesOutput::class,
)]
class Species
{
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $commonName = null;

    #[ORM\Column(length: 255)]
    private ?string $latinName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommonName(): ?string
    {
        return $this->commonName;
    }

    public function setCommonName(string $commonName): static
    {
        $this->commonName = $commonName;

        return $this;
    }

    public function getLatinName(): ?string
    {
        return $this->latinName;
    }

    public function setLatinName(string $latinName): static
    {
        $this->latinName = $latinName;

        return $this;
    }
}
