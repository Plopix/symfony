<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\TypedReference;

/**
 * Looks for definitions with autowiring enabled and registers their corresponding "@required" properties.
 *
 * @author Sebastien Morel (Plopix) <morel.seb@gmail.com>
 * @author Nicolas Grekas <p@tchwork.com>
 */
class AutowireRequiredPropertiesPass extends AbstractRecursivePass
{
    /**
     * {@inheritdoc}
     */
    protected function processValue($value, bool $isRoot = false)
    {
        if (\PHP_VERSION_ID < 70400) {
            return $value;
        }
        $value = parent::processValue($value, $isRoot);

        if (!$value instanceof Definition || !$value->isAutowired() || $value->isAbstract() || !$value->getClass()) {
            return $value;
        }
        if (!$reflectionClass = $this->container->getReflectionClass($value->getClass(), false)) {
            return $value;
        }

        $properties = $value->getProperties();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if (false === $doc = $reflectionProperty->getDocComment()) {
                continue;
            }
            if (false === stripos($doc, '@required') &&
                preg_match('#(?:^/\*\*|\n\s*+\*)\s*+@required(?:\s|\*/$)#i', $doc)) {
                continue;
            }
            if (!\array_key_exists($reflectionProperty->getName(), $properties)) {
                $type = $reflectionProperty->getType()->getName();
                $value->setProperty(
                    $reflectionProperty->getName(),
                    new TypedReference($type, $type, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $reflectionProperty->getName())
                );
            }
        }

        return $value;
    }
}
