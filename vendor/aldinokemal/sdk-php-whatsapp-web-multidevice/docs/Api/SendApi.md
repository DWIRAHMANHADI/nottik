# SdkWhatsappWebMultiDevice\SendApi

All URIs are relative to http://localhost:3000, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**sendAudio()**](SendApi.md#sendAudio) | **POST** /send/audio | Send Audio |
| [**sendContact()**](SendApi.md#sendContact) | **POST** /send/contact | Send Contact |
| [**sendFile()**](SendApi.md#sendFile) | **POST** /send/file | Send File |
| [**sendImage()**](SendApi.md#sendImage) | **POST** /send/image | Send Image |
| [**sendLink()**](SendApi.md#sendLink) | **POST** /send/link | Send Link |
| [**sendLocation()**](SendApi.md#sendLocation) | **POST** /send/location | Send Location |
| [**sendMessage()**](SendApi.md#sendMessage) | **POST** /send/message | Send Message |
| [**sendPoll()**](SendApi.md#sendPoll) | **POST** /send/poll | Send Poll / Vote |
| [**sendPresence()**](SendApi.md#sendPresence) | **POST** /send/presence | Send presence status |
| [**sendVideo()**](SendApi.md#sendVideo) | **POST** /send/video | Send Video |


## `sendAudio()`

```php
sendAudio($phone, $audio): \SdkWhatsappWebMultiDevice\Model\SendResponse
```

Send Audio

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = SdkWhatsappWebMultiDevice\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new SdkWhatsappWebMultiDevice\Api\SendApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$phone = 'phone_example'; // string | Phone number with country code
$audio = '/path/to/file.txt'; // \SplFileObject | Audio to send

try {
    $result = $apiInstance->sendAudio($phone, $audio);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SendApi->sendAudio: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **phone** | **string**| Phone number with country code | [optional] |
| **audio** | **\SplFileObject****\SplFileObject**| Audio to send | [optional] |

### Return type

[**\SdkWhatsappWebMultiDevice\Model\SendResponse**](../Model/SendResponse.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: `multipart/form-data`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sendContact()`

```php
sendContact($send_contact_request): \SdkWhatsappWebMultiDevice\Model\SendResponse
```

Send Contact

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = SdkWhatsappWebMultiDevice\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new SdkWhatsappWebMultiDevice\Api\SendApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$send_contact_request = new \SdkWhatsappWebMultiDevice\Model\SendContactRequest(); // \SdkWhatsappWebMultiDevice\Model\SendContactRequest

try {
    $result = $apiInstance->sendContact($send_contact_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SendApi->sendContact: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **send_contact_request** | [**\SdkWhatsappWebMultiDevice\Model\SendContactRequest**](../Model/SendContactRequest.md)|  | [optional] |

### Return type

[**\SdkWhatsappWebMultiDevice\Model\SendResponse**](../Model/SendResponse.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sendFile()`

```php
sendFile($phone, $caption, $file): \SdkWhatsappWebMultiDevice\Model\SendResponse
```

Send File

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = SdkWhatsappWebMultiDevice\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new SdkWhatsappWebMultiDevice\Api\SendApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$phone = 'phone_example'; // string | Phone number with country code
$caption = 'caption_example'; // string | Caption to send
$file = '/path/to/file.txt'; // \SplFileObject | File to send

try {
    $result = $apiInstance->sendFile($phone, $caption, $file);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SendApi->sendFile: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **phone** | **string**| Phone number with country code | [optional] |
| **caption** | **string**| Caption to send | [optional] |
| **file** | **\SplFileObject****\SplFileObject**| File to send | [optional] |

### Return type

[**\SdkWhatsappWebMultiDevice\Model\SendResponse**](../Model/SendResponse.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: `multipart/form-data`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sendImage()`

```php
sendImage($phone, $caption, $view_once, $image, $image_url, $compress): \SdkWhatsappWebMultiDevice\Model\SendResponse
```

Send Image

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = SdkWhatsappWebMultiDevice\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new SdkWhatsappWebMultiDevice\Api\SendApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$phone = 'phone_example'; // string | Phone number with country code
$caption = 'caption_example'; // string | Caption to send
$view_once = True; // bool | View once
$image = '/path/to/file.txt'; // \SplFileObject | Image to send
$image_url = 'image_url_example'; // string | Image URL to send
$compress = True; // bool | Compress image

try {
    $result = $apiInstance->sendImage($phone, $caption, $view_once, $image, $image_url, $compress);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SendApi->sendImage: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **phone** | **string**| Phone number with country code | [optional] |
| **caption** | **string**| Caption to send | [optional] |
| **view_once** | **bool**| View once | [optional] |
| **image** | **\SplFileObject****\SplFileObject**| Image to send | [optional] |
| **image_url** | **string**| Image URL to send | [optional] |
| **compress** | **bool**| Compress image | [optional] |

### Return type

[**\SdkWhatsappWebMultiDevice\Model\SendResponse**](../Model/SendResponse.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: `multipart/form-data`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sendLink()`

```php
sendLink($send_link_request): \SdkWhatsappWebMultiDevice\Model\SendResponse
```

Send Link

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = SdkWhatsappWebMultiDevice\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new SdkWhatsappWebMultiDevice\Api\SendApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$send_link_request = new \SdkWhatsappWebMultiDevice\Model\SendLinkRequest(); // \SdkWhatsappWebMultiDevice\Model\SendLinkRequest

try {
    $result = $apiInstance->sendLink($send_link_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SendApi->sendLink: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **send_link_request** | [**\SdkWhatsappWebMultiDevice\Model\SendLinkRequest**](../Model/SendLinkRequest.md)|  | [optional] |

### Return type

[**\SdkWhatsappWebMultiDevice\Model\SendResponse**](../Model/SendResponse.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sendLocation()`

```php
sendLocation($send_location_request): \SdkWhatsappWebMultiDevice\Model\SendResponse
```

Send Location

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = SdkWhatsappWebMultiDevice\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new SdkWhatsappWebMultiDevice\Api\SendApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$send_location_request = new \SdkWhatsappWebMultiDevice\Model\SendLocationRequest(); // \SdkWhatsappWebMultiDevice\Model\SendLocationRequest

try {
    $result = $apiInstance->sendLocation($send_location_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SendApi->sendLocation: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **send_location_request** | [**\SdkWhatsappWebMultiDevice\Model\SendLocationRequest**](../Model/SendLocationRequest.md)|  | [optional] |

### Return type

[**\SdkWhatsappWebMultiDevice\Model\SendResponse**](../Model/SendResponse.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sendMessage()`

```php
sendMessage($send_message_request): \SdkWhatsappWebMultiDevice\Model\SendResponse
```

Send Message

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = SdkWhatsappWebMultiDevice\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new SdkWhatsappWebMultiDevice\Api\SendApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$send_message_request = new \SdkWhatsappWebMultiDevice\Model\SendMessageRequest(); // \SdkWhatsappWebMultiDevice\Model\SendMessageRequest

try {
    $result = $apiInstance->sendMessage($send_message_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SendApi->sendMessage: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **send_message_request** | [**\SdkWhatsappWebMultiDevice\Model\SendMessageRequest**](../Model/SendMessageRequest.md)|  | [optional] |

### Return type

[**\SdkWhatsappWebMultiDevice\Model\SendResponse**](../Model/SendResponse.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sendPoll()`

```php
sendPoll($send_poll_request): \SdkWhatsappWebMultiDevice\Model\SendResponse
```

Send Poll / Vote

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = SdkWhatsappWebMultiDevice\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new SdkWhatsappWebMultiDevice\Api\SendApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$send_poll_request = new \SdkWhatsappWebMultiDevice\Model\SendPollRequest(); // \SdkWhatsappWebMultiDevice\Model\SendPollRequest

try {
    $result = $apiInstance->sendPoll($send_poll_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SendApi->sendPoll: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **send_poll_request** | [**\SdkWhatsappWebMultiDevice\Model\SendPollRequest**](../Model/SendPollRequest.md)|  | |

### Return type

[**\SdkWhatsappWebMultiDevice\Model\SendResponse**](../Model/SendResponse.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sendPresence()`

```php
sendPresence($send_presence_request): \SdkWhatsappWebMultiDevice\Model\SendResponse
```

Send presence status

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = SdkWhatsappWebMultiDevice\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new SdkWhatsappWebMultiDevice\Api\SendApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$send_presence_request = new \SdkWhatsappWebMultiDevice\Model\SendPresenceRequest(); // \SdkWhatsappWebMultiDevice\Model\SendPresenceRequest

try {
    $result = $apiInstance->sendPresence($send_presence_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SendApi->sendPresence: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **send_presence_request** | [**\SdkWhatsappWebMultiDevice\Model\SendPresenceRequest**](../Model/SendPresenceRequest.md)|  | |

### Return type

[**\SdkWhatsappWebMultiDevice\Model\SendResponse**](../Model/SendResponse.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `sendVideo()`

```php
sendVideo($phone, $caption, $view_once, $video, $compress): \SdkWhatsappWebMultiDevice\Model\SendResponse
```

Send Video

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = SdkWhatsappWebMultiDevice\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new SdkWhatsappWebMultiDevice\Api\SendApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$phone = 'phone_example'; // string | Phone number with country code
$caption = 'caption_example'; // string | Caption to send
$view_once = True; // bool | View once
$video = '/path/to/file.txt'; // \SplFileObject | Video to send
$compress = True; // bool | Compress video

try {
    $result = $apiInstance->sendVideo($phone, $caption, $view_once, $video, $compress);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SendApi->sendVideo: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **phone** | **string**| Phone number with country code | [optional] |
| **caption** | **string**| Caption to send | [optional] |
| **view_once** | **bool**| View once | [optional] |
| **video** | **\SplFileObject****\SplFileObject**| Video to send | [optional] |
| **compress** | **bool**| Compress video | [optional] |

### Return type

[**\SdkWhatsappWebMultiDevice\Model\SendResponse**](../Model/SendResponse.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: `multipart/form-data`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
