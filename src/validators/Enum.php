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
            $this->setMessage('cannot find ' . $value . ' in haystack - allowed values: ' . $this->options['haystack']);
        }

        return $result;

    }
}
