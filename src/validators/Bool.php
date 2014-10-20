<?php
namespace Dogpatch\Validators;

/**
 * Class Bool
 *
 * @package Dogpatch\Validators
 */
class Bool extends InputValidator{

    public function isValid($value) {
        $result = is_bool($value);

        if (!$result) {
            $this->setMessage('boolean value expected ' . gettype($value) . ' given');
        }

        return $result;

    }
}
