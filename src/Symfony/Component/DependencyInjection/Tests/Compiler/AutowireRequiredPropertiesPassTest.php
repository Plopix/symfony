<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Tests\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\AutowireRequiredPropertiesPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveClassPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once __DIR__.'/../Fixtures/includes/autowiring_classes.php';

class AutowireRequiredPropertiesPassTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        if (\PHP_VERSION_ID >= 70400) {
            require_once __DIR__.'/../Fixtures/includes/autowiring_classes_74.php';
        }
    }

    public function testInjection()
    {
        if (\PHP_VERSION_ID < 70400) {
            $this->assertTrue(true);

            return;
        }

        $container = new ContainerBuilder();
        $container->register(Bar::class);
        $container->register(A::class);
        $container->register(B::class);
        $container->register(PropertiesInjection::class)->setAutowired(true);

        (new ResolveClassPass())->process($container);
        (new AutowireRequiredPropertiesPass())->process($container);

        $properties = $container->getDefinition(PropertiesInjection::class)->getProperties();

        $this->assertArrayHasKey('plop', $properties);
        $this->assertEquals(Bar::class, (string) $properties['plop']);
    }
}
