<?php
namespace Dogpatch\Validators;

/**
 * Class Regexp
 *
 * @package Dogpatch\Validators
 */
class Regexp extends InputValidator {

    /**
     * Performs validation
     *
     * @param array $value
     * @throws \Exception Cannot find pattern
     * @return bool
     */
    public function isValid($value) {
        if (!isset($this->options['pattern'])) {
            throw new \Exception('Regexp validator - cannot find pattern');
        }
        $result = (bool) preg_match($this->options['pattern'], $value);

        if (!$result) {
            $this->setMessage('cannot find pattern: ' . $this->options['pattern']);
        }

        return $result;
    }
}
