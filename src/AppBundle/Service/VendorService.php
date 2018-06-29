<?php

namespace AppBundle\Service;

use AppBundle\Client\MktpClient;
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

    const USE_CACHE = true;

    const MEMCACHE_KEY = 'vendor_list';

    const MEMCACHE_LIFETIME = 1800;

    const VENDOR_API_TIMEOUT = 10;

    /** @var  Client */
    protected $mktpClient;


    /** @var  CacheProvider */
    protected $cacheProvider;

    /**
     * VendorService constructor.
     *
     * @param $mktpClient
     */
    public function __construct(MktpClient $mktpClient, CacheProvider $cacheProvider)
    {
        $this->mktpClient = $mktpClient;
        $this->cacheProvider = $cacheProvider;
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

        $vendors = $this->mktpClient->getAllVendors();
        $this->updateVendorInCache($vendors);

        return $vendors;
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
     * @param null $vendorId
     *
     * @return array|null
     */
    protected function getVendorFromCache($vendorId = null)
    {
        if (!$this->useCache()) {
            return [];
        }

        $vendors = $this->cacheProvider->fetch(self::MEMCACHE_KEY);

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

        $this->cacheProvider->save(self::MEMCACHE_KEY, $vendors, self::MEMCACHE_LIFETIME);
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
     * @return bool
     */
    protected function useCache()
    {
        return self::USE_CACHE;
    }
}
