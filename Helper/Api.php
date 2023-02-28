<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Message\ManagerInterface;
use Zend_Http_Client_Exception;

/**
 *
 */
class Api
{
    /**
     * @var Config
     */
    private Config $configHelper;

    /**
     * @var ZendClientFactory
     */
    private ZendClientFactory $httpClient;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * @var Http
     */
    private Http $request;

    /**
     * @var $filename
     */
    private $filename;

    /**
     * @param  Config            $configHelper
     * @param  ZendClientFactory $httpClient
     * @param  ManagerInterface  $messageManager
     * @param  Filesystem        $filesystem
     * @param  DirectoryList     $directoryList
     * @param  Http              $request
     * @param  $filename
     * @throws Zend_Http_Client_Exception
     */
    public function __construct(
        Config            $configHelper,
        ZendClientFactory $httpClient,
        ManagerInterface  $messageManager,
        Filesystem        $filesystem,
        DirectoryList     $directoryList,
        Http              $request,
        $filename
    ) {
        $this->configHelper = $configHelper;
        $this->httpClient = $httpClient;
        $this->messageManager = $messageManager;
        $this->filesystem = $filesystem;
        $this->request = $request;
        $this->directoryList = $directoryList;
        $this->filename = $filename;
        $this->checkApiConnection();
    }

    /**
     * Creates an HTTP request to a specified path and returns the response.
     *
     * @param  string $endpoint     The Foxpost API endpoint.
     * @param  string $method       The HTTP method.
     * @param  array  $request_data The request data.
     * @param  string $return_type  The response return type (data or file).
     * @throws Zend_Http_Client_Exception
     */
    public function request(string $endpoint, string $method = 'GET', array $request_data = [], string $return_type = 'data')
    {
        $url = $this->configHelper->getApiBaseUrl() . $endpoint;

        $httpHeaders = [
            'Content-Type' => 'application/json',
            'api-key' => $this->configHelper->getFoxpostApiKey(),
        ];

        $request = $this->httpClient->create()
            ->setAuth($this->configHelper->getFoxpostApiUsername(), $this->configHelper->getFoxpostApiPassword())
            ->setHeaders($httpHeaders)
            ->setUri($url);

        if (!empty($request_data)) {
            $request->setRawData(json_encode($request_data));
        }

        $body = $request->request($method)->getBody();

        if ($return_type === 'file') {
            $data = $this->saveFile($body);
        } else {
            $data = (array)json_decode($body);
        }

        if (!empty($data)) {
            $this->showMessage($data);
        }

        return [
            'message' => $data['error'] ?? 'OK',
            'data' => $data,
        ];
    }

    /**
     * Shows a message depending on error or success.
     *
     * @param  $data
     * @return void
     */
    public function showMessage($data): void
    {
        // Are there any errors sent as a response?
        $errors = [];
        if (isset($data['parcels'])) {
            foreach ($data['parcels'] as $parcel) {
                if ($parcel->errors !== null) {
                    $errors[] = [
                        'error' => $parcel->errors,
                        'order_id' => $parcel->refCode,
                    ];
                }
            }
        }

        $message = '';
        if (count($errors)) {
            foreach ($errors as $error) {
                $message .= $error['error'][0]->message . ' (ID: ' . $error['order_id'] . ') ';
            }
            $this->messageManager->addErrorMessage($message);
        } else {
            if (!isset($data['error'])) {
                $this->messageManager->addSuccessMessage(__('Data was successfully sent to Foxpost.'));
            }
        }
    }

    /**
     * Saves the file to the media folder.
     *
     * @param  string $body
     * @return void
     */
    public function saveFile(string $body): void
    {
        try {
            $media = $this->filesystem->getDirectoryWrite($this->directoryList::MEDIA);
            $media->writeFile($this->filename, $body);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Set the filename.
     *
     * @param  string $filename
     * @return void
     */
    public function setFileName(string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * Checks for API connection and returns an error in case a request was sent with wrong authorization or invalid
     * API key. Don't execute it on the config page, as it will be cached.
     *
     * @return void
     * @throws Zend_Http_Client_Exception
     */
    public function checkApiConnection(): void
    {
        $response = $this->request('address', 'GET');
        $uri = $this->request->getUriString();
        if (isset($response['data']['error']) && str_contains($uri, 'test/connection') !== true) {
            $this->messageManager->addErrorMessage(__('Couldn\'t retreive data from the Foxpost API, please check if the connection is working.'));
        }
    }
}
