<?php

namespace AppBundle\Service;

use Doctrine\Common\Cache\CacheProvider;
use GuzzleHttp\Client;

/**
 * Class VendorService
 *
 * @package AppBundle\Service
 */
class VendorService
{
    const ID = 'est.vendor.service';

    const HTTP_REQUEST_METHOD_POST = 'POST';

    const USE_CACHE = true;

    const MEMCACHE_KEY = 'vendor_list';

    const MEMCACHE_LIFETIME = 1800;

    const ITEMS_PER_PAGE = 1000;

    const VENDOR_API_TIMEOUT = 10;

    /** @var  Client */
    protected $guzzleClient;

    protected $mktpUrl;

    /** @var  string */
    protected $mktpUsername;

    /** @var  string */
    protected $mktpPassword;

    /** @var  string */
    protected $mktpVendorCode;

    /** @var  string */
    protected $mktpVendorApiUrl;

    /** @var  CacheProvider */
    protected $cacheProvider;

    /**
     * VendorService constructor.
     */
    public function __construct()
    {
        $guzzleConfig = [
            'timeout' => self::VENDOR_API_TIMEOUT,
        ];

        $this->setGuzzleClient(new Client($guzzleConfig));
    }

    /**
     * @param array $data
     * @param int   $pageNr
     *
     * @return array|mixed
     */
    protected function request($data = [], $pageNr = 1)
    {
        try {
            $response = $this->getGuzzleClient()->request(
                self::HTTP_REQUEST_METHOD_POST,
                $this->mktpUrl . $this->mktpVendorApiUrl,
                [
                    'form_params' => $this->buildRequestData($data, $pageNr),
                ]
            );

            return json_decode($response->getBody()->getContents(), true);

        } catch (\Exception $ex) {

            return [
                'isError' => true,
                'results' => [],
            ];
        }
    }

    /**
     * @param array $responseData
     *
     * @return array
     */
    protected function processResponse($responseData = [])
    {
        $response = [];
        if (isset($responseData['isError']) && $responseData['isError'] !== true) {

            $data = $responseData['results'];

            foreach ($data as $item) {
                if (isset($item['id']) && isset($item['name'])) {
                    $response[intval($item['id'])] = $item['name'];
                }
            }
        }

        return $response;
    }

    /**
     * @param null $vendorId
     *
     * @return array|null
     */
    public function getVendor($vendorId = null)
    {
        $vendors = $this->getVendorFromCache($vendorId);

        if (!empty($vendors)) {
            return $vendors;
        }

        $vendors = $this->getAllVendorsFromApi();
        $this->updateVendorInCache($vendors);

        return $vendors;
    }

    /**
     * @return array
     */
    protected function getAllVendorsFromApi()
    {
        $done = false;
        $result = [];
        $page = 1;
        while (!$done) {
            $requestResponse = $this->request([], $page);
            $response = $this->processResponse($requestResponse);
            $result = $result + $response;

            if (count($response) < self::ITEMS_PER_PAGE) {
                $done = true;
            }
            $page++;
        }

        return $result;
    }

    /**
     * @param null $vendorName
     *
     * @return array|null
     */
    public function getVendorByName($vendorName = null)
    {
        $vendors = $this->getVendor(null);
        $reversedVendors = array_flip($vendors);

        return isset($reversedVendors[$vendorName])
            ? [$reversedVendors[$vendorName] => $vendorName]
            : null;
    }

    /**
     * @param     $data
     * @param int $currentPage
     *
     * @return array
     */
    protected function buildRequestData($data, $currentPage = 1)
    {
        $data['itemsPerPage'] = self::ITEMS_PER_PAGE;
        $data['currentPage'] = $currentPage;
        $requestData = [
            'code'     => $this->getMktpVendorCode(),
            'username' => $this->getMktpUsername(),
            'data'     => $data,
            'hash'     => $this->buildHash($data),
        ];

        return $requestData;
    }

    /**
     * @param $data
     *
     * @return string
     */
    protected function buildHash($data)
    {
        return sha1(http_build_query($data) . sha1($this->getMktpPassword()));
    }

    /**
     * @param null $vendorId
     *
     * @return array|null
     */
    protected function getVendorFromCache($vendorId = null)
    {
        if (!$this->useCache()) {
            return [];
        }

        $vendors = $this->getCacheProvider()->fetch(self::MEMCACHE_KEY);

        if (null !== $vendorId) {
            return isset($vendors[$vendorId])
                ? [$vendorId => $vendors[$vendorId]]
                : null;
        }

        return $vendors !== false ? $vendors : [];

    }

    /**
     * @param array $vendors
     */
    protected function updateVendorInCache($vendors = [])
    {
        if (!$this->useCache()) {
            return;
        }

        $this->getCacheProvider()->save(self::MEMCACHE_KEY, $vendors, self::MEMCACHE_LIFETIME);
    }

    /**
     * @return Client
     */
    public function getGuzzleClient()
    {
        return $this->guzzleClient;
    }

    /**
     * @param Client $guzzleClient
     *
     * @return VendorService
     */
    public function setGuzzleClient($guzzleClient)
    {

        $this->guzzleClient = $guzzleClient;

        return $this;
    }

    /**
     * @param mixed $mktpUrl
     *
     * @return VendorService
     */
    public function setMktpUrl($mktpUrl)
    {
        $this->mktpUrl = $mktpUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getMktpUsername()
    {
        return $this->mktpUsername;
    }

    /**
     * @param string $mktpUsername
     *
     * @return VendorService
     */
    public function setMktpUsername($mktpUsername)
    {
        $this->mktpUsername = $mktpUsername;

        return $this;
    }

    /**
     * @return string
     */
    public function getMktpPassword()
    {
        return $this->mktpPassword;
    }

    /**
     * @param string $mktpPassword
     *
     * @return VendorService
     */
    public function setMktpPassword($mktpPassword)
    {
        $this->mktpPassword = $mktpPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getMktpVendorCode()
    {
        return $this->mktpVendorCode;
    }

    /**
     * @param string $mktpVendorCode
     *
     * @return VendorService
     */
    public function setMktpVendorCode($mktpVendorCode)
    {
        $this->mktpVendorCode = $mktpVendorCode;

        return $this;
    }

    /**
     * @param string $mktpVendorApiUrl
     *
     * @return VendorService
     */
    public function setMktpVendorApiUrl($mktpVendorApiUrl)
    {
        $this->mktpVendorApiUrl = $mktpVendorApiUrl;

        return $this;
    }

    /**
     * @return CacheProvider
     */
    public function getCacheProvider()
    {
        return $this->cacheProvider;
    }

    /**
     * @param CacheProvider $cacheProvider
     *
     * @return VendorService
     */
    public function setCacheProvider(CacheProvider $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;

        return $this;
    }

    /**
     * @return bool
     */
    protected function useCache()
    {
        return self::USE_CACHE;
    }
}
