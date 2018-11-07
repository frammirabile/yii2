<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\helpers;

/**
 * String helper
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class StringHelper extends BaseStringHelper
{
    /**
     * Converts a UUID into its binary representation
     *
     * @param string $uuid UUID
     * @return string binary representation
     */
    public static function uuid2Binary(string $uuid): string
    {
        return hex2bin(str_replace('-', '', $uuid));
    }

    /**
     * Converts a binary into its UUID representation
     *
     * @param string $binary binary
     * @return string UUID representation
     */
    public static function binaryToUuid(string $binary): string
    {
        return preg_replace('/^(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})$/i', '$1-$2-$3-$4-$5', substr(bin2hex($binary), 0, 32));
    }
}