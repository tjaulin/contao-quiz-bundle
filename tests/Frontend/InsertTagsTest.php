<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Test\Frontend;

use Contao\Config;
use Contao\Controller;
use Contao\Model;
use Contao\ModuleModel;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Firebase\JWT\JWT;
use HeimrichHannot\QuizBundle\Frontend\InsertTags;
use HeimrichHannot\QuizBundle\Manager\QuizQuestionManager;
use HeimrichHannot\QuizBundle\Manager\TokenManager;
use HeimrichHannot\QuizBundle\Model\QuizQuestionModel;
use HeimrichHannot\Request\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Tests\Logger;

class InsertTagsTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();

        if (!defined('TL_ROOT')) {
            \define('TL_ROOT', __DIR__);
        }

        $GLOBALS['FE_MOD']['quiz'] = [
            'quiz' => 'HeimrichHannot\QuizBundle\Module\ModuleQuizReader',
            'quizSubmission' => 'HeimrichHannot\QuizBundle\Module\ModuleQuizSubmission',
        ];

        if (!defined('TL_MODE')) {
            \define('TL_MODE', 'FE');
        }

        if (!defined('TL_ERROR')) {
            \define('TL_ERROR', 'ERROR');
        }

        $quizQuestionManager = new QuizQuestionManager($this->mockContaoFramework([QuizQuestionModel::class => $this->getQuizQuestionAdapter()]));

        $database = $this->createMock(Connection::class);
        $container = $this->mockContainer();
        $container->set('session', new Session(new MockArraySessionStorage()));
        $container->set('database_connection', $database);
        $container->set('huh.quiz.question.manager', $quizQuestionManager);
        $container->set('monolog.logger.contao', new Logger());
        $container->set('contao.framework', $this->mockContaoFramework($this->createMockAdapter()));
        $container->setParameter('secret', Config::class);

        $framework = $this->mockContaoFramework($this->createMockAdapter());
        $tokenManager = new TokenManager($framework);
        $container->set('huh.quiz.token.manager', $tokenManager);

        System::setContainer($container);
    }

    public function testQuizInsertTags()
    {
        $insertTag = new InsertTags();
        $framework = $this->mockContaoFramework($this->createMockAdapter());
        $tokenManager = new TokenManager($framework);
        $encode = JWT::encode(['session' => ''], System::getContainer()->getParameter('secret'));
        $token = $tokenManager->increaseScore($encode, 1);
        Request::setGet('token', $token);

        $resultCurrentScore = $insertTag->quizInsertTags(InsertTags::CURRENT_SCORE);
        $resultTotalScore = $insertTag->quizInsertTags(InsertTags::TOTAL_SCORE.'::1');
        $resultFalse = $insertTag->quizInsertTags('bla');
        $resultQuiz = $insertTag->quizInsertTags(InsertTags::QUIZ.'::2::3');

        $this->assertSame(1, $resultCurrentScore);
        $this->assertSame(2, $resultTotalScore);
        $this->assertFalse($resultFalse);
        $this->assertSame('', $resultQuiz);
    }

    public function testGetQuiz()
    {
        $insertTag = new InsertTags();

        $quiz = $insertTag->getQuiz(1, 2);
        $this->assertSame('', $quiz);
    }

    public function createMockAdapter()
    {
        $modelAdapter = $this->mockAdapter(['__construct']);

        return [Model::class => $modelAdapter, ModuleModel::class => $this->getModuleAdapter(), Controller::class => $this->getControllerAdapter()];
    }

    public function getModuleAdapter()
    {
        $moduleMock = $this->mockClassWithProperties(ModuleModel::class, ['id' => 1]);
        $moduleAdapter = $this->mockAdapter(['findByIdOrAlias']);
        $moduleAdapter->method('findByIdOrAlias')->willReturn($moduleMock);

        return $moduleAdapter;
    }

    public function getControllerAdapter()
    {
        $controllerAdapter = $this->mockAdapter(['getFrontendModule']);
        $controllerAdapter->method('getFrontendModule')->willReturn('');

        return $controllerAdapter;
    }

    public function getQuizQuestionAdapter()
    {
        $quizQuestionAdapter = $this->mockAdapter(['countByPid', 'countBy']);
        $quizQuestionAdapter->method('countByPid')->willReturn(2);
        $quizQuestionAdapter->method('countBy')->willReturn(2);

        return $quizQuestionAdapter;
    }
}
