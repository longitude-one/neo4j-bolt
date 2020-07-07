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

namespace GraphAware\Bolt\IO;

use GraphAware\Bolt\Configuration;
use GraphAware\Bolt\Exception\IOException;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcher;

class StreamSocket extends AbstractIO
{
    /**
     * @var array|null
     */
    protected $context;

    /**
     * @var EventDispatcher|null
     */
    protected $eventDispatcher;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var bool
     */
    protected $keepAlive;

    /**
     * @var int
     */
    protected $port;
    /**
     * @var string
     */
    protected $protocol;

    /**
     * @var int
     */
    protected $timeout = 5;

    private $configuration;

    /**
     * @var resource|null
     */
    private $sock;

    /**
     * @param string     $host
     * @param int        $port
     * @param array|null $context
     * @param bool       $keepAlive
     */
    public function __construct($host, $port, $context = null, $keepAlive = false, EventDispatcher $eventDispatcher = null, Configuration $configuration = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->context = $context;
        $this->keepAlive = $keepAlive;
        $this->eventDispatcher = $eventDispatcher;
        $this->protocol = 'tcp';

        $this->context = null !== $context ? $context : stream_context_create();
        $this->configuration = $configuration;

        /*
        if (is_null($this->context)) {
            $this->context = stream_context_create();
        } else {
            $this->protocol = 'ssl';
        }
        */
        //stream_set_blocking($this->sock, false);
    }

    public static function withConfiguration($host, $port, Configuration $configuration, EventDispatcher $eventDispatcher = null)
    {
        $context = null;
        if (null !== $configuration->getValue('bind_to_interface')) {
            $context = stream_context_create([
                'socket' => [
                    'bindto' => $configuration->getValue('bind_to_interface'),
                ],
            ]);
        }

        return new self($host, $port, $context, false, $eventDispatcher, $configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if (is_resource($this->sock)) {
            fclose($this->sock);
        }

        $this->sock = null;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        $errstr = $errno = null;

        $remote = sprintf(
            '%s://%s:%s',
            $this->protocol,
            $this->host,
            $this->port
        );

        $this->sock = stream_socket_client(
            $remote,
            $errno,
            $errstr,
            $this->timeout,
            STREAM_CLIENT_CONNECT,
            $this->context
        );

        if (false === $this->sock) {
            throw new IOException(sprintf('Error to connect to the server(%s) :  "%s"', $errno, $errstr));
        }

        if ($this->shouldEnableCrypto()) {
            //FIXME Add a test to verify that STREAM_CRYPTO_METHOD_SSLv23_CLIENT is defined and ext-openssl is available
            $result = stream_socket_enable_crypto($this->sock, true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT);
            if (true !== $result) {
                throw new RuntimeException(sprintf('Unable to enable crypto on socket'));
            }
        }

        stream_set_read_buffer($this->sock, 0);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isConnected()
    {
        return is_resource($this->sock);
    }

    /**
     * {@inheritdoc}
     */
    public function read($n)
    {
        if (null === $n) {
            return $this->readAll();
        }
        $this->assertConnected();
        $read = 0;
        $data = '';

        while ($read < $n) {
            $buffer = fread($this->sock, ($n - $read));
            //echo 'R:'.\GraphAware\Bolt\Misc\Helper::prettyHex($buffer).PHP_EOL; //FIXME REMOVE
            // check '' later for non-blocking mode use case
            if (false === $buffer || '' === $buffer) {
                throw new IOException('Error receiving data');
            }

            $read += mb_strlen($buffer, 'ASCII');
            $data .= $buffer;
        }

        return $data;
    }

    /**
     * @param int $l
     *
     * @return string
     */
    public function readChunk($l = 8192)
    {
        return stream_socket_recvfrom($this->sock, $l);
        //echo Helper::prettyHex($data);
    }

    /**
     * {@inheritdoc}
     */
    public function reconnect()
    {
        $this->close();

        return $this->connect();
    }

    /**
     * {@inheritdoc}
     */
    public function select($sec, $usec)
    {
        $r = [$this->sock];
        $w = $e = null;

        return stream_select($r, $w, $e, $sec, $usec);
    }

    public function shouldEnableCrypto()
    {
        if (null !== $this->configuration && Configuration::TLSMODE_REQUIRED === $this->configuration->getValue('tls_mode')) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function write($data)
    {
        //echo 'W:'.\GraphAware\Bolt\Misc\Helper::prettyHex($data).PHP_EOL; //FIXME REMOVE
        $this->assertConnected();
        $written = 0;
        $len = mb_strlen($data, 'ASCII');

        while ($written < $len) {
            $buf = fwrite($this->sock, $data);

            if (false === $buf) {
                throw new IOException('Error writing data');
            }

            if (0 === $buf && feof($this->sock)) {
                throw new IOException('Broken pipe or closed connection');
            }

            $written += $buf;
        }
    }

    /**
     * @return string
     */
    private function readAll()
    {
        stream_set_blocking($this->sock, false);
        $r = [$this->sock];
        $w = $e = [];
        $data = '';
        $continue = true;

        while ($continue) {
            $select = stream_select($r, $w, $e, 0, 10000);

            if (0 === $select) {
                stream_set_blocking($this->sock, true);

                return $data;
            }

            $buffer = stream_get_contents($this->sock, 8192);

            if ('' === $buffer) {
                stream_select($r, $w, $e, null, null);
            }

            $r = [$this->sock];
            $data .= $buffer;
        }

        return $data;
    }
}
