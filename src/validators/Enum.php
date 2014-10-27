<?php
namespace Dogpatch\Validators;

/**
 * Class Enum
 *
 * @package Dogpatch\Validators
 */
class Enum extends InputValidator{

    public function isValid($value) {
        if (!isset($this->options['haystack'])) {
            throw new \Exception('Enum validator - cannot find haystack');
        }

        $value = (string) $value;

        $result = in_array($value, $this->options['haystack']);

        if (!$result) {
            $allowedValue = implode(',', $this->options['haystack']);

            $this->setMessage('cannot find "' . $value . '" value in haystack - allowed values: ' .
                $allowedValue);
        }

        return $result;
    }
}
