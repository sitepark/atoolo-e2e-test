<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Test\GraphQL;

use Atoolo\WebAccount\Dto\AuthenticationResult;
use Atoolo\WebAccount\Dto\AuthenticationStatus;
use Atoolo\WebAccount\Dto\User;
use Atoolo\WebAccount\GraphQL\Authentication;
use Atoolo\WebAccount\Service\Authentication\UsernamePasswordAuthentication;
use Atoolo\WebAccount\Service\CookieJar;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[CoversClass(Authentication::class)]
class AuthenticationTest extends TestCase
{
    private readonly UsernamePasswordAuthentication $usernamePasswordAuthentication;
    private readonly JWTTokenManagerInterface $jwtManager;
    private readonly NormalizerInterface $normalizer;
    private readonly CookieJar $cookieJar;
    private readonly Authentication $authentication;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->usernamePasswordAuthentication = $this->createMock(UsernamePasswordAuthentication::class);
        $this->jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->cookieJar = new CookieJar();
        $this->authentication = new Authentication(
            $this->usernamePasswordAuthentication,
            $this->jwtManager,
            $this->normalizer,
            $this->cookieJar,
            100,
        );
    }

    /**
     * @throws Exception
     */
    public function testAuthenticationWithPassword(): void
    {
        $user = new User(
            '1',
            'peterpan',
            'Peter',
            'Pan',
            'peterpan@neverland.com',
            ['A', 'B'],
        );

        $expectedResult = new AuthenticationResult(
            status: AuthenticationStatus::SUCCESS,
            user: $user,
        );

        $this->usernamePasswordAuthentication->expects($this->once())
            ->method('authenticate')
            ->with('testuser', 'testpassword')
            ->willReturn($expectedResult);

        $this->normalizer->expects($this->once())
            ->method('normalize')
            ->with($user)
            ->willReturn([
                'id' => '1',
                'username' => 'peterpan',
                'firstName' => 'Peter',
                'lastName' => 'Pan',
                'email' => 'peterpan@neverland.com',
            ]);

        $this->jwtManager->expects($this->once())
            ->method('createFromPayload')
            ->willReturn('abc');

        $result = $this->authentication->authenticationWithPassword('testuser', 'testpassword', true);

        $this->assertSame($expectedResult, $result);
        $this->assertEquals('abc', $this->cookieJar->all()[0]->getValue(), "unexpected cookie value");
    }

    public function testUnsetJwtCookie(): void
    {
        $this->authentication->unsetJwtCookie();
        $this->assertLessThan(time(), $this->cookieJar->all()[0]->getExpiresTime(), "Cookie should have expired");
    }
}
