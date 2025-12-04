<?php

namespace App\Classes\Base;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ClientBaseController extends BaseController
{
    private ?string $requestMode = null;
    private ?string $requestCompanyId = null;
    private ?array $requestCompanyInfo = null;
    private ?string $requestVenueId = null;
    private ?array $requestVenueInfo = null;
    private string $requestLangCode;
    private ?array $requestLangInfo = null;

    public function getClientLanguages(): array
    {
        if (self::REQUEST_PARAM_MODE_COMPANY == $this->getRequestMode()) {
            return $this->requestCompanyInfo['company_languages'] ?? [];
        }

        if (self::REQUEST_PARAM_MODE_VENUE == $this->getRequestMode()) {
            return $this->requestVenueInfo['venue_languages'] ?? [];
        }

        return [];
    }

    public function getClientDefaultLanguage(): ?array
    {
        if (self::REQUEST_PARAM_MODE_COMPANY == $this->getRequestMode()) {
            return $this->requestCompanyInfo['default_language'] ?? [];
        }

        if (self::REQUEST_PARAM_MODE_VENUE == $this->getRequestMode()) {
            return $this->requestVenueInfo['default_language'] ?? [];
        }

        return [];
    }

    public function getClientDefaultLangCode(): string
    {
        $defaultLang = $this->getClientDefaultLanguage();
        if (!is_null($defaultLang)) {
            return $defaultLang['code'];
        }

        return 'en';
    }



    public function getRequestMode(): ?string
    {
        return $this->requestMode;
    }

    public function setRequestMode(?string $requestMode): void
    {
        $this->requestMode = $requestMode;
    }

    public function getRequestCompanyId(): ?string
    {
        return $this->requestCompanyId;
    }

    public function setRequestCompanyId(?string $requestCompanyId): void
    {
        $this->requestCompanyId = $requestCompanyId;
    }

    public function getRequestCompanyInfo(): ?array
    {
        return $this->requestCompanyInfo;
    }

    public function setRequestCompanyInfo(?array $requestCompanyInfo): void
    {
        $this->requestCompanyInfo = $requestCompanyInfo;
    }

    public function getRequestVenueId(): ?string
    {
        return $this->requestVenueId;
    }

    public function setRequestVenueId(?string $requestVenueId): void
    {
        $this->requestVenueId = $requestVenueId;
    }

    public function getRequestVenueInfo(): ?array
    {
        return $this->requestVenueInfo;
    }

    public function setRequestVenueInfo(?array $requestVenueInfo): void
    {
        $this->requestVenueInfo = $requestVenueInfo;
    }

    public function getRequestLangCode(): string
    {
        return $this->requestLangCode;
    }

    public function setRequestLangCode(string $requestLangCode): void
    {
        $this->requestLangCode = $requestLangCode;
    }

    public function getRequestLangInfo(): ?array
    {
        return $this->requestLangInfo;
    }

    public function setRequestLangInfo(?array $requestLangInfo): void
    {
        $this->requestLangInfo = $requestLangInfo;
    }
}
