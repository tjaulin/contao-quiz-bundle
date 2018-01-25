<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Entity;


use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class QuizSession
{
    const SCORE_NAME          = 'score';
    const USED_QUESTIONS_NAME = 'usedQuestions';

    /**
     * Symfony session object.
     *
     * @var SessionInterface
     */
    protected $session;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct()
    {
        $this->session = \System::getContainer()->get('session');
    }

    /**
     * Set the filter data for a given filter key.
     *
     * @param string $key
     * @param mixed  $data
     */
    public function setData(string $key, $data)
    {
        $this->session->set($key, $data);
    }

    /**
     * Get the filter data for a given key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getData(string $key)
    {
        $data = [];

        if ($this->session->has($key)) {
            $data = $this->session->get($key);
        }

        return $data;
    }

    /**
     * Has the filter data for a given key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasData(string $key): bool
    {
        return !empty($this->getData($key));
    }

    /**
     * Reset the filter data for a given key.
     *
     * @param string $key
     */
    public function reset(string $key)
    {
        if ($this->session->has($key)) {
            $this->session->remove($key);
        }
    }

    /**
     * increase the score +1
     */
    public function increaseScore()
    {
        $score = $this->getData(static::SCORE_NAME);
        if (empty($score)) {
            $score = 1;
        } else {
            $score = $score + 1;
        }
        $this->setData(static::SCORE_NAME, $score);
    }
}