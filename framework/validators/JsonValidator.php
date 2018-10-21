<?php
namespace yii\validators;

use yii\base\InvalidArgumentException;
use yii\helpers\Json;

/**
 * JsonValidator validates that the attribute value is a json.
 *
 * @author Francesco Ammirabile
 * @since 1.0
 */
class JsonValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        try {
            if (!is_string($value))
                $value = Json::encode($value);

            Json::decode($value);

            return null;
        } catch (InvalidArgumentException $e) {
            return ['Invalid json', []];
        }
    }
}