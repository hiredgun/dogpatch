<?php
namespace Dogpatch\Validators;

class Validator {
    /**
     * Bit flag indicating whether value can be null
     */
    const ALLOW_NULL = 1;

    /**
     * Bit flag indicating whether value can be empty array
     */
    const ALLOW_EMPTY_ARRAY = 2;

    /**
     * Bit flag indicating whether value should be presented in a response
     */
    const OPTIONAL = 4;

    /**
     * Array of message
     *
     * @var array
     */
    private $messages = array();

    /**
     * Array of validators
     *
     * @var array
     */
    private $validators;

    /**
     * Dat to validate
     *
     * @var array
     */
    private $data;

    /**
     * Performs validation
     *
     * @return bool
     */
    public function isValid() {
        $this->validate($this->data, $this->validators);
        return empty($this->messages);
    }

    /**
     * Performs main validation
     *
     * @param array $data
     * @param array $validationConfigs
     * @param null|string $validationKey
     * @throws \Exception
     */
    private function validate($data, $validationConfigs, $validationKey = null) {
        if (!is_array($validationConfigs)) {
            throw new \Exception('Array expected, ' . gettype($validationConfigs) . ' given');
        }

        foreach ($data as $key => $value) {
            if (is_int($key) || isset($validationConfigs[$key])) {
                $config = (is_int($key)) ? $validationConfigs:$validationConfigs[$key];
                $this->apply($key, $value, $config, $validationKey);
            } else {
                if (isset($validationKey)) {
                    $this->messages['missingValidators'][$validationKey][] = $key;
                } else {
                    $this->messages['missingValidators'][] = $key;
                }
            }
        }

        $missingVields = array_diff_key($validationConfigs, $data);
        foreach ($missingVields as $key => $validator) {
            if (isset($validator['options']) && !($validator['options'] & self::OPTIONAL)) {
                if (isset($validationKey)) {
                    $this->messages['missingFields'][$validationKey][] = $key;
                } else {
                    $this->messages['missingFields'][] = $key;
                }
            }
        }
    }

    public function apply($key, $value, $validationConfig, $validationKey = null) {
        $result = false;
        if (is_callable($validationConfig)) {
            $result = call_user_func($value);
        } else {
            if (isset($validationConfig['options'])) {
                if (is_null($value) && ($validationConfig['options'] & self::ALLOW_NULL)) {
                    return true;
                }
                if (is_array($value) && empty($value) && ($validationConfig['options'] & self::ALLOW_EMPTY_ARRAY)) {
                    return true;
                }
            }

            if (isset($validationConfig['validators'])) {
                foreach ($validationConfig['validators'] as $validatorConfig) {
                    /** @var ValidatorInterface $validator */
                    $validator = InputValidatorsFactory::create($validatorConfig);
                    $result = $validator->isValid($value);
                    if (!$result) {
                        if (isset($validationKey)) {
                            $this->messages['errors'][$validationKey][] = $key . ': ' . $validator->getMessage();
                        } else {
                            $this->messages['errors'][] = $key . ': ' . $validator->getMessage();
                        }
                    }
                }
            }

            if (isset($validationConfig['subEntity'])) {
                $newValidationKey = ($validationKey) ? $validationKey . ':' . $key : $key;
                $this->validate($value, $validationConfig['subEntity'], $newValidationKey);
            }
        }

        return $result;
    }

    public function getMessages() {
        return $this->messages;
    }

    public function setData($data) {
        $this->data = $data;
    }

    /**
     * Sets validators
     *
     * @param $validators
     * @return mixed
     */
    public function setValidators($validators) {
        $this->validators = $validators;
    }
}
