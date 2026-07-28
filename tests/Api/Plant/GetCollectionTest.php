<?php

namespace App\Tests\Api\Plant;

use App\Tests\Api\AbstractApiTestCase;

/**
 * Functional tests for GET /api/plants (collection endpoint).
 *
 * Fixture state (loaded once for the whole class):
 *   - admin@plant.dev  → ROLE_ADMIN, 0 plants
 *   - user1@plant.dev  → ROLE_USER,  3 plants (Ma Monstera, Pothos du bureau, Ficus du salon)
 *   - user2@plant.dev  → ROLE_USER,  2 plants (Petite echeveria, Orchidée blanche)
 */
class GetCollectionTest extends AbstractApiTestCase
{
    // -------------------------------------------------------------------------
    // Setup
    // -------------------------------------------------------------------------

    /**
     * Flag to load fixtures only once for the whole class.
     *
     * We can't use setUpBeforeClass() for fixtures in Symfony 8 because
     * static::getContainer() requires the kernel to be booted first, and
     * createClient() (the only allowed boot point) runs in setUp().
     *
     * So we load fixtures in the first setUp() call and skip it for the rest.
     * All tests here are read-only, so no test can corrupt the shared state.
     */
    private static bool $fixturesLoaded = false;

    protected function setUp(): void
    {
        parent::setUp(); // boots kernel via createClient(), sets $this->client

        if (!self::$fixturesLoaded) {
            $this->loadFixtures();
            self::$fixturesLoaded = true;
        }
    }

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    /**
     * Without a token the JWT firewall must reject the request immediately.
     * The application logic (PlantProvider, PlantOwnerExtension) is never reached.
     */
    public function testUnauthenticatedRequestReturns401(): void
    {
        $result = $this->jsonRequest('GET', '/api/plants');

        $this->assertSame(401, $result['status']);
    }

    // -------------------------------------------------------------------------
    // Ownership isolation (core behaviour of PlantProvider / PlantOwnerExtension)
    // -------------------------------------------------------------------------

    /**
     * user1 has exactly 3 plants in the fixtures.
     * The collection must contain only those, no more, no less.
     */
    public function testUser1SeesOnlyHisOwnPlants(): void
    {
        $token = $this->getToken('user1@plant.dev');
        $result = $this->jsonRequest('GET', '/api/plants', $token);

        $this->assertSame(200, $result['status'], $result['body']);
        $this->assertArrayHasKey('member', $result['data'] ?? [], $result['body']);
        $this->assertCount(3, $result['data']['member']);
    }

    /**
     * user2 has exactly 2 plants in the fixtures.
     */
    public function testUser2SeesOnlyHisOwnPlants(): void
    {
        $token = $this->getToken('user2@plant.dev');
        $result = $this->jsonRequest('GET', '/api/plants', $token);

        $this->assertSame(200, $result['status'], $result['body']);
        $this->assertArrayHasKey('member', $result['data'] ?? [], $result['body']);
        $this->assertCount(2, $result['data']['member']);
    }

    /**
     * Count alone is not enough: a bug could return the right number of plants
     * but belonging to the wrong user. This test compares the two collections
     * directly to ensure there is zero overlap between them.
     */
    public function testPlantCollectionsDoNotOverlapBetweenUsers(): void
    {
        $tokenUser1 = $this->getToken('user1@plant.dev');
        $tokenUser2 = $this->getToken('user2@plant.dev');

        $resultUser1 = $this->jsonRequest('GET', '/api/plants', $tokenUser1);
        $resultUser2 = $this->jsonRequest('GET', '/api/plants', $tokenUser2);

        $user1Names = array_column($resultUser1['data']['member'], 'name');
        $user2Names = array_column($resultUser2['data']['member'], 'name');

        $this->assertEmpty(
            array_intersect($user1Names, $user2Names),
            'Plants from user1 and user2 must never appear in each other\'s collection.',
        );
    }

    // -------------------------------------------------------------------------
    // Response structure (PlantOutput DTO contract)
    // -------------------------------------------------------------------------

    /**
     * Verifies that PlantOutput::fromEntity() exposes every expected field.
     *
     * We pick the first plant of user1 (Ma Monstera) which has notes set,
     * and check all non-nullable fields are present.
     * Nullable fields (photo, notes) are omitted by the serializer when null,
     * so we only assert them on a plant where we know they are set.
     */
    public function testResponseStructureMatchesPlantOutputDto(): void
    {
        $token = $this->getToken('user1@plant.dev');
        $result = $this->jsonRequest('GET', '/api/plants', $token);

        $this->assertSame(200, $result['status'], $result['body']);
        $this->assertNotEmpty($result['data']['member'], $result['body']);

        // "Ma Monstera" is the first plant and has notes set — use it for structure checks
        $plant = $result['data']['member'][0];

        // Always-present scalar fields
        $this->assertArrayHasKey('id', $plant);
        $this->assertArrayHasKey('name', $plant);
        $this->assertArrayHasKey('wateringFrequencyDays', $plant);

        // Date fields serialised as ISO 8601 strings
        $this->assertArrayHasKey('lastWateredAt', $plant);
        $this->assertArrayHasKey('acquiredAt', $plant);
        $this->assertArrayHasKey('createdAt', $plant);
        $this->assertArrayHasKey('updatedAt', $plant);

        // Species fields denormalised directly in the output (no nested object)
        $this->assertArrayHasKey('speciesId', $plant);
        $this->assertArrayHasKey('speciesCommonName', $plant);
        $this->assertArrayHasKey('speciesLatinName', $plant);

        // "notes" is set on Ma Monstera — it must appear in the response
        $this->assertArrayHasKey('notes', $plant);
        $this->assertIsString($plant['notes']);
    }

    // -------------------------------------------------------------------------
    // Edge case: authenticated user with no plants
    // -------------------------------------------------------------------------

    /**
     * The admin has ROLE_ADMIN which implies ROLE_USER, so access is granted.
     * But the admin owns no plants in the fixtures.
     * Expected: 200 with an empty collection, not a 403 or 404.
     */
    public function testAuthenticatedUserWithNoPlantsReceivesEmptyCollection(): void
    {
        $token = $this->getToken('admin@plant.dev');
        $result = $this->jsonRequest('GET', '/api/plants', $token);

        $this->assertSame(200, $result['status']);
        $this->assertCount(0, $result['data']['member']);
        $this->assertSame(0, $result['data']['totalItems']);
    }
}
