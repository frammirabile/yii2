<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\helpers;

/**
 * Url provides a set of static methods for managing URLs.
 *
 * For more details and usage information on Url, see the [guide article on url helpers](guide:helper-url).
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class Url extends BaseUrl
{
    /**
     * Returns the domain
     *
     * @return string
     */
    public static function domain(): string
    {
        return preg_replace('/.*\.(.+\..+)/', '$1', parent::base(true));
    }
}