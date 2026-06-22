<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Security;

use Atoolo\WebAccount\Dto\User;
use Atoolo\WebAccount\Service\CookieJar;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AsAlias(id: 'atoolo_web_account.authenticator')]
class WebAccountAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly JWTEncoderInterface $jwtEncoder,
        private readonly DenormalizerInterface $denormalizer,
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->cookies->has(CookieJar::WEB_ACCOUNT_TOKEN_NAME);
    }

    /**
     * @throws ExceptionInterface
     */
    public function authenticate(Request $request): Passport
    {
        $token = $request->cookies->get(CookieJar::WEB_ACCOUNT_TOKEN_NAME);
        if (!$token || !is_string($token)) {
            throw new CustomUserMessageAuthenticationException('No token provided');
        }

        try {
            $payload = $this->jwtEncoder->decode($token);
        } catch (JWTDecodeFailureException $e) {
            throw new CustomUserMessageAuthenticationException($e->getReason());
        }

        if (!$payload || !isset($payload['username'], $payload['id'], $payload['lastName'])) {
            throw new CustomUserMessageAuthenticationException('Invalid token');
        }

        /** @var User $user */
        $user = $this->denormalizer->denormalize($payload, User::class);

        return new SelfValidatingPassport(new UserBadge($user->getUsername(), fn() => $user));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }

}
