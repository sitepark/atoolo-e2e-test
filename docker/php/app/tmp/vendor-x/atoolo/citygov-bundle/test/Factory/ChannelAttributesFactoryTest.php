<?php

namespace Atoolo\CityGov\Test\Factory;

use Atoolo\CityGov\Factory\ChannelAttributesFactory;
use Atoolo\Resource\DataBag;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceTenant;
use PHPUnit\Framework\TestCase;

class ChannelAttributesFactoryTest extends TestCase
{
    /**
     * Helper function
     * @param DataBag $attributes
     * @return ChannelAttributesFactory
     */
    public function getChannelWithAttributes(DataBag $attributes): ChannelAttributesFactory
    {
        $resourceChannel = new ResourceChannel(
            'pub1',
            'www',
            'www',
            'Webserver',
            false,
            'internet',
            'de_DE',
            '/var/www/publisher/',
            '/',
            '/',
            'www',
            [],
            $attributes,
            new ResourceTenant(
                'pub1',
                'www',
                'www',
                'localhost',
                new DataBag([]),
            ),
        );
        return new ChannelAttributesFactory($resourceChannel);
    }

    /**
     *
     */
    public function testEmpty(): void
    {
        $factory = $this->getChannelWithAttributes(new DataBag([]));
        $channelAttribute = $factory->create();
        $this->assertFalse($channelAttribute->addAlternativeDocuments);
    }

    /**
     *
     */
    public function testFalse(): void
    {
        $factory = $this->getChannelWithAttributes(new DataBag(['sp_vv_alternativeTitle' => false]));
        $channelAttribute = $factory->create();
        $this->assertFalse($channelAttribute->addAlternativeDocuments);
    }

    /**
     *
     */
    public function testTrue(): void
    {
        $factory = $this->getChannelWithAttributes(new DataBag(['sp_vv_alternativeTitle' => true]));
        $channelAttribute = $factory->create();
        $this->assertTrue($channelAttribute->addAlternativeDocuments);
    }
}
