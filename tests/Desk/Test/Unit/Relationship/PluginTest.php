<?php

namespace Desk\Test\Unit\Relationship;

use Desk\Relationship\ResponseParser;
use Desk\Test\Helper\UnitTestCase;

class PluginTest extends UnitTestCase
{

    /**
     * {@inheritdoc}
     */
    public function getMockedClass()
    {
        return 'Desk\\Relationship\\Plugin';
    }

    /**
     * @covers Desk\Relationship\Plugin::getSubscribedEvents
     */
    public function testGetSubscribedEvents()
    {
        $plugin = $this->mock('getSubscribedEvents');
        $events = $plugin->getSubscribedEvents();
        $this->assertInternalType('array', $events);
    }

    /**
     * @covers Desk\Relationship\Plugin::onCreateCommand
     */
    public function testOnCreateCommand()
    {
        $parser = null;

        $command = \Mockery::mock('Guzzle\\Service\\Command\\OperationCommand')
            ->shouldReceive('setResponseParser')
                ->with(
                    \Mockery::on(
                        function ($arg) use (&$parser) {
                            if ($arg instanceof ResponseParser) {
                                $parser = $arg; // save for later
                                return true;
                            }

                            return false;
                        }
                    )
                )
            ->getMock();

        $event = \Mockery::mock('Guzzle\\Common\\Event')
            ->shouldReceive('offsetGet')
                ->with('command')
                ->andReturn($command)
            ->getMock();

        $this->mock('onCreateCommand')->onCreateCommand($event);

        if ($parser instanceof ResponseParser) {
            $builder = $this->getPrivateProperty($parser, 'builder');
            $this->assertInstanceOf('Desk\\Relationship\\ResourceBuilder', $builder);

            $actualCommand = $this->getPrivateProperty($builder, 'command');
            $this->assertSame($command, $actualCommand);
        }
    }
}
