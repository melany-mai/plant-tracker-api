<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\OpenApi;

readonly class LogoutOpenApiDecorator implements OpenApiFactoryInterface
{
    public function __construct(private OpenApiFactoryInterface $decorated)
    {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        $openApi->getPaths()->addPath('/api/logout', new PathItem(
            post: new Operation(
                operationId: 'logout',
                tags: ['Auth'],
                responses: [
                    new Response(description: 'Logged out successfully'),
                    new Response(description: 'Invalid or missing token'),
                ],
                summary: 'Invalidate the refresh token and log out',
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['refresh_token'],
                                'properties' => [
                                    'refresh_token' => ['type' => 'string'],
                                ],
                            ],
                        ],
                    ]),
                ),
                security: [['bearerAuth' => []]],
            ),
        ));

        return $openApi;
    }
}
