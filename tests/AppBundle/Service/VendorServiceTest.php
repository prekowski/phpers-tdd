<?php

namespace AppBundle\Service;

use AppBundle\Client\MktpClient;
use Doctrine\Common\Cache\CacheProvider;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class VendorServiceTest extends TestCase
{
    public function testGetVendor()
    {

    }

    /**
     * @throw \Throwable
     */
    public function testShouldReturnEmptyArrayWhenCallGetVendorWithoutVendorIdAndWithoutDataInCache()
    {
        // Given
        $expected = [];

        $mktpClient = $this->createMock(MktpClient::class); // Stub
        $cacheMock = $this->getMockBuilder(CacheProvider::class)->getMock(); // Mock

        $mktpClient->expects($this->once())
            ->method('request')
            ->withAnyParameters()
            ->willReturn()

        $cacheMock->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $vendorServiceUnderTest = new VendorService($mktpClient, $cacheMock);

        // When
        $actual = $vendorServiceUnderTest->getVendor();

        // Then
        $this->assertEquals($expected, $actual);

    }
}
