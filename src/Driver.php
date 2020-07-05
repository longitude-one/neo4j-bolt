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

namespace GraphAware\Bolt;

use GraphAware\Bolt\Exception\HandshakeException;
use GraphAware\Bolt\Exception\IOException;
use GraphAware\Bolt\IO\StreamSocket;
use GraphAware\Bolt\Protocol\SessionRegistry;
use GraphAware\Bolt\Protocol\V1\Session;
use GraphAware\Common\Driver\DriverInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Driver implements DriverInterface
{
    const DEFAULT_TCP_PORT = 7687;
    const VERSION = '1.5.4';

    /**
     * @var array
     */
    protected $credentials;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var StreamSocket
     */
    protected $io;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var SessionRegistry
     */
    protected $sessionRegistry;

    /**
     * @var bool
     */
    protected $versionAgreed = false;

    /**
     * @param string $uri
     */
    public function __construct($uri, Configuration $configuration = null)
    {
        $this->credentials = null !== $configuration ? $configuration->getValue('credentials', []) : [];
        /*
        $ctx = stream_context_create(array());
        define('CERTS_PATH',
        '/Users/ikwattro/dev/_graphs/3.0-M02-NIGHTLY/conf');
        $ssl_options = array(
            'cafile' => CERTS_PATH . '/cacert.pem',
            'local_cert' => CERTS_PATH . '/ssl/snakeoil.pem',
            'peer_name' => 'example.com',
            'allow_self_signed' => true,
            'verify_peer' => true,
            'capture_peer_cert' => true,
            'capture_peer_cert_chain' => true,
            'disable_compression' => true,
            'SNI_enabled' => true,
            'verify_depth' => 1
        );
        foreach ($ssl_options as $k => $v) {
            stream_context_set_option($ctx, 'ssl', $k, $v);
        }
        */

        $config = null !== $configuration ? $configuration : Configuration::create();
        $parsedUri = parse_url($uri);
        $host = isset($parsedUri['host']) ? $parsedUri['host'] : $parsedUri['path'];
        $port = isset($parsedUri['port']) ? $parsedUri['port'] : static::DEFAULT_TCP_PORT;
        $this->dispatcher = new EventDispatcher();
        $this->io = StreamSocket::withConfiguration($host, $port, $config, $this->dispatcher);
        $this->sessionRegistry = new SessionRegistry($this->io, $this->dispatcher);
        $this->sessionRegistry->registerSession(Session::class);
    }

    /**
     * @return string
     */
    public static function getUserAgent()
    {
        return 'GraphAware-BoltPHP/'.self::VERSION;
    }

    /**
     * @throws HandshakeException when server isn't compatible with Bolt 1.0 nor Bolt 4.1
     * @throws IOException
     */
    public function handshake(): int
    {
        if (!$this->io->isConnected()) {
            $this->io->reconnect();
        }

        //Version 4: 60 60 b0 17 00 00 01 04 00 00 00 04 00 00 00 03 00 00 00 02
        //Version 1: 60:60:b0:17:00:00:00:01:00:00:00:00:00:00:00:00:00:00:00:00
        try {
            $version = $this->handshakeVersion41();
            if ($version) {
                return $version;
            }
        } catch (IOException $e) {
            throw new HandshakeException($e->getMessage());
        }

        throw new HandshakeException('Handshake Exception. Unable to negotiate a version to use. Proposed versions were 4.1 and 1.0');
    }

    /**
     * @return Session
     */
    public function session()
    {
        if (null !== $this->session) {
            return $this->session;
        }

        if (!$this->versionAgreed) {
            $this->versionAgreed = $this->handshake();
        }

        $this->session = $this->sessionRegistry->getSession($this->versionAgreed, $this->credentials);

        return $this->session;
    }

    /**
     * Handshake and present a compatibility with Bolt protocol v4.1 and v1.
     *
     * @throws IOException
     */
    private function handshakeVersion41(): int
    {
        //We only send 4.1 and 1.0
        //60 60 b0 17 00 00 01 04 00 00 00 01 00 00 00 00 00 00 00 00
        $msg = chr(0x60).chr(0x60).chr(0xb0).chr(0x17);
        $msg .= chr(0x00).chr(0x00).chr(0x01).chr(0x04);
        $msg .= pack('N', 1);
        $msg .= pack('N', 0);
        $msg .= pack('N', 0);

        $this->io->write($msg);
        $rawHandshakeResponse = $this->io->read(4);
        $response = unpack('N', $rawHandshakeResponse);

        return $response[1];
    }
}
