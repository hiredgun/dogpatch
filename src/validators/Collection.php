<?php
namespace Dogpatch\Validators;

/**
 * Class Collection
 *
 * @package Dogpatch\Validators
 */
class Collection extends InputValidator {

    public function isValid($values) {
        if (!isset($this->options['validators'])) {
            throw new \Exception('Collection validator: nothing to do, no validators attached');
        }

        if (!is_array($values)) {
            $this->setMessage('Collection validator expects array, ' . gettype($values) . ' given');
        }

        $errors = array();
        foreach ($values as $key => $value) {
            foreach ($this->options['validators'] as $validatorConfig) {
                /** @var ValidatorInterface $validator */
                $validator = InputValidatorsFactory::create($validatorConfig);
                if (!$validator->isValid($value)) {
                    $errors[] = 'Invalid item ' . $key . ': ' . $validator->getMessage();
                }
            }
        }

        $result = true;
        if (!empty($errors)) {
            $result = false;
            $this->setMessage(var_export($errors, true));
        }

        return $result;
    }
}
