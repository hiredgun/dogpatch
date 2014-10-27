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
    private function validate(array $data, $validationConfigs, $validationKey = null) {
        if (!is_array($validationConfigs)) {
            throw new \Exception('Array expected, ' . gettype($validationConfigs) . ' given');
        }

        foreach ($data as $key => $value) {
            if (is_int($key) || isset($validationConfigs[$key])) {
                $config = (is_int($key)) ? $validationConfigs : $validationConfigs[$key];
                $this->apply($key, $value, $config, $validationKey);
            } else {
                $this->addMessage('', $key, $validationKey, self::MISSING_VALIDATORS);
            }
        }

        $missingFields = array_diff_key($validationConfigs, $data);
        foreach ($missingFields as $key => $validator) {
            if (isset($validator['options']) && !($validator['options'] & self::OPTIONAL)) {
                $this->addMessage('', $key, $validationKey, self::MISSING_FIELDS);
            }
        }
    }

    public function apply($key, $value, $validationConfig, $validationKey = null) {
        $result = false;
        if (isset($validationConfig['options'])) {
            if (is_null($value) && ($validationConfig['options'] & self::ALLOW_NULL)) {
                return true;
            }
            if (is_array($value) && empty($value) && ($validationConfig['options'] & self::ALLOW_EMPTY_ARRAY)) {
                return true;
            }
        }

        if (isset($validationConfig['validators'])) {
            if (!is_array($validationConfig['validators'])) {
                throw new \Exception('Invalid validaotrs definition for key ' . $key);
            }

            foreach ($validationConfig['validators'] as $validator) {
                if (!$validator instanceof ValidatorInterface) {
                    $validator = InputValidatorsFactory::create($validator);
                }

                /** @var ValidatorInterface $validator */
                $result = $validator->isValid($value);
                if (!$result) {
                    $this->addMessage($validator->getMessages(), $key, $validationKey, self::ERROR);
                }
            }
        }

        if (isset($validationConfig['subEntity'])) {
            if (is_array($value)) {
                $newValidationKey = ($validationKey) ? $validationKey . ':' . $key : $key;
                $this->validate($value, $validationConfig['subEntity'], $newValidationKey);
            } else {
                $this->addMessage('subEentity expected, ' . gettype($value) . ' given', $key, $validationKey, self::ERROR);
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
}
