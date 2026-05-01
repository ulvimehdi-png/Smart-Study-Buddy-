<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testGetUserFailsWhenNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/user');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testRegister(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'username' => 'newstudent' . uniqid(),
            'password' => 'securepassword'
        ]));

        $this->assertResponseStatusCodeSame(201);
    }
}