<?php

namespace Auth\Core;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../src/auth/core/functions.php';

class FunctionsTest extends TestCase
{
    public function testGetHelloWorld(): void
    {
        $this->assertSame('Hello, World!', getHelloWorld());
    }
}