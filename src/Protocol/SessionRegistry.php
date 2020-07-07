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

namespace GraphAware\Bolt\Protocol;

use GraphAware\Bolt\IO\AbstractIO;
use GraphAware\Common\Driver\SessionInterface;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SessionRegistry
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;
    /**
     * @var AbstractIO
     */
    protected $io;

    /**
     * @var array
     */
    protected $sessions = [];

    public function __construct(AbstractIO $io, EventDispatcherInterface $dispatcher)
    {
        $this->io = $io;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return SessionInterface
     */
    public function getSession(int $version, array $credentials)
    {
        if (!$this->supportsVersion($version)) {
            throw new InvalidArgumentException(sprintf('No session registered supporting Version %d', $version));
        }
        $class = $this->sessions[$version];

        return new $class($this->io, $this->dispatcher, $credentials);
    }

    /**
     * @return array
     */
    public function getSupportedVersions()
    {
        return array_keys($this->sessions);
    }

    /**
     * @param SessionInterface $sessionClass
     */
    public function registerSession($sessionClass)
    {
        $version = (int) $sessionClass::getProtocolVersion();

        if (array_key_exists($version, $this->sessions)) {
            throw new RuntimeException(sprintf('There is already a Session registered for supporting Version#%d', $version));
        }

        $this->sessions[$version] = $sessionClass;
    }

    /**
     * @param int $version
     *
     * @return bool
     */
    public function supportsVersion($version)
    {
        return array_key_exists((int) $version, $this->sessions);
    }
}
