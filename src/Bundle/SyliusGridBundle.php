<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\GridBundle;

use Sylius\Bundle\GridBundle\DependencyInjection\Compiler\RegisterDriversPass;
use Sylius\Bundle\GridBundle\DependencyInjection\Compiler\RegisterFieldTypesPass;
use Sylius\Bundle\GridBundle\DependencyInjection\Compiler\RegisterFiltersPass;
use Sylius\Bundle\GridBundle\DependencyInjection\Compiler\RegisterStubCommandsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SyliusGridBundle extends Bundle
{
    public const DRIVER_DOCTRINE_ORM = 'doctrine/orm';

    public const DRIVER_DOCTRINE_PHPCR_ODM = 'doctrine/phpcr-odm';

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterDriversPass());
        $container->addCompilerPass(new RegisterFiltersPass());
        $container->addCompilerPass(new RegisterFieldTypesPass());
        $container->addCompilerPass(new RegisterStubCommandsPass());
    }

    /**
     * @return string[]
     */
    public static function getAvailableDrivers(): array
    {
        return [
            self::DRIVER_DOCTRINE_ORM,
            self::DRIVER_DOCTRINE_PHPCR_ODM,
        ];
    }
}
