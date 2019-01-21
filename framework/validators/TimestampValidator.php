<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\validators;

use yii\base\InvalidConfigException;

/**
 * Timestamp validator
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 *
 * @tbd sistemare
 */
class TimestampValidator extends DateValidator
{
    /**
     * {@inheritdoc}
     * @return void
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        $this->type = parent::TYPE_DATETIME;
        $this->format = 'php:U';

        parent::init();
    }
}