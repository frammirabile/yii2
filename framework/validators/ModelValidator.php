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
     * Validates one or more models
     *
     * @param Model $value
     * @return array|null
     */
    protected function validateValue($value): ?array
    {
        if (!is_array($value))
            $value = [$value];

        foreach ($value as $model)
            if (!($model instanceof Model) || !$model->validate())
                return [current($model->firstErrors) ?? 'Invalid model', []];

        return null;
    }
}