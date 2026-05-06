<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Session;
use App\LiveTimerSession\Enum\SessionStatus;
use App\LiveTimerSession\Enum\SessionType;

class SessionCoverageTest extends TestCase
{
    public function testSessionGettersAndSetters(): void
    {
        $session = new Session();
        
        $session->setStatus(SessionStatus::from('completed'));
        $this->assertSame(SessionStatus::from('completed'), $session->getStatus());

        $session->setSessionType(SessionType::from('pomodoro'));
        $this->assertSame(SessionType::from('pomodoro'), $session->getSessionType());

        $this->assertNull($session->getId());
    }
}