<?php

namespace App\DataFixtures;

use App\Entity\Plant;
use App\Entity\Species;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $speciesData = [
            ['Monstera', 'Monstera deliciosa'],
            ['Pothos', 'Epipremnum aureum'],
            ['Ficus', 'Ficus lyrata'],
            ['Succulent', 'Echeveria elegans'],
            ['Orchidée', 'Phalaenopsis amabilis'],
        ];

        $species = [];
        foreach ($speciesData as [$common, $latin]) {
            $s = new Species();
            $s->setCommonName($common);
            $s->setLatinName($latin);
            $manager->persist($s);
            $species[] = $s;
        }

        $admin = new User();
        $admin->setEmail('admin@plant.dev');
        $admin->setUsername('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'password'));
        $manager->persist($admin);

        $user1 = new User();
        $user1->setEmail('user1@plant.dev');
        $user1->setUsername('user1');
        $user1->setPassword($this->hasher->hashPassword($user1, 'password'));
        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail('user2@plant.dev');
        $user2->setUsername('user2');
        $user2->setPassword($this->hasher->hashPassword($user2, 'password'));
        $manager->persist($user2);

        $plantsData = [
            [
                'owner' => $user1,
                'name' => 'Ma Monstera',
                'species' => $species[0],
                'wateringFrequencyDays' => 7,
                'lastWateredAt' => new \DateTimeImmutable('-3 days'),
                'acquiredAt' => new \DateTimeImmutable('-6 months'),
                'notes' => 'Exposée à la lumière indirecte, se porte très bien.',
            ],
            [
                'owner' => $user1,
                'name' => 'Pothos du bureau',
                'species' => $species[1],
                'wateringFrequencyDays' => 10,
                'lastWateredAt' => new \DateTimeImmutable('-1 day'),
                'acquiredAt' => new \DateTimeImmutable('-1 year'),
                'notes' => null,
            ],
            [
                'owner' => $user1,
                'name' => 'Ficus du salon',
                'species' => $species[2],
                'wateringFrequencyDays' => 5,
                'lastWateredAt' => new \DateTimeImmutable('-2 days'),
                'acquiredAt' => new \DateTimeImmutable('-3 months'),
                'notes' => 'Attention aux courants d\'air.',
            ],
            [
                'owner' => $user2,
                'name' => 'Petite echeveria',
                'species' => $species[3],
                'wateringFrequencyDays' => 14,
                'lastWateredAt' => new \DateTimeImmutable('-7 days'),
                'acquiredAt' => new \DateTimeImmutable('-2 months'),
                'notes' => 'Très peu d\'arrosage nécessaire.',
            ],
            [
                'owner' => $user2,
                'name' => 'Orchidée blanche',
                'species' => $species[4],
                'wateringFrequencyDays' => 7,
                'lastWateredAt' => new \DateTimeImmutable('-4 days'),
                'acquiredAt' => new \DateTimeImmutable('-5 months'),
                'notes' => 'Arrosage par trempage une fois par semaine.',
            ],
        ];

        foreach ($plantsData as $data) {
            $plant = new Plant();
            $plant->setName($data['name']);
            $plant->setSpecies($data['species']);
            $plant->setOwner($data['owner']);
            $plant->setWateringFrequencyDays($data['wateringFrequencyDays']);
            $plant->setLastWateredAt($data['lastWateredAt']);
            $plant->setAcquiredAt($data['acquiredAt']);
            $plant->setNotes($data['notes']);
            $manager->persist($plant);
        }

        $manager->flush();
    }
}
