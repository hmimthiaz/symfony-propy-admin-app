<?php

namespace App\Classes\Api;

use Adbar\Dot;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Request;

class ApiRequest
{
    public const CACHE_TIME_15_MINUTES = 60 * 15;

    public const CACHE_TIME_30_MINUTES = 60 * 30;

    public const CACHE_TIME_HOUR = 60 * 60;

    public const CACHE_TIME_DAY = 60 * 60 * 24;

    private string $path;

    private string $method;

    private bool $forceRefresh = false;

    private ?string $cacheKey = null;

    private array $cacheTags = [];

    private ?Client $client = null;

    private ?string $secret = null;

    private Dot $data;

    private Dot $headers;

    private bool $cachedRequest = false;

    private float $startTime = 0.0;

    private float $endTime = 0.0;

    private ?ApiResult $apiResult = null;

    private ?CacheItemPoolInterface $cache = null;

    public function __construct(
        string $endPoint,
        string $path,
        string $method = Request::METHOD_POST,
        int $timeout = 10,
    ) {
        $this->data = new Dot();
        $this->headers = new Dot();
        $this->path = $path;
        $this->method = $method;
        $this->client = new Client([
            'base_uri' => $endPoint,
            'timeout' => $timeout,
            'cookies' => true,
        ]);
    }

    public function execute(bool $useCache = false, int $cacheTime = ApiRequest::CACHE_TIME_DAY): ApiResult
    {
        $this->cachedRequest = $useCache;
        $this->startTime = microtime(true);

        $cacheItem = null;
        $addDataSignature = true;
        $requestData = $this->data->get();
        if (empty($requestData)) {
            $addDataSignature = false;
        }
        $dataJson = json_encode($requestData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($useCache & !$this->forceRefresh) {
            if (is_null($this->cacheKey)) {
                $this->cacheKey = 'api_request_'.md5($this->path.$dataJson);
            }
            $cacheItem = $this->cache->getItem($this->cacheKey);
            if ($cacheItem->isHit()) {
                $this->endTime = microtime(true);
                $this->apiResult = new ApiResult(cachedResponseString: $cacheItem->get());

                return $this->apiResult;
            }
        }

        if (!is_null($this->secret) && $addDataSignature) {
            $dataSignatureBinary = hash_hmac('sha256', $dataJson, $this->secret, true);
            $dataSignatureBase64 = base64_encode($dataSignatureBinary);
            $this->addHeader('x-data-signature', $dataSignatureBase64);
        }

        try {
            $response = $this->getClient()->request(
                $this->getMethod(),
                $this->path, [
                    'json' => $this->data->get(),
                    'headers' => $this->headers->get(),
                ]);
            $this->endTime = microtime(true);
            $this->apiResult = new ApiResult(response: $response);

            if (!is_null($cacheItem)) {
                $cacheItem->set($this->apiResult->getResponseString());
                $cacheItem->expiresAfter($cacheTime);
                $cacheItem->tag($this->cacheTags);
                $this->cache->save($cacheItem);
            }
        } catch (GuzzleException $exception) {
            $this->endTime = microtime(true);
            $this->apiResult = new ApiResult(
                response: $exception->getResponse(),
                exception: $exception
            );
        }

        return $this->apiResult;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function addParam(string $name, mixed $value): void
    {
        $this->data->add('params.'.$name, $value);
    }

    public function addField(string $name, mixed $value): void
    {
        $this->data->add('fields.'.$name, $value);
    }

    public function addData(string $name, mixed $value): void
    {
        $this->data->add($name, $value);
    }

    public function addHeader(string $name, mixed $value): void
    {
        $this->headers->add($name, $value);
    }

    public function setBearer(string $authCode): void
    {
        $this->addHeader('Authorization', 'Bearer '.$authCode);
    }

    public function setApiKey(string $apiKey, string $secret): void
    {
        $this->secret = $secret;
        $this->addHeader('x-api-key', $apiKey);
    }

    public function setUserIp(string $userIp): void
    {
        $this->addHeader('x-user-ip', $userIp);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    public function isForceRefresh(): bool
    {
        return $this->forceRefresh;
    }

    public function setForceRefresh(bool $forceRefresh): void
    {
        $this->forceRefresh = $forceRefresh;
    }

    public function getCacheKey(): ?string
    {
        return $this->cacheKey;
    }

    public function setCacheKey(?string $cacheKey): void
    {
        $this->cacheKey = $cacheKey;
    }

    public function getCacheTags(): array
    {
        return $this->cacheTags;
    }

    public function setCacheTags(array $cacheTags): void
    {
        $this->cacheTags = $cacheTags;
    }

    public function getData(): Dot
    {
        return $this->data;
    }

    public function setData(Dot $data): void
    {
        $this->data = $data;
    }

    public function getHeaders(): Dot
    {
        return $this->headers;
    }

    public function setHeaders(Dot $headers): void
    {
        $this->headers = $headers;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    public function getCache(): ?CacheItemPoolInterface
    {
        return $this->cache;
    }

    public function setCache(?CacheItemPoolInterface $cache): void
    {
        $this->cache = $cache;
    }

    public function getStartTime(): float
    {
        return $this->startTime;
    }

    public function getEndTime(): float
    {
        return $this->endTime;
    }

    public function getApiResult(): ?ApiResult
    {
        return $this->apiResult;
    }

    public function isCachedRequest(): bool
    {
        return $this->cachedRequest;
    }
}
