<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\helpers;

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
     * @param int $interval
     * @param string $unity
     * @param string $date
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public static function add(int $interval, string $unity, string $date = 'now', string $format = 'date'): string
    {
        return static::format((new \DateTime($date))->add(new \DateInterval("P$interval$unity")), $format);
    }

    /**
     * @param int|string|\DateTime $time
     * @param string $format
     * @return string
     */
    public static function format($time, string $format = 'date'): string
    {
        return FormatConverter::{'as'.ucfirst($format)}($time);
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

    /**
     * @param string $format
     * @return string
     */
    public static function now(string $format = 'date'): string
    {
        return static::format('now', $format);
    }
}