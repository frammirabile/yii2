<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\validators;

use yii\base\InvalidArgumentException;
use yii\helpers\Json;

/**
 * Json validator
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class JsonValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function validateValue($value): ?array
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