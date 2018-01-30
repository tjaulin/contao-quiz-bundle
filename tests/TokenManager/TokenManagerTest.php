<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test\Choice;

use Contao\Config;
use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Contao\ManagerPlugin\PluginLoader;
use Contao\Model;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Firebase\JWT\JWT;
use HeimrichHannot\QuizBundle\Manager\TokenManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class TokenManagerTest extends ContaoTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getFixturesDir());
        }

        $router = $this->createRouterMock();
        $requestStack = $this->createRequestStackMock();
        $framework = $this->mockContaoFramework($this->createMockAdapater());

        $database = $this->createMock(Connection::class);
        $container = $this->mockContainer();
        $container->set('kernel', $this->createMock(ContaoKernel::class));
        $container->setParameter('secret', Config::class);
        $container->set('request_stack', $requestStack);
        $container->set('router', $router);
        $container->set('contao.framework', $framework);
        $container->set('database_connection', $database);
        System::setContainer($container);
    }

    /**
     * @return array
     */
    public function testAddDataToJwtToken()
    {
        $tokenManager = new TokenManager();
        $encode = JWT::encode(['id' => 12], System::getContainer()->getParameter('secret'));
        $token = $tokenManager->getDataFromJwtToken($encode);

        $this->assertSame(12, $token->id);
    }

    public function createRouterMock()
    {
        $router = $this->createMock(RouterInterface::class);
        $router->method('generate')->with('contao_backend', $this->anything())->will($this->returnCallback(function ($route, $params = []) {
            $url = '/contao';
            if (!empty($params)) {
                $count = 0;
                foreach ($params as $key => $value) {
                    $url .= (0 === $count ? '?' : '&');
                    $url .= $key.'='.$value;
                    ++$count;
                }
            }

            return $url;
        }));

        return $router;
    }

    public function createRequestStackMock()
    {
        $requestStack = new RequestStack();
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->attributes->set('_contao_referer_id', 'foobar');
        $requestStack->push($request);

        return $requestStack;
    }

    public function createMockAdapater()
    {
        $modelAdapter = $this->mockAdapter(['__construct']);

        return [Model::class => $modelAdapter];
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'Fixtures';
    }

    /**
     * Mocks the plugin loader.
     *
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedRecorder $expects
     * @param array                                                 $plugins
     *
     * @return PluginLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockPluginLoader(\PHPUnit_Framework_MockObject_Matcher_InvokedRecorder $expects, array $plugins = [])
    {
        $pluginLoader = $this->createMock(PluginLoader::class);

        $pluginLoader->expects($expects)->method('getInstancesOf')->with(PluginLoader::EXTENSION_PLUGINS)->willReturn($plugins);

        return $pluginLoader;
    }
}
