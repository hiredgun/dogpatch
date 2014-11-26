<?php
namespace Dogpatch\Validators;

class InputValidatorsFactory {

    public static function create(array $validator) {
        if (!isset($validator['name'])) {
            throw new \Exception('Invalid validator\'s definition, cannot find validator\'s name');
        }

        $class = __NAMESPACE__ . '\\' . $validator['name'];
        if (class_exists($class)) {
            $options = (isset($validator['options'])) ? $validator['options'] : null;

            return new $class($options);
        } else {
            throw new \Exception('Unknown validator ' . $validator['name']);
        }
    }
}
