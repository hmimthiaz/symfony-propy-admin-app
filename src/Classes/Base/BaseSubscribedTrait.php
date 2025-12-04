<?php

namespace App\Classes\Base;

use Adbar\Dot;
use App\Kernel;
use Hidehalo\Nanoid\Client;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

trait BaseSubscribedTrait
{
    public const CACHE_FIFTEEN_MINUTES = 60 * 15;
    public const CACHE_TIME_HOUR = 60 * 60;

    public const CACHE_TIME_DAY = 60 * 60 * 24;

    public const CACHE_TIME_WEEK = 60 * 60 * 24 * 7;

    public ?Client $nanoidClient = null;

    private ?Dot $appParams = null;

    private ?\DateTimeZone $currentTimeZone = null;

    private ?\DateTimeZone $utcTimeZone = null;

    public static function getSubscribedServices(): array
    {
        return [

            'router' => '?'.RouterInterface::class,
            'cache.app' => '?'.CacheItemPoolInterface::class,
            'http_kernel' => '?'.HttpKernelInterface::class,
            'kernel' => '?'.Kernel::class,
            'monolog.logger' => '?'.LoggerInterface::class,
            'serializer' => '?'.SymfonySerializerInterface::class,
            'validator' => '?'.ValidatorInterface::class,
            'parameter_bag' => '?'.ContainerBagInterface::class,
            'request_stack' => '?'.RequestStack::class,
            'security.authorization_checker' => '?'.AuthorizationCheckerInterface::class,
            'twig' => '?'.Environment::class,
            'form.factory' => '?'.FormFactoryInterface::class,
            'security.token_storage' => '?'.TokenStorageInterface::class,
            'security.csrf.token_manager' => '?'.CsrfTokenManagerInterface::class,
        ];
    }

    // Start App Services


    // End App Services

    public function getRouter(): RouterInterface
    {
        return $this->container->get('router');
    }

    public function getCache(): CacheItemPoolInterface
    {
        return $this->container->get('cache.app');
    }

    public function getHttpKernel(): HttpKernelInterface
    {
        return $this->container->get('http_kernel');
    }

    public function getKernel(): Kernel
    {
        return $this->container->get('kernel');
    }

    public function getLogger(): LoggerInterface
    {
        return $this->container->get('monolog.logger');
    }

    public function getSerializer(): SymfonySerializerInterface
    {
        return $this->container->get('serializer');
    }

    public function getValidator(): ValidatorInterface
    {
        return $this->container->get('validator');
    }

    protected function getParameter(string $name): array|bool|string|int|float|\UnitEnum|null
    {
        if (!$this->container->has('parameter_bag')) {
            throw new ServiceNotFoundException('parameter_bag.', null, null, [], sprintf('The "%s::getParameter()" method is missing a parameter bag to work properly. Did you forget to register your controller as a service subscriber? This can be fixed either by using autoconfiguration or by manually wiring a "parameter_bag" in the service locator passed to the controller.', static::class));
        }

        return $this->container->get('parameter_bag')->get($name);
    }

    public function getRequestStack(): RequestStack
    {
        return $this->container->get('request_stack');
    }

    public function getAuthChecker(): AuthorizationCheckerInterface
    {
        return $this->container->get('security.authorization_checker');
    }

    public function getTwig(): Environment
    {
        return $this->container->get('twig');
    }

    public function getTokenStorage(): TokenStorageInterface
    {
        return $this->container->get('security.token_storage');
    }

    public function getCsrfTokenManager(): CsrfTokenManagerInterface
    {
        return $this->container->get('security.csrf.token_manager');
    }

    // Custom Methods

    public function isValidUUID(string $uuid): bool
    {
        return 1 === preg_match('/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/i', $uuid);
    }

    public function isValidLanguageCode($code): bool
    {
        return 1 === preg_match('/^[a-zA-Z]{2}$/', $code);
    }

    public function getAppParams(): Dot
    {
        if (is_null($this->appParams)) {
            $this->appParams = new Dot($this->getParameter('app'));
        }

        return $this->appParams;
    }

    public function getNanoId(): string
    {
        if (is_null($this->nanoidClient)) {
            $this->nanoidClient = new Client();
        }

        return $this->nanoidClient->generateId();
    }

    public function getRequestIP(): string
    {
        $request = $this->getRequestStack()->getCurrentRequest();
        if (is_null($request)) {
            return '127.0.0.1';
        }
        if ($request->headers->has('x-forwarded-for')) {
            return $request->headers->get('x-forwarded-for');
        }

        if ($request->headers->has('x-real-ip')) {
            return $request->headers->get('x-real-ip');
        }

        return $request->getClientIp();
    }

    public function generateUrl(string $route, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return $this->getRouter()->generate($route, $parameters, $referenceType);
    }

    /**
     * Get the current user from the token storage.
     *
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface
    {
        if (null === $token = $this->getTokenStorage()->getToken()) {
            return null;
        }

        return $token->getUser();
    }

    public function getCurrentTimeZone(): ?\DateTimeZone
    {
        return $this->currentTimeZone;
    }

    public function setCurrentTimeZone(?\DateTimeZone $currentTimeZone): void
    {
        $this->currentTimeZone = $currentTimeZone;
    }

    public function formatToTimezone($dateTime, $format = 'd-M-y h:i A'): string
    {
        if (is_string($dateTime) && !empty($dateTime)) {
            $dateTime = new \DateTime($dateTime);
        }

        if (!($dateTime instanceof \DateTime) && !($dateTime instanceof \DateTimeImmutable)) {
            return '';
        }

        if (is_null($this->currentTimeZone)) {
            $timezone = 'Asia/Dubai';
            $this->currentTimeZone = new \DateTimeZone($timezone);
        }

        if ($dateTime instanceof \DateTimeImmutable) {
            $dateTimeWithZone = $dateTime->setTimezone($this->currentTimeZone);
        } else {
            $dateTime->setTimezone($this->currentTimeZone);
            $dateTimeWithZone = $dateTime;
        }

        return $dateTimeWithZone->format($format);
    }

    public function formatToUTC($dateTime, $format = DATE_ATOM): string
    {
        if (!($dateTime instanceof \DateTime) && !($dateTime instanceof \DateTimeImmutable)) {
            return '';
        }
        if (is_null($this->utcTimeZone)) {
            $this->utcTimeZone = new \DateTimeZone('UTC');
        }
        $dateTime->setTimezone($this->utcTimeZone);

        return $dateTime->format($format);
    }

    public function formatConstantValue(string $value): string
    {
        if ('na' == strtolower($value)) {
            return ' - ';
        }
        $value = strtolower($value);
        $value = str_replace('-', ' ', $value);

        return ucwords($value);
    }

    public function formatToFloat(int $value): string
    {
        $floatValue = $value / 100;

        return number_format($floatValue, 2);
    }

    public function formatBool2Icon(bool $value, float $opacity = 0.3): string
    {
        return $value ? '<i class="rbi rbi-circle-yes"></i>' : '<i class="rbi rbi-circle-no" style="opacity: '.$opacity.';"></i>';
    }
}
