<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use yii\base\InvalidConfigException;

/**
 * FormatConverter provides functionality to convert between different formatting pattern formats
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @author Enrica Ruedin <e.ruedin@guggach.com>
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 2.0
 */
class FormatConverter extends BaseFormatConverter
{
    /**
     * @param string $value
     * @return string
     * @throws InvalidConfigException
     */
    public static function asCurrency(string $value): string
    {
        return \Yii::$app->formatter->asCurrency($value);
    }

    /**
     * @param int|string|\DateTime $value
     * @return string
     * @throws InvalidConfigException
     */
    public static function asDate($value): string
    {
        return \Yii::$app->formatter->asDate($value);
    }

    /**
     * @param int|string|\DateTime $value
     * @return string
     * @throws InvalidConfigException
     */
    public static function asDateTime($value): string
    {
        return \Yii::$app->formatter->asDateTime($value);
    }

    /**
     * @param string $value
     * @return int
     */
    public static function asSeconds(string $value): int
    {
        return strtotime('+'.$value) - time();
    }

    /**
     * @param int|string|\DateTime $value
     * @return string
     * @throws InvalidConfigException
     */
    public static function asTime($value): string
    {
        return \Yii::$app->formatter->asTime($value);
    }

    /**
     * @param int|string|\DateTime $value
     * @return string
     * @throws InvalidConfigException
     */
    public static function asYear($value): string
    {
        return \Yii::$app->formatter->asDate($value, 'Y');
    }
}