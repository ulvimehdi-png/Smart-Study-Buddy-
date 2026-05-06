<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\LoginFailureHandler;
use App\Security\LoginSuccessHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class LoginHandlerTest extends TestCase
{
    public function testLoginSuccessHandlerReturnsCorrectJson(): void
    {
        $user = new User();
        $user->setUsername('student_login_test');

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $request = new Request();
        $handler = new LoginSuccessHandler();
        
        // Triggers the logic in LoginSuccessHandler[cite: 29]
        $response = $handler->onAuthenticationSuccess($request, $token);

        $this->assertSame(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('user', $content);
        $this->assertSame('student_login_test', $content['user']['username']);
    }

    public function testLoginFailureHandlerReturns401(): void
    {
        $request = new Request();
        $exception = new AuthenticationException('Bad credentials');
        $handler = new LoginFailureHandler();
        
        // Triggers the logic in LoginFailureHandler[cite: 28]
        $response = $handler->onAuthenticationFailure($request, $exception);

        $this->assertSame(401, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertSame('Invalid credentials', $content['error']);
    }
}