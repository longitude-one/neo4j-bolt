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

namespace GraphAware\Bolt\Protocol\Message;

interface MessageInterface
{
    /**
     * @return array
     */
    public function getFields();

    /**
     * @return string
     */
    public function getMessageType();

    /**
     * @return string
     */
    public function getSignature();

    /**
     * @return bool
     */
    public function isFailure();

    /**
     * @return bool
     */
    public function isSuccess();

    /*
    public function isIgnored();

    //FIXME Why is this comment?
    public function isRecord();
    */
}
