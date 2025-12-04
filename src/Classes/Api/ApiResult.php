<?php

namespace App\Classes\Api;

use Adbar\Dot;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

class ApiResult
{
    public Dot $data;

    public Dot $error;

    private bool $_exception = false;

    private bool $_valid = true;

    private bool $cached = false;

    private ?ResponseInterface $guzzleResponse = null;

    private ?GuzzleException $guzzleException = null;
    private ?string $cachedResponseString = null;
    private ?string $responseString = null;
    private ?array $responseHeaders = [];

    public function __construct(
        ?ResponseInterface $response = null,
        ?GuzzleException $exception = null,
        ?string $cachedResponseString = null,
    ) {
        $this->data = new Dot([]);
        $this->error = new Dot([]);

        $this->guzzleResponse = $response;
        $this->guzzleException = $exception;
        $this->cachedResponseString = $cachedResponseString;

        $this->initResult();
    }

    private function initResult(): void
    {
        if (!is_null($this->guzzleResponse)) {
            $this->responseHeaders = $this->guzzleResponse->getHeaders();
            $this->responseString = $this->guzzleResponse->getBody()->getContents();
        }
        if (!is_null($this->cachedResponseString)) {
            $this->cached = true;
            $this->responseString = $this->cachedResponseString;
        }
        if (!is_null($this->responseString)) {
            $responseArray = json_decode($this->responseString, true);
            if (!empty($responseArray['data'])) {
                $this->data->setArray($responseArray['data']);
            }
            if (!empty($responseArray['error'])) {
                $this->_valid = false;
                $this->error->setArray($responseArray['error']);
            }
        }

        if (!is_null($this->guzzleException)) {
            $this->_exception = true;
        }
    }

    public function applyFormValidationErrors(FormInterface $form): void
    {
        foreach ($this->getParamValidationErrors() as $fieldError) {
            $form->addError(new FormError($fieldError['name'].' - '.$fieldError['message']));
        }

        foreach ($this->getFieldValidationErrors() as $fieldError) {
            if ($form->has($fieldError['name'])) {
                $form->get($fieldError['name'])->addError(new FormError($fieldError['message']));
            } else {
                $form->addError(new FormError($fieldError['name'].' - '.$fieldError['message']));
            }
        }

        if ($this->hasExceptionMessage()) {
            $form->addError(new FormError($this->getExceptionMessage()));
        }
    }

    public function hasValidationErrors(): bool
    {
        return $this->error->has('validation');
    }

    public function getFieldValidationErrors(): array
    {
        if ($this->error->isEmpty('validation.fields')) {
            return [];
        }

        return $this->error->get('validation.fields');
    }

    public function getParamValidationErrors(): array
    {
        if ($this->error->isEmpty('validation.params')) {
            return [];
        }

        return $this->error->get('validation.params');
    }

    public function hasExceptionMessage(): bool
    {
        if ($this->_exception) {
            return true;
        }

        if (!$this->_valid) {
            return (bool) $this->error->get('exception');
        }

        return false;
    }

    public function getExceptionMessage(): string
    {
        if ($this->error->has('message')) {
            return $this->error->get('message');
        }

        if ($this->_exception) {
            return $this->guzzleException->getMessage();
        }

        return '-';
    }

    public function isValid(): bool
    {
        if ($this->_valid && !$this->_exception) {
            return true;
        }

        return false;
    }

    public function getResponseString(): ?string
    {
        return $this->responseString;
    }

    public function setResponseString(?string $responseString): void
    {
        $this->responseString = $responseString;
    }

    public function isCached(): bool
    {
        return $this->cached;
    }

    public function getData(): Dot
    {
        return $this->data;
    }

    public function getError(): Dot
    {
        return $this->error;
    }

    public function getResponseHeaders(): ?array
    {
        return $this->responseHeaders;
    }
}
