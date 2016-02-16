<?php

namespace MattJanssen\ApiResponseBundle\Model;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;

/**
 * Error Model Added to Failed API Form Responses
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ApiResponseFormErrorData implements \JsonSerializable
{
    /**
     * @var FormErrorIterator
     */
    private $formErrorIterator;

    /**
     * Constructor
     *
     * @param FormErrorIterator $formErrorIterator Deep, unflattened form errors.
     */
    public function __construct(FormErrorIterator $formErrorIterator)
    {
        $this->formErrorIterator = $formErrorIterator;
    }

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        $formErrors = [];
        $childIterators = [];

        foreach ($this->formErrorIterator as $error) {
            if ($error instanceof FormError) {
                $formErrors[] = $error->getMessage();
            } elseif ($error instanceof FormErrorIterator) {
                $childName = $error->getForm()->getName();
                $childIterators[$childName] = new ApiResponseFormErrorData($error);
            }
        }

        $data = [
            'errors' => $formErrors,
        ];

        if ($childIterators) {
            $data['children'] = $childIterators;
        }

        return $data;
    }
}
