<?php
namespace Dogpatch\Validators;

/**
 * Class String
 *
 * @package Dogpatch\Validators
 */
class String extends InputValidator {

    /**
     * Perform main validation
     *
     * @param array $value
     * @return bool
     */
    public function isValid($value) {
        $result = is_string($value);

        if (!$result) {
            $this->setMessage('string value expected ' . gettype($value) . ' given');
        }

        return $result;
    }
}
