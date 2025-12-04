<?php

namespace App\Classes\Base;

use App\Classes\Api\ApiResult;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractController
{
    use BaseSubscribedTrait;

    public const REQUEST_PARAM_MODE_ALL = 'all';
    public const REQUEST_PARAM_MODE_COMPANY = 'company';
    public const REQUEST_PARAM_MODE_VENUE = 'venue';
    public const REQUEST_PARAM_UUID = '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}';

    public const REQUEST_PARAM_LANG_CODE = '[a-zA-F]{2}';

    public const NotifySuccess = 'success';

    public const NotifyInfo = 'info';

    public const NotifyWarning = 'warning';

    public const NotifyDanger = 'danger';

    public function addSuccessFlash(string $message): void
    {
        $this->addFlash(self::NotifySuccess, $message);
    }

    public function addInfoFlash(string $message): void
    {
        $this->addFlash(self::NotifyInfo, $message);
    }

    public function addWarningFlash(string $message): void
    {
        $this->addFlash(self::NotifyWarning, $message);
    }

    public function addDangerFlash(string $message): void
    {
        $this->addFlash(self::NotifyDanger, $message);
    }

    public function handleApiResultFormErrors(ApiResult $apiResult, $form): void
    {
        $fieldErrors = $apiResult->getFieldValidationErrors();
        if (!empty($fieldErrors)) {
            foreach ($fieldErrors as $fieldError) {
                $collectionFields = $this->validateCollectionFieldError($fieldError['name']);
                if (false !== $collectionFields) {
                    $itemForm = $form->get($collectionFields['collection'])
                        ->get($collectionFields['index'] ?? null)
                        ->get($collectionFields['field'] ?? null);
                    if ($itemForm) {
                        $itemForm->addError(new FormError($fieldError['message']));
                    }
                } else {
                    $form->get($fieldError['name'])->addError(new FormError($fieldError['message']));
                }
            }
        }
        $paramErrors = $apiResult->getParamValidationErrors();
        if (!empty($paramErrors)) {
            foreach ($paramErrors as $paramError) {
                $this->addDangerFlash($paramError['name'].' - '.$paramError['message']);
            }
        }
        if (!$apiResult->hasValidationErrors() && $apiResult->hasExceptionMessage()) {
            $form->addError(new FormError($apiResult->getExceptionMessage()));
        }
    }

    public function validateCollectionFieldError($variable): array|false
    {
        if (!is_string($variable)) {
            return false;
        }

        if (preg_match('/^([^.]+)\.(\d+)\.([^.]+)$/', $variable, $matches)) {
            return [
                'full' => $matches[0],
                'collection' => $matches[1],
                'index' => (int) $matches[2],
                'field' => $matches[3],
            ];
        }

        return false;
    }

    public function callbackResponse(Request $request, mixed $data, int $status = 200): Response
    {
        $serializedReturn = $this->getSerializer()->serialize($data, 'json');
        if ($request->query->has('callback')) {
            $callback = $request->query->get('callback');
            $serializedReturn = $callback.'('.$serializedReturn.')';
        }

        return new Response($serializedReturn, $status, ['Content-Type' => 'application/json']);
    }
}
