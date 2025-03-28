<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Application;

class ExampleTest extends TestCase
{
    public function test_application_singleton(): void
    {
        $app1 = Application::getInstance();
        $app2 = Application::getInstance();

        $this->assertSame($app1, $app2);
    }

    public function test_application_config(): void
    {
        $app = Application::getInstance();
        $config = $app->getConfig();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('APP_NAME', $config);
    }
} 