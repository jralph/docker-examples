<?php declare(strict_types=1);

use App\App;
use PHPUnit\Framework\TestCase;

final class AppTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $this->assertInstanceOf(
            App::class,
            new App()
        );
    }
}