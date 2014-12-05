<?php
namespace Dogpatch\Validators;

/**
 * Class InputValidator
 *
 * @package Dogpatch\Validators
 */
abstract class InputValidator implements ValidatorInterface {
    /**
     * Error message
     *
     * @var array
     */
    private $message;

    /**
     * Options list
     *
     * @var array
     */
    protected $options;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options) {
        $this->options = $options;
    }

    /**
     * Returns error message
     *
     * @return array
     */
    public function getMessages() {
        return $this->message;
    }

    /**
     * Set message
     *
     * @param string $message
     */
    public function setMessage($message){
        $this->message = $message;

    }
}
