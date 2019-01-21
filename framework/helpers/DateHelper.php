<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\helpers;

use DateTime;
use yii\base\InvalidConfigException;

/**
 * Date helper.
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class DateHelper
{
    /**
     * @param string $time
     * @param string $format
     * @return string
     */
    public static function format(string $time, string $format = 'date'): string
    {
        return FormatConverter::{'as'.ucfirst($format)}($time);
    }

    /**
     * @param string $format
     * @return string
     */
    public static function now(string $format = 'date'): string
    {
        return static::format('now', $format);
    }

    /**
     * @param int $year
     * @param int $month
     * @return string
     * @throws \Exception
     * @throws InvalidConfigException
     */
    public static function lastDay(int $year, int $month): string
    {
        return \Yii::$app->formatter->asDate((new DateTime("$year-$month-01"))->format('Y-m-t'));
    }
}