<?php

namespace App\Tests\Controller;

use App\Controller\SecurityController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class CoreLogicTest extends TestCase
{

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
