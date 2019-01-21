<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\validators;

use yii\base\Model;
use yii\helpers\{Inflector, StringHelper};

/**
 * Model validator
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class ModelValidator extends Validator
{
    /**
     * Validates a model
     *
     * @param Model $value
     * @return array|null
     */
    protected function validateValue($value): ?array
    {
        return $value instanceof Model && $value->validate() ? null : ['Invalid {model}', ['model' => Inflector::variablize(StringHelper::basename(get_class($value)))]];
    }
}