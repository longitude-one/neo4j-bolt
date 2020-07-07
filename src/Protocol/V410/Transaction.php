<?php
/**
 * This file is part of the LongitudeOne Neo4j Bolt driver for PHP.
 *
 * PHP version 7.2|7.3|7.4
 * Neo4j 3.0|3.5|4.0|4.1
 *
 * (c) Alexandre Tranchant <alexandre.tranchant@gmail.com>
 * (c) Longitude One 2020
 * (c) Graph Aware Limited <http://graphaware.com> 2015-2016
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GraphAware\Bolt\Protocol\V410;

use GraphAware\Bolt\Exception\MessageFailureException;
use GraphAware\Common\Cypher\Statement;
use GraphAware\Common\Transaction\TransactionInterface;
use RuntimeException;

class Transaction implements TransactionInterface
{
    const COMMITED = 'COMMITED';

    const OPENED = 'OPEN';

    const ROLLED_BACK = 'TRANSACTION_ROLLED_BACK';
    private static $NO_ROLLBACK_STATUS_CODE = 'ClientNotification';

    /**
     * @var bool
     */
    protected $closed = false;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var string|null
     */
    protected $state;

    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->session->transaction = $this;
    }

    /**
     * {@inheritdoc}
     */
    public function begin()
    {
        $this->assertNotStarted();
        $this->session->run('BEGIN');
        $this->state = self::OPENED;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $this->success();
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function isCommited()
    {
        return self::COMMITED === $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function isOpen()
    {
        return self::OPENED === $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function isRolledBack()
    {
        return self::ROLLED_BACK === $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function push($query, array $parameters = [], $tag = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        $this->assertNotClosed();
        $this->assertStarted();
        $this->session->run('ROLLBACK');
        $this->closed = true;
        $this->state = self::ROLLED_BACK;
        $this->session->transaction = null;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Statement $statement)
    {
        try {
            return $this->session->run($statement->text(), $statement->parameters(), $statement->getTag());
        } catch (MessageFailureException $e) {
            $spl = explode('.', $e->getStatusCode());
            if (self::$NO_ROLLBACK_STATUS_CODE !== $spl[1]) {
                $this->state = self::ROLLED_BACK;
                $this->closed = true;
            }
            throw $e;
        }
    }

    /**
     * @param Statement[] $statements
     *
     * @return \GraphAware\Common\Result\ResultCollection
     */
    public function runMultiple(array $statements)
    {
        $pipeline = $this->session->createPipeline();

        foreach ($statements as $statement) {
            $pipeline->push($statement->text(), $statement->parameters(), $statement->getTag());
        }

        return $pipeline->run();
    }

    /**
     * {@inheritdoc}
     */
    public function status()
    {
        return $this->getStatus();
    }

    public function success()
    {
        $this->assertNotClosed();
        $this->assertStarted();
        $this->session->run('COMMIT');
        $this->state = self::COMMITED;
        $this->closed = true;
        $this->session->transaction = null;
    }

    private function assertNotClosed()
    {
        if (false !== $this->closed) {
            throw new RuntimeException('This Transaction is closed');
        }
    }

    private function assertNotStarted()
    {
        if (null !== $this->state) {
            throw new RuntimeException(sprintf('Can not begin transaction, Transaction State is "%s"', $this->state));
        }
    }

    private function assertStarted()
    {
        if (self::OPENED !== $this->state) {
            throw new RuntimeException('This transaction has not been started');
        }
    }
}
