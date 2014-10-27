<?php
namespace Dogpatch\Validators
;

/**
 * Interface ValidatorInterface
 *
 * @package Dogpatch
 */
interface ValidatorInterface {
    /**
     * Performs validation
     *
     * @param array $value
     * @return bool
     */
    public function isValid($value);

    /**
     * Returns error message
     *
     * @return array
     */
    public function getMessages();
}
