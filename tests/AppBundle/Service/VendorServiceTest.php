<?php

namespace Tests\AppBundle\Service;

use AppBundle\Service\VendorService;
use Doctrine\Common\Cache\RedisCache;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class VendorServiceTest extends TestCase
{
    /**
     * @var VendorService|m\MockInterface
     */
    protected $vendorService;

    /** @var  m\MockInterface|Client */
    protected $guzzle;

    protected function setUp()
    {
        /** @var m\MockInterface $guzzleMock */
        $guzzleMock = m::mock('GuzzleHttp\Client');

        $this->guzzle = $guzzleMock;

        /** @var VendorService|m\MockInterface vendorService */
        $this->vendorService = m::mock('AppBundle\Service\VendorService')
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $this->vendorService->setGuzzleClient($this->guzzle);
    }

    public function buildHashProvider()
    {
        $testCases = [];

        $testCases['Array'] = [
            [
                'data' => [
                    'id'   => 1,
                    'name' => 'test',
                ],
            ],
        ];

        $testCases['Empty Array'] = [
            [
                'data' => [],
            ],
        ];

        return $testCases;
    }

    /**
     * @dataProvider buildHashProvider
     *
     * @param $data
     */
    public function testBuildHash($data)
    {
        $result = self::getMethod('buildHash', $this->vendorService, $data);

        $expected = sha1(http_build_query($data['data']) . sha1($this->vendorService->getMktpPassword()));

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function buildHashRequestDataProvider()
    {
        $testCases = [];

        $testCases['test with default pagination'] = [
            [
                'input'    => [
                    'vendor_id'    => 1,
                    'itemsPerPage' => 1000,
                    'currentPage'  => 1,
                ],
                'expected' => [
                    'code'     => null,
                    'username' => null,
                    'data'     => [
                        'vendor_id'    => 1,
                        'itemsPerPage' => 1000,
                        'currentPage'  => 1,
                    ],
                    'hash'     => sha1(http_build_query(
                                           [
                                               'vendor_id'    => 1,
                                               'itemsPerPage' => 1000,
                                               'currentPage'  => 1,
                                           ]
                                       ) . sha1(null)
                    ),
                ],
            ],
        ];

        return $testCases;
    }

    /**
     * @depends      testBuildHash
     * @dataProvider buildHashRequestDataProvider
     *
     * @param $data
     */
    public function testBuildRequestData($data)
    {
        $result = self::getMethod('buildRequestData', $this->vendorService, [$data['input']]);
        $this->assertEquals($data['expected'], $result);
    }

    public function requestProvider()
    {
        $testCases = [];

        $testCases['test'] = [
            [
                'input'    => [
                    'vendor_id' => 1,
                ],
                'expected' => null,
            ],
        ];

        return $testCases;
    }

    /**
     * @depends      testBuildRequestData
     *
     * @dataProvider requestProvider
     *
     * @param $data
     */
    public function testRequest($data)
    {
        $this->guzzle->shouldReceive('request')
            ->once()
            ->withAnyArgs()
            ->andReturn(new Response(200, [], ''));

        $result = self::getMethod('request', $this->vendorService, $data);

        $this->assertEquals($data['expected'], $result);
    }

    /**
     * @depends      testBuildRequestData
     *
     */
    public function testRequestThrowsException()
    {
        $this->guzzle->shouldReceive('request')
            ->once()
            ->withAnyArgs()
            ->andReturn(false)
            ->andThrow(new m\Exception());

        $result = self::getMethod('request', $this->vendorService, []);

        $this->assertEquals(
            [
                'isError' => true,
                'results' => [],
            ],
            $result
        );
    }

    /**
     * @return array
     */
    public function getVendorProvider()
    {
        $testCases = [];

        $testCases['From Cache'] = [
            [
                'input'      => null,
                'cache_data' => [
                    [
                        'id'   => 1,
                        'name' => 'Test',
                    ],
                ],
                'expected'   => [
                    [
                        'id'   => 1,
                        'name' => 'Test',
                    ],
                ],
            ],
        ];

        return $testCases;
    }

    /**
     * @depends      testRequest
     *
     * @dataProvider getVendorProvider
     *
     * @param $data
     */
    public function testGetVendorWithCacheData($data)
    {
        //$this->vendorService = $this->getMock('AppBundle\Service\VendorService', array('request', 'getVendorFromCache'));

        $this->vendorService
            ->shouldReceive('getVendorFromCache')
            ->once()
            ->andReturn($data['cache_data']);
        $result = $this->vendorService->getVendor($data['input']);

        $this->assertEquals($data['expected'], $result);
    }

    /**
     * @return array
     */
    public function getVendorWithoutCacheProvider()
    {
        $testCases = [];

        $testCases['From Cache'] = [
            [
                'input'    => null,
                'api_data' => [
                    [
                        'id'   => 1,
                        'name' => 'Test',
                    ],
                ],
                'expected' => [
                    [
                        'id'   => 1,
                        'name' => 'Test',
                    ],
                ],
            ],
        ];

        return $testCases;
    }

    /**
     * *
     * @depends      testRequest
     *
     * @dataProvider getVendorWithoutCacheProvider
     *
     * @param $data
     */
    public function testGetVendorWithoutCacheData($data)
    {
        $this->vendorService
            ->shouldReceive("getVendorFromCache")
            ->andReturn($data['api_data']);

        $this->vendorService
            ->shouldReceive("getAllVendorsFromApi")
            ->andReturn($data['api_data']);

        $this->vendorService
            ->shouldReceive("updateVendorInCache")
            ->andReturn(true);

        $result = $this->vendorService->getVendor($data['input']);

        $this->assertEquals($data['expected'], $result);
    }

    /**
     * @return array
     */
    public function getVendorByNameProvider()
    {
        $testCases = [];

        $testCases[] = [
            [
                'input'    => 'Nume Vendor',
                'vendors'  => [
                    1 => 'Nume Vendors',
                    2 => 'Nume Vendor',
                ],
                'expected' => [
                    2 => 'Nume Vendor',
                ],
            ],
        ];

        $testCases[] = [
            [
                'input'    => 'Nume Vendor',
                'vendors'  => [
                    1 => 'Nume Vendors',
                    2 => 'Nume Vendorr',
                ],
                'expected' => null,
            ],
        ];

        return $testCases;
    }

    /**
     * @depends      testGetVendorWithoutCacheData
     * @depends      testGetVendorWithCacheData
     *
     * @dataProvider getVendorByNameProvider
     *
     * @param $data
     */
    public function testGetVendorByName($data)
    {
        $this->vendorService
            ->shouldReceive("getVendor")
            ->once()
            ->andReturn($data['vendors']);

        $result = $this->vendorService->getVendorByName($data['input']);

        $this->assertEquals($data['expected'], $result);
    }

    public function testGetGuzzleClient()
    {
        $this->assertTrue($this->vendorService->getGuzzleClient() != null);
    }

    /**
     * @return array
     */
    public function processResponseProvider()
    {
        $testCases = [];

        $testCases['Normal'] = [
            [
                'input'    => [
                    'isError' => false,
                    'results' => [
                        [
                            'id'   => 1,
                            'name' => 'Vendor',
                        ],
                    ],

                ],
                'expected' => [
                    1 => 'Vendor',
                ],
            ],
        ];

        $testCases['isError True'] = [
            [
                'input'    => [
                    'isError' => true,
                    'results' => [
                        [
                            'id'   => 1,
                            'name' => 'Vendor',
                        ],
                    ],

                ],
                'expected' => [
                ],
            ],
        ];

        $testCases['Invalid input array'] = [
            [
                'input'    => [
                ],
                'expected' => [
                ],
            ],
        ];

        return $testCases;
    }

    /**
     * @dataProvider processResponseProvider
     *
     * @param $data
     */
    public function testProcessResponse($data)
    {
        $result = self::getMethod('processResponse', $this->vendorService, $data);

        $this->assertEquals($data['expected'], $result);
    }

    public function getAllVendorsFromApiProvider()
    {
        $testCases = [];

        $testCases['no results'] = [
            [
                'request'         => [],
                'process_request' => [],
                'expected'        => [],
            ],
        ];

        $testCases['With Results'] = [
            [
                'request'         => [
                    1,
                ],
                'process_request' => [
                    1,
                ],
                'expected'        => [
                    1,
                ],
            ],
        ];

        return $testCases;
    }

    /**
     * @dataProvider  getAllVendorsFromApiProvider
     * @depends       testProcessResponse
     * @depends       testRequest
     * @depends       testRequestThrowsException
     *
     * @param $data
     */
    public function testGetAllVendorsFromApi($data)
    {
        $this->vendorService
            ->shouldReceive("request")
            ->once()
            ->andReturn($data['request']);

        $this->vendorService
            ->shouldReceive("processResponse")
            ->once()
            ->andReturn($data['process_request']);

        $result = self::getMethod('getAllVendorsFromApi', $this->vendorService, []);

        $this->assertEquals($data['expected'], $result);
    }

    public function testSetMktpUsername()
    {
        $this->vendorService->setMktpUsername('username');
        $this->assertEquals('username', $this->vendorService->getMktpUsername());
    }

    public function testSetMktpPassword()
    {
        $this->vendorService->setMktpPassword('password');
        $this->assertEquals('password', $this->vendorService->getMktpPassword());
    }

    public function testSetMktpVendorCode()
    {
        $this->vendorService->setMktpVendorCode('vendor_code');
        $this->assertEquals('vendor_code', $this->vendorService->getMktpVendorCode());
    }

    public function testSetMemcachedService()
    {
        $cache = new RedisCache();
        $this->vendorService->setCacheProvider($cache);
        $this->assertSame($cache, $this->vendorService->getCacheProvider());
    }

    public function testGetMemcachedService()
    {
        $cache = new RedisCache();
        $this->vendorService->setCacheProvider($cache);
        $this->assertSame($cache, $this->vendorService->getCacheProvider());
    }

    public function updateVendorInCacheProvider()
    {
        $testCases = [];

        $testCases['useCache TRUE'] = [
            [
                'useCache' => true,
                'expected' => null,
            ],
        ];

        $testCases['useCache FALSE'] = [
            [
                'useCache' => false,
                'expected' => null,
            ],
        ];

        return $testCases;
    }

    /**
     * @dataProvider updateVendorInCacheProvider
     *
     * @param $data
     */
    public function testUpdateVendorInCache($data)
    {
        $memcachedService = m::mock('Doctrine\Common\Cache\CacheProvider');

        $this->vendorService
            ->shouldReceive("useCache")
            ->andReturn($data['useCache']);

        if ($data['useCache'] === true) {
            $memcachedService
                ->shouldReceive('save')
                ->andReturn(true);
        }

        $this->vendorService
            ->shouldReceive("getCacheProvider")
            ->andReturn($memcachedService);

        $result = self::getMethod('updateVendorInCache', $this->vendorService, []);

        $this->assertEquals($data['expected'], $result);
    }

    /**
     * @return array
     */
    public function getVendorFromCacheProvider()
    {
        $testCases = [];

        $testCases['Vendor found'] = [
            [
                'input'      => [
                    1,
                ],
                'cache_data' => [
                    1 => 'Vendor',
                    2 => 'Vendor 2',
                ],
                'use_cache'  => true,
                'expected'   => [
                    1 => 'Vendor',
                ],
            ],
        ];

        $testCases['no cache'] = [
            [
                'input'      => [
                    1,
                ],
                'cache_data' => [
                    10 => 'Vendor',
                    2  => 'Vendor 2',
                ],
                'use_cache'  => false,
                'expected'   => [

                ],
            ],
        ];

        $testCases['no data'] = [
            [
                'input'      => [
                    1,
                ],
                'cache_data' => [
                    10 => 'Vendor',
                    2  => 'Vendor 2',
                ],
                'use_cache'  => true,
                'expected'   => null,
            ],
        ];

        $testCases['null input data'] = [
            [
                'input'      => [
                    null,
                ],
                'cache_data' => false,
                'use_cache'  => true,
                'expected'   => [],
            ],
        ];

        return $testCases;
    }

    /**
     * @dataProvider getVendorFromCacheProvider
     *
     * @param $data
     */
    public function testGetVendorFromCache($data)
    {
        $memcachedService = m::mock('Doctrine\Common\Cache\CacheProvider');

        $this->vendorService
            ->shouldReceive("useCache")
            ->andReturn($data["use_cache"]);

        $memcachedService
            ->shouldReceive('fetch')
            ->withAnyArgs()
            ->andReturn($data['cache_data']);

        $this->vendorService
            ->shouldReceive("getCacheProvider")
            ->andReturn($memcachedService);

        $result = self::getMethod('getVendorFromCache', $this->vendorService, $data['input']);

        $this->assertEquals($data['expected'], $result);
    }

    public function testUseCache()
    {
        $result = self::getMethod('useCache', $this->vendorService, []);

        $this->assertTrue(is_bool($result));
    }

    /**
     * @param $name
     * @param $obj
     * @param $params
     *
     * @return mixed
     */
    protected static function getMethod($name, $obj, $params)
    {
        $class  = new \ReflectionClass(get_class($obj));
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $params);
    }

    protected function tearDown()
    {
        m::close();
    }
}
