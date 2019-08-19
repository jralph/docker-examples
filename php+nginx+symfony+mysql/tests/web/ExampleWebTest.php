<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExampleWebTest extends WebTestCase
{
    public function testAPIWorks() {
        $client = self::createClient([]);
        $client->request('GET', '/', [], [], []);
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testAPIDoesntWork() {
        $client = self::createClient([]);
        $client->request('GET', '/error', [], [], []);
        $this->assertTrue($client->getResponse()->isServerError());
    }
}
