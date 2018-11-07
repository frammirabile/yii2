<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\validators;

/**
 * Translation validator
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 *
 * @tbd
 */
class TranslationValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function validateValue($value): ?array
    {
        return is_array($value) && array_intersect($value = array_keys($value), $languages = \Yii::$app->params['languages']) == $value && in_array($languages[0], $value) ? null : ['Invalid translation', []];
    }
}