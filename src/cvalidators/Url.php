<?php
namespace Dogpatch\Validators;

/**
 * Class Url
 *
 * @package Dogpatch\Validators
 */
class Url extends InputValidator{

    public function isValid($value) {

        $result = filter_var($value, FILTER_VALIDATE_URL);

        if (!$result) {
            $this->setMessage($value . ' is not valid URL');
        }

        return $result;

    }
}
