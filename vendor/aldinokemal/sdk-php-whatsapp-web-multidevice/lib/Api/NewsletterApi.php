<?php
/**
 * NewsletterApi
 * PHP version 8.1
 *
 * @category Class
 * @package  SdkWhatsappWebMultiDevice
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * WhatsApp API MultiDevice
 *
 * This API is used for sending whatsapp via API
 *
 * The version of the OpenAPI document: 5.4.0
 * Generated by: https://openapi-generator.tech
 * Generator version: 7.13.0-SNAPSHOT
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace SdkWhatsappWebMultiDevice\Api;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SdkWhatsappWebMultiDevice\ApiException;
use SdkWhatsappWebMultiDevice\Configuration;
use SdkWhatsappWebMultiDevice\HeaderSelector;
use SdkWhatsappWebMultiDevice\ObjectSerializer;

/**
 * NewsletterApi Class Doc Comment
 *
 * @category Class
 * @package  SdkWhatsappWebMultiDevice
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class NewsletterApi
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var HeaderSelector
     */
    protected $headerSelector;

    /**
     * @var int Host index
     */
    protected $hostIndex;

    /** @var string[] $contentTypes **/
    public const contentTypes = [
        'unfollowNewsletter' => [
            'application/json',
        ],
    ];

    /**
     * @param ClientInterface $client
     * @param Configuration   $config
     * @param HeaderSelector  $selector
     * @param int             $hostIndex (Optional) host index to select the list of hosts if defined in the OpenAPI spec
     */
    public function __construct(
        ?ClientInterface $client = null,
        ?Configuration $config = null,
        ?HeaderSelector $selector = null,
        int $hostIndex = 0
    ) {
        $this->client = $client ?: new Client();
        $this->config = $config ?: Configuration::getDefaultConfiguration();
        $this->headerSelector = $selector ?: new HeaderSelector();
        $this->hostIndex = $hostIndex;
    }

    /**
     * Set the host index
     *
     * @param int $hostIndex Host index (required)
     */
    public function setHostIndex($hostIndex): void
    {
        $this->hostIndex = $hostIndex;
    }

    /**
     * Get the host index
     *
     * @return int Host index
     */
    public function getHostIndex()
    {
        return $this->hostIndex;
    }

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Operation unfollowNewsletter
     *
     * Unfollow newsletter
     *
     * @param  \SdkWhatsappWebMultiDevice\Model\UnfollowNewsletterRequest|null $unfollow_newsletter_request unfollow_newsletter_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['unfollowNewsletter'] to see the possible values for this operation
     *
     * @throws \SdkWhatsappWebMultiDevice\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \SdkWhatsappWebMultiDevice\Model\GenericResponse|\SdkWhatsappWebMultiDevice\Model\ErrorBadRequest|\SdkWhatsappWebMultiDevice\Model\ErrorInternalServer
     */
    public function unfollowNewsletter($unfollow_newsletter_request = null, string $contentType = self::contentTypes['unfollowNewsletter'][0])
    {
        list($response) = $this->unfollowNewsletterWithHttpInfo($unfollow_newsletter_request, $contentType);
        return $response;
    }

    /**
     * Operation unfollowNewsletterWithHttpInfo
     *
     * Unfollow newsletter
     *
     * @param  \SdkWhatsappWebMultiDevice\Model\UnfollowNewsletterRequest|null $unfollow_newsletter_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['unfollowNewsletter'] to see the possible values for this operation
     *
     * @throws \SdkWhatsappWebMultiDevice\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \SdkWhatsappWebMultiDevice\Model\GenericResponse|\SdkWhatsappWebMultiDevice\Model\ErrorBadRequest|\SdkWhatsappWebMultiDevice\Model\ErrorInternalServer, HTTP status code, HTTP response headers (array of strings)
     */
    public function unfollowNewsletterWithHttpInfo($unfollow_newsletter_request = null, string $contentType = self::contentTypes['unfollowNewsletter'][0])
    {
        $request = $this->unfollowNewsletterRequest($unfollow_newsletter_request, $contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();


            switch($statusCode) {
                case 200:
                    return $this->handleResponseWithDataType(
                        '\SdkWhatsappWebMultiDevice\Model\GenericResponse',
                        $request,
                        $response,
                    );
                case 400:
                    return $this->handleResponseWithDataType(
                        '\SdkWhatsappWebMultiDevice\Model\ErrorBadRequest',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\SdkWhatsappWebMultiDevice\Model\ErrorInternalServer',
                        $request,
                        $response,
                    );
            }

            

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            return $this->handleResponseWithDataType(
                '\SdkWhatsappWebMultiDevice\Model\GenericResponse',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\SdkWhatsappWebMultiDevice\Model\GenericResponse',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 400:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\SdkWhatsappWebMultiDevice\Model\ErrorBadRequest',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\SdkWhatsappWebMultiDevice\Model\ErrorInternalServer',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation unfollowNewsletterAsync
     *
     * Unfollow newsletter
     *
     * @param  \SdkWhatsappWebMultiDevice\Model\UnfollowNewsletterRequest|null $unfollow_newsletter_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['unfollowNewsletter'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function unfollowNewsletterAsync($unfollow_newsletter_request = null, string $contentType = self::contentTypes['unfollowNewsletter'][0])
    {
        return $this->unfollowNewsletterAsyncWithHttpInfo($unfollow_newsletter_request, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation unfollowNewsletterAsyncWithHttpInfo
     *
     * Unfollow newsletter
     *
     * @param  \SdkWhatsappWebMultiDevice\Model\UnfollowNewsletterRequest|null $unfollow_newsletter_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['unfollowNewsletter'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function unfollowNewsletterAsyncWithHttpInfo($unfollow_newsletter_request = null, string $contentType = self::contentTypes['unfollowNewsletter'][0])
    {
        $returnType = '\SdkWhatsappWebMultiDevice\Model\GenericResponse';
        $request = $this->unfollowNewsletterRequest($unfollow_newsletter_request, $contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'unfollowNewsletter'
     *
     * @param  \SdkWhatsappWebMultiDevice\Model\UnfollowNewsletterRequest|null $unfollow_newsletter_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['unfollowNewsletter'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function unfollowNewsletterRequest($unfollow_newsletter_request = null, string $contentType = self::contentTypes['unfollowNewsletter'][0])
    {



        $resourcePath = '/newsletter/unfollow';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;





        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($unfollow_newsletter_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                $httpBody = \GuzzleHttp\Utils::jsonEncode(ObjectSerializer::sanitizeForSerialization($unfollow_newsletter_request));
            } else {
                $httpBody = $unfollow_newsletter_request;
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                $httpBody = \GuzzleHttp\Utils::jsonEncode($formParams);
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires HTTP basic authentication
        if (!empty($this->config->getUsername()) || !(empty($this->config->getPassword()))) {
            $headers['Authorization'] = 'Basic ' . base64_encode($this->config->getUsername() . ":" . $this->config->getPassword());
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'POST',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Create http client option
     *
     * @throws \RuntimeException on file opening failure
     * @return array of http client options
     */
    protected function createHttpClientOption()
    {
        $options = [];
        if ($this->config->getDebug()) {
            $options[RequestOptions::DEBUG] = fopen($this->config->getDebugFile(), 'a');
            if (!$options[RequestOptions::DEBUG]) {
                throw new \RuntimeException('Failed to open the debug file: ' . $this->config->getDebugFile());
            }
        }

        return $options;
    }

    private function handleResponseWithDataType(
        string $dataType,
        RequestInterface $request,
        ResponseInterface $response
    ): array {
        if ($dataType === '\SplFileObject') {
            $content = $response->getBody(); //stream goes to serializer
        } else {
            $content = (string) $response->getBody();
            if ($dataType !== 'string') {
                try {
                    $content = json_decode($content, false, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $exception) {
                    throw new ApiException(
                        sprintf(
                            'Error JSON decoding server response (%s)',
                            $request->getUri()
                        ),
                        $response->getStatusCode(),
                        $response->getHeaders(),
                        $content
                    );
                }
            }
        }

        return [
            ObjectSerializer::deserialize($content, $dataType, []),
            $response->getStatusCode(),
            $response->getHeaders()
        ];
    }

    private function responseWithinRangeCode(
        string $rangeCode,
        int $statusCode
    ): bool {
        $left = (int) ($rangeCode[0].'00');
        $right = (int) ($rangeCode[0].'99');

        return $statusCode >= $left && $statusCode <= $right;
    }
}
