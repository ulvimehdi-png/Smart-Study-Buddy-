<?php

namespace App\Tests\Controller;

use App\Controller\DevMercureController;
use App\Controller\SecurityController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class CoreLogicTest extends TestCase
{
    public function testDevMercurePingReturnsValidPayload(): void
    {
        $hub = $this->createMock(HubInterface::class);
        
        $hub->expects($this->once())
            ->method('publish')
            ->with($this->isInstanceOf(Update::class));

        $controller = new DevMercureController();
        // Give it the blank container so it can return JSON
        $controller->setContainer(new Container());
        $response = $controller->ping($hub);

        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['ok']);
        $this->assertSame('http://localhost/timer/test', $content['topic']);
        $this->assertArrayHasKey('sentAt', $content['payload']);
    }

    public function testSecurityLoginIsHandledByFirewall(): void
    {
        $controller = new SecurityController();
        $this->expectException(\LogicException::class);
        $controller->login();
    }

    public function testSecurityLogoutIsHandledByFirewall(): void
    {
        $controller = new SecurityController();
        $this->expectException(\LogicException::class);
        $controller->logout();
    }
}