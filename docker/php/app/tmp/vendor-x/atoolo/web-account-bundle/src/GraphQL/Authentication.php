<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\GraphQL;

use Atoolo\WebAccount\Dto\AuthenticationResult;
use Atoolo\WebAccount\Dto\AuthenticationStatus;
use Atoolo\WebAccount\Service\Authentication\UsernamePasswordAuthentication;
use Atoolo\WebAccount\Service\CookieJar;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[GQL\Provider]
class Authentication
{
    public function __construct(
        private readonly UsernamePasswordAuthentication $usernamePasswordAuthentication,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly NormalizerInterface $normalizer,
        private readonly CookieJar $cookieJar,
        #[Autowire('%atoolo_web_account.token_ttl%')]
        private readonly int $tokenTtl,
    ) {}

    /**
     * @throws ExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \JsonException
     */
    #[GQL\Mutation(name: 'webAccountAuthenticationWithPassword', type: 'AuthenticationResult!')]
    public function authenticationWithPassword(
        string $username,
        string $password,
        bool $setJwtCookie,
    ): AuthenticationResult {
        $result = $this->usernamePasswordAuthentication->authenticate($username, $password);

        if ($setJwtCookie && $result->status === AuthenticationStatus::SUCCESS && $result->user !== null) {
            /** @var array<string,mixed> $userData */
            $userData = $this->normalizer->normalize($result->user);
            $customPayload = array_merge(
                $userData,
                [
                    'exp' => time() + $this->tokenTtl,
                    'roles' => array_merge(
                        ['ROLE_WEB_ACCOUNT'],
                        array_map(
                            fn($role) => 'ROLE_' . $role,
                            $result->user->getRoles(),
                        ),
                    ),
                ],
            );
            $jwt = $this->jwtManager->createFromPayload($result->user, $customPayload);
            $cookie = Cookie::create(CookieJar::WEB_ACCOUNT_TOKEN_NAME)
                ->withValue($jwt)
                ->withExpires(time() + $this->tokenTtl)
                ->withHttpOnly(true)
                ->withSecure(true)
                ->withPath('/')
                ->withSameSite('strict');

            $this->cookieJar->add($cookie);
        }

        return $result;
    }

    #[GQL\Mutation(name: 'webAccountUnsetJwtCookie', type: 'Boolean!')]
    public function unsetJwtCookie(): bool
    {
        $cookie = Cookie::create(CookieJar::WEB_ACCOUNT_TOKEN_NAME)
            ->withValue('')
            ->withExpires(1)
            ->withHttpOnly(true)
            ->withSecure(true)
            ->withPath('/')
            ->withSameSite('strict');

        $this->cookieJar->add($cookie);
        return true;
    }

}
