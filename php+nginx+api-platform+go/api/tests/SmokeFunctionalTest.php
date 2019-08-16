<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SmokeFunctionalTest extends WebTestCase {
    /**
     * @dataProvider urlApiProvider
     */
    public function testAPIWorks($url) {
        $client = self::createClient([]);
        $client->request('GET', $url, [], [], ['HTTP_X_AUTH_TOKEN' => 'test_api_key', 'HTTP_ACCEPT' => 'application/json']);
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function urlApiProvider() {
        yield ['/api/articles'];
        yield ['/api/comments'];
    }
}
