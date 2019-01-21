<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

/**
 * Token interface
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
interface TokenInterface
{
    /**
     * Finds a token by the given user id
     *
     * @param int $userId the user id to be looked for
     * @return self|null the token object that matches the given user id
     */
    public static function findByUserId(int $userId): ?self;

    /**
     * Finds a token by the given string
     *
     * @param string $string the string to be looked for
     * @return self|null the token object that matches the given string
     */
    public static function findByString(string $string): ?self;

    /**
     * Finds a token by the given refresh one
     *
     * @param string $refreshToken the refresh token to be looked for
     * @return self|null the token object that matches the given refresh one
     */
    public static function findByRefresh(string $refreshToken): ?self;

    /**
     * Converts a token object into its string representation
     *
     * @return string the token string
     */
    public function __toString(): string;

    /**
     * Returns the user id
     *
     * @return int the user id
     */
    public function getUserId(): int;

    /**
     * Returns whether the token is valid
     *
     * @return bool whether the token is valid
     */
    public function isValid(): bool;
}