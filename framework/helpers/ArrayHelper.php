<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

/**
 * ArrayHelper provides additional array functionality that you can use in your
 * application.
 *
 * For more details and usage information on ArrayHelper, see the [guide article on array helpers](guide:helper-array).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class ArrayHelper extends BaseArrayHelper
{
    /**
     * {@inheritdoc}
     */
    public static function replaceKeys(array $array, array $replacements): array
    {
        return self::setKeys($array, 'preg_replace', [array_keys($replacements), array_values($replacements)]);
    }

    /**
     * {@inheritdoc}
     */
    public static function variablizeKeys(array $array): array
    {
        return self::setKeys($array, [Inflector::class, 'variablize']);
    }

    /**
     * {@inheritdoc}
     */
    public static function underscoreKeys(array $array): array
    {
        return self::setKeys($array, [Inflector::class, 'underscore']);
    }

    /**
     * @param array $array
     * @param callable $callable
     * @param array $params
     * @return array
     */
    public static function setKeys(array $array, callable $callable, array $params = []): array
    {
        $result = [];
        foreach ($array as $key => $value)
            $result[call_user_func_array($callable, array_merge($params, [$key]))] = is_array($value) ? self::setKeys($value, $callable, $params) : $value;

        return $result;
    }
}