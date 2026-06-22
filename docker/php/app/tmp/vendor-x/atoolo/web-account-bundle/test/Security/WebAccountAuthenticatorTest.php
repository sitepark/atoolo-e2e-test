<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Test\Security;

use Atoolo\WebAccount\Dto\User;
use Atoolo\WebAccount\Security\WebAccountAuthenticator;
use Atoolo\WebAccount\Service\CookieJar;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[CoversClass(WebAccountAuthenticator::class)]
class WebAccountAuthenticatorTest extends TestCase
{
    private readonly JWTEncoderInterface $jwtEncoder;
    private readonly DenormalizerInterface $denormalizer;

    private readonly WebAccountAuthenticator $authenticator;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->jwtEncoder = $this->createMock(JWTEncoderInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
        $this->authenticator = new WebAccountAuthenticator($this->jwtEncoder, $this->denormalizer);
    }

    /**
     * @throws Exception
     */
    public function testSupports(): void
    {
        $request = new Request([], [], [], [CookieJar::WEB_ACCOUNT_TOKEN_NAME => 'abc']);
        $this->assertTrue($this->authenticator->supports($request));
    }

    public function testAuthenticateWithMissingToken(): void
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->authenticator->authenticate(new Request());
    }

    public function testAuthenticateWithExpiredToken(): void
    {
        $request = new Request([], [], [], [CookieJar::WEB_ACCOUNT_TOKEN_NAME => 'abc']);
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->jwtEncoder->method('decode')->willThrowException(new JWTDecodeFailureException('', ''));
        $this->authenticator->authenticate($request);
    }

    public function testAuthenticateWithRequiredPayload(): void
    {
        $request = new Request([], [], [], [CookieJar::WEB_ACCOUNT_TOKEN_NAME => 'abc']);
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->jwtEncoder->method('decode')->willReturn(['id' => '1']);
        $this->authenticator->authenticate($request);
    }

    /**
     * @throws Exception
     */
    public function testAuthenticate(): void
    {
        $payload = [
            'id' => '1',
            'username' => 'peterpan',
            'firstName' => 'Peter',
            'lastName' => 'Pan',
            'email' => 'peterpan@neverland.com',
        ];

        $user = new User(
            '1',
            'peterpan',
            'Peter',
            'Pan',
            'peterpan@neverland.com',
            ['A'],
        );

        $request = new Request([], [], [], [CookieJar::WEB_ACCOUNT_TOKEN_NAME => 'abc']);
        $this->jwtEncoder->method('decode')->willReturn($payload);
        $this->denormalizer->expects($this->once())->method('denormalize')->with($payload)->willReturn($user);

        $passport = $this->authenticator->authenticate($request);

        $this->assertSame($user, $passport->getUser(), 'The user should be the one we created from the payload');
    }

    public function testOnAuthenticationSuccess(): void
    {
        $request = $this->createMock(Request::class);
        $token = $this->createMock(TokenInterface::class);
        $firewallName = 'webAccount';

        $response = $this->authenticator->onAuthenticationSuccess($request, $token, $firewallName);
        $this->assertNull($response);
    }

    public function testOnAuthenticationFailure(): void
    {
        $request = $this->createMock(Request::class);
        $exception = $this->createMock(AuthenticationException::class);

        $response = $this->authenticator->onAuthenticationFailure($request, $exception);
        $this->assertNull($response);
    }

}
