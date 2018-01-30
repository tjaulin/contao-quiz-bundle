<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\DelegatingParser;
use HeimrichHannot\QuizBundle\ContaoManager\Plugin;
use HeimrichHannot\QuizBundle\HeimrichHannotContaoQuizBundle;
use PHPUnit\Framework\TestCase;

/**
 * Test the plugin class
 * Class PluginTest
 *
 * @package HeimrichHannot\QuizBundle\Test\ContaoManager
 */
class PluginTest extends TestCase
{
    public function testInstantiation()
    {
        static::assertInstanceOf(Plugin::class, new Plugin());
    }

    public function testGetBundles()
    {
        $plugin = new Plugin();

        /** @var BundleConfig[] $bundles */
        $bundles = $plugin->getBundles(new DelegatingParser());

        static::assertCount(1, $bundles);
        static::assertInstanceOf(BundleConfig::class, $bundles[0]);
        static::assertEquals(HeimrichHannotContaoQuizBundle::class, $bundles[0]->getName());
        static::assertEquals([ContaoCoreBundle::class, 'submissions_creator', 'submissions'], $bundles[0]->getLoadAfter());
    }
}