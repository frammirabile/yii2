<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

/**
 * Rest response
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class Response extends \yii\web\Response
{
    /**
     * {@inheritdoc}
     */
    public $format = self::FORMAT_JSON;
}