<?php

namespace MattJanssen\ApiResponseBundle\Exception;

use MattJanssen\ApiResponseBundle\Model\ApiResponseFormErrorData;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Exception for Invalid Form Submissions
 *
 * Adds the form error data to be serialized as errorData.
 */
class ApiFormException extends ApiResponseException
{
    /**
     * {@inheritdoc}
     *
     * @param FormInterface $form Form from which to show errors from.
     */
    public function __construct(
        FormInterface $form,
        $code = 0,
        $message = 'Invalid form submission.',
        $httpCode = Response::HTTP_BAD_REQUEST,
        $errorData = null,
        \Exception $previous = null
    ) {
        $errorData = new ApiResponseFormErrorData($form->getErrors(true, false));

        parent::__construct($message, $code, $httpCode, $errorData, $previous);
    }
}
