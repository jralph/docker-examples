<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExampleWebTest extends WebTestCase {
    /**
     * @dataProvider urlApiProvider
     */
    public function testAPIWorks($url) {
        $client = self::createClient([]);
        $client->request('GET', $url, [], [], []);
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function urlApiProvider() {
        yield ['/'];
    }
}
