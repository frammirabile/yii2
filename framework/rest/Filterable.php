<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

/**
 * Filterable interface
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
interface Filterable
{
    /**
     * @return array
     */
    public static function filters(): array;
}