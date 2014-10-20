<?php


namespace Dogpatch\Validators;

/**
 * Class Int
 *
 * @package Dogpatch\Validators
 */
class Int extends InputValidator{

    public function isValid($value) {
        $result = is_int($value);

        if (!$result) {
            $this->setMessage('integer value expected ' . gettype($value) . ' given');
        }

        return $result;

    }
}
