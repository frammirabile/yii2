<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\db\ActiveQuery;

/**
 * Rest user interface
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
interface UserInterface
{
    /**
     * Finds a user by the given username
     *
     * @param string $username the username to be looked for
     * @return self|null the user object that matches the given username
     * Null should be returned if such a user cannot be found
     * or the user is not in an active state (disabled, deleted, etc.)
     */
    public static function findByUsername(string $username): ?self;

    /**
     * Finds a user by the given token
     *
     * @param TokenInterface $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return self|null the user object that matches the given token
     * Null should be returned if such a user cannot be found
     * or the user is not in an active state (disabled, deleted, etc.)
     */
    public static function findByAccessToken(TokenInterface $token, $type = null): ?self;

    /**
     * Returns the username
     *
     * @return string the username
     */
    public function getUsername(): string;

    /**
     * Returns whether the user password is valid
     *
     * @param string $password the user password
     * @return bool whether the user password is valid
     */
    public function validatePassword(string $password): bool;

    /**
     * Returns the password reset code
     *
     * @return string the password reset code
     */
    public function getPasswordResetCode(): string;

    /**
     * Returns whether the password reset code is valid
     *
     * @param string $passwordResetCode the password reset code
     * @return bool whether the password reset code is valid
     */
    public function validatePasswordResetCode(string $passwordResetCode): bool;

    /**
     * Returns the identity
     *
     * @return ActiveQuery the identity
     */
    public function getIdentity(): ActiveQuery;
}