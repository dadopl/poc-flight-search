<?php

declare(strict_types=1);

namespace App\Tests;

use App\Kernel;
use PHPUnit\Framework\TestCase;

class KernelBootTest extends TestCase
{
    public function testKernelBoots(): void
    {
        $kernel = new Kernel('test', false);
        $kernel->boot();

        $this->assertSame('test', $kernel->getEnvironment());
        $this->assertFalse($kernel->isDebug());

        $kernel->shutdown();
    }
}
