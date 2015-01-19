<?php
namespace Dogpatch\Validators;

/**
 * Class Validator
 *
 * @package Dogpatch\Validators
 */
class Validator {
    /**
     * Bit flag indicating whether value can be null
     */
    const ALLOW_NULL = 1;

    /**
     * Bit flag indicating whether value can be an empty array
     */
    const ALLOW_EMPTY_ARRAY = 2;

    /**
     * Bit flag indicating whether value should be presented in a response
     */
    const OPTIONAL = 4;

    /**
     * Error key
     */
    const ERROR = 'errors';

    /**
     * Missing field key
     */
    const MISSING_FIELDS = 'missingFields';

    /**
     * Missing validator key
     */
    const MISSING_VALIDATORS = 'missingValidators';

    /**
     * Array of messages
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
    private function validate(array $data, $validationConfigs, $validationKey = null) {
        if (!is_array($validationConfigs)) {
            throw new \InvalidArgumentException('Validation config must be array, ' . gettype($validationConfigs) . ' given');
        }

        foreach ($data as $key => $value) {
            if (($valueIsCollection = is_int($key)) || isset($validationConfigs[$key])) {
                $config = ($valueIsCollection) ? $validationConfigs : $validationConfigs[$key];
                $this->apply($key, $value, $config, $validationKey);
            } else {
                $this->addMessage('', $key, $validationKey, self::MISSING_VALIDATORS);
            }
        }

        $this->logMissingFields($validationConfigs, $data, $validationKey);
    }

    /**
     * Runs each validator against provided value
     *
     * @param string $key
     * @param mixed $value
     * @param string $validationKey
     * @throws \Exception
     */
    private function runValidators($key, $value, $validationKey, $validationConfig) {
        if (isset($validationConfig['validators'])) {
            if (!is_array($validationConfig['validators'])) {
                throw new \Exception('Invalid validaotrs definition for key ' . $key);
            }

            foreach ($validationConfig['validators'] as $validator) {
                if (is_array($validator)) {
                    $validator = InputValidatorsFactory::create($validator);
                } elseif(!$validator instanceof ValidatorInterface) {
                    throw new \Exception('Invalid validator config for ' . $key . ', expected array ' . gettype($validator) . ', given');
                }

                /** @var ValidatorInterface $validator */
                $result = $validator->isValid($value);
                if (!$result) {
                    $this->addMessage($validator->getMessages(), $key, $validationKey, self::ERROR);
                }
            }
        }
    }

    /**
     * Starts validation of subEntities
     */
    private function evaluateSubEntity($key, $value, $validationKey, $validationConfig) {
        if (isset($validationConfig['subEntity'])) {
            if (is_array($value)) {
                $newValidationKey = ($validationKey) ? $validationKey . ':' . $key : $key;
                $this->validate($value, $validationConfig['subEntity'], $newValidationKey);
            } else {
                $this->addMessage('subEntity expected, ' . gettype($value) . ' given', $key, $validationKey, self::ERROR);
            }
        }
    }

    /**
     * Applies given validator to its corresponding value
     *
     * @param string $key
     * @param mixed $value
     * @param array $validationConfig
     * @param null $validationKey
     * @throws \Exception
     */
    public function apply($key, $value, $validationConfig, $validationKey = null) {
        if (isset($validationConfig['options'])) {
            if (is_null($value) && ($validationConfig['options'] & self::ALLOW_NULL)) {
                return;
            }
            if (is_array($value) && empty($value) && ($validationConfig['options'] & self::ALLOW_EMPTY_ARRAY)) {
                return;
            }
        }

        $this->runValidators($key, $value, $validationKey, $validationConfig);
        $this->evaluateSubEntity($key, $value, $validationKey, $validationConfig);
    }

    /**
     * Adds message to messages haystack
     *
     * @param string $message
     * @param string $key
     * @param string $validationKey
     * @param string $type
     */
    private function addMessage($message, $key, $validationKey, $type) {
        $message = !empty($message) ? ': ' . $message : $message;
        if (isset($validationKey)) {
            $this->messages[$type][$validationKey][] = $key . $message;
        } else {
            $this->messages[$type][] = $key . $message;
        }
    }

    /**
     * Logs fields not presented in a response
     *
     * @param array $validationConfigs
     * @param array $data
     * @param string $validationKey
     */
    private function logMissingFields($validationConfigs, $data, $validationKey) {
        $missingFields = array_diff_key($validationConfigs, $data);
        foreach ($missingFields as $key => $validator) {
            if ((!isset($validator['options']) && $key != 'subEntity') ||
                (isset($validator['options']) && !($validator['options'] & self::OPTIONAL))) {
                $this->addMessage('', $key, $validationKey, self::MISSING_FIELDS);
            }
        }
    }

    /**
     * Returns logged messages
     *
     * @return array
     */
    public function getMessages() {
        return $this->messages;
    }

    /**
     * Array of response fields
     *
     * @param array $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    /**
     * Sets validators
     *
     * @param array $validators
     * @return mixed
     */
    public function setValidators(array $validators) {
        $this->validators = $validators;
    }
}
