<?php

declare(strict_types=1);

/**
 * @author     Andreas Fieger
 */

namespace Fiedsch\TokenloginBundle\ContaoManager;

use Fiedsch\TokenloginBundle\FiedschTokenloginBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(FiedschTokenloginBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }

}