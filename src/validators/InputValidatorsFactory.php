<?php
namespace Dogpatch\Validators;

class InputValidatorsFactory {

    public static function create($validator) {
        $class = __NAMESPACE__ . '\\' . $validator['name'];
        if (class_exists($class)) {
            $options = (isset($validator['options'])) ? $validator['options'] : null;

            return new $class($options);
        } else {
            throw new \Exception('Unknown validator ' . $validator['name']);
        }
    }
}
