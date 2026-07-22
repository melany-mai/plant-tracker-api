<?php

namespace App\Tests\Api;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    /**
     * Creates ONE client per test and stores it as $this->client.
     *
     * Symfony 8's WebTestCase enforces a single kernel boot per test:
     * createClient() is the only allowed boot point. All helper methods
     * (getToken, jsonRequest) must reuse $this->client instead of
     * creating new ones, otherwise a second boot triggers a LogicException.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Purges the test database and loads all fixtures.
     *
     * Must be called AFTER parent::setUp() because static::getContainer()
     * requires the kernel to be booted (which createClient() does in setUp).
     * static::getContainer() returns the test container, which exposes all
     * services including private ones like AppFixtures.
     */
    protected function loadFixtures(): void
    {
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        $executor = new ORMExecutor($em, new ORMPurger($em));
        $executor->execute([$container->get(AppFixtures::class)]);
    }

    /**
     * Generates a JWT token for the given user directly via Lexik's JWT manager.
     *
     * We deliberately avoid calling POST /auth here. The RateLimitSubscriber
     * blocks that endpoint after a few requests from the same IP, which would
     * cause all tests beyond the first couple to fail with 429.
     *
     * Going through the service is also faster (no HTTP round-trip) and tests
     * the API endpoints in isolation from the authentication endpoint.
     */
    protected function getToken(string $email): string
    {
        $container = static::getContainer();

        $user = $container->get(UserRepository::class)->findOneBy(['email' => $email]);
        self::assertNotNull($user, sprintf('User "%s" not found in database.', $email));

        /** @var \Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface $jwtManager */
        $jwtManager = $container->get('lexik_jwt_authentication.jwt_manager');

        return $jwtManager->create($user);
    }

    /**
     * Makes a JSON request and returns status code + decoded body.
     *
     * Reuses $this->client — no second kernel boot.
     *
     * @param array<string, mixed>|null $body
     *
     * @return array{status: int, data: array<mixed>|null, body: string}
     */
    protected function jsonRequest(
        string $method,
        string $url,
        ?string $token = null,
        ?array $body = null,
    ): array {
        $headers = [
            'CONTENT_TYPE' => 'application/ld+json',
            'HTTP_ACCEPT' => 'application/ld+json',
        ];

        if (null !== $token) {
            $headers['HTTP_AUTHORIZATION'] = 'Bearer '.$token;
        }

        $this->client->request(
            $method,
            $url,
            [],
            [],
            $headers,
            null !== $body ? json_encode($body) : null,
        );

        $content = $this->client->getResponse()->getContent();

        return [
            'status' => $this->client->getResponse()->getStatusCode(),
            'data' => json_decode($content, true),
            'body' => $content, // raw body — used in assertion messages for diagnosis
        ];
    }
}
