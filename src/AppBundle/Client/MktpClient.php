<?php

namespace AppBundle\Client;

use GuzzleHttp\Client;

class MktpClient
{
    const HTTP_REQUEST_METHOD_POST = 'POST';

    const ITEMS_PER_PAGE = 1000;

    protected $mktpUrl;

    /** @var  string */
    protected $mktpUsername;

    /** @var  string */
    protected $mktpPassword;

    /** @var  string */
    protected $mktpVendorCode;

    /** @var  string */
    protected $mktpVendorApiUrl;

    private $guzzleClient;

    /**
     * @param Client $guzzleClient
     * @param        $mktpUrl
     * @param        $mktpUsername
     * @param        $mktpPassword
     * @param        $mktpVendorCode
     * @param        $mktpVendorApiUrl
     */
    public function __construct(Client $guzzleClient, $mktpUrl, $mktpUsername, $mktpPassword, $mktpVendorCode, $mktpVendorApiUrl)
    {
        $this->mktpUrl = $mktpUrl;
        $this->mktpUsername = $mktpUsername;
        $this->mktpPassword = $mktpPassword;
        $this->mktpVendorCode = $mktpVendorCode;
        $this->mktpVendorApiUrl = $mktpVendorApiUrl;
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * @return array
     */
    public function getAllVendors()
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
     * @param array $data
     * @param int   $pageNr
     *
     * @return array|mixed
     */
    private function request($data = [], $pageNr = 1)
    {
        try {
            $response = $this->guzzleClient->request(
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
            'code'     => $this->mktpVendorCode,
            'username' => $this->mktpUsername,
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
        return sha1(http_build_query($data) . sha1($this->mktpPassword));
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
}