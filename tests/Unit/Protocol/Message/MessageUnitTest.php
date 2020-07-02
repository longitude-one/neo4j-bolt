<?php

namespace GraphAware\Bolt\Tests\Unit\Protocol\Message;

use GraphAware\Bolt\Protocol\Message\SuccessMessage;
use GraphAware\Bolt\Protocol\Message\AbstractMessage;
use PHPUnit\Framework\TestCase;

/**
 * Class MessageUnitTest
 * @package GraphAware\Bolt\Tests\Protocol\Message
 *
 * @group message
 * @group unit
 */
class MessageUnitTest extends TestCase
{
    public function testSuccessMessageWithoutFields()
    {
        $message = new SuccessMessage(array());
        $this->assertInstanceOf(AbstractMessage::class, $message);
    }
}
