<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\db\ActiveQuery;

/**
 * Rest identity interface.
 *
 * @method static findIdentityByAccessToken(TokenInterface $token, $type = null) ?IdentityInterface
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
interface UserInterface extends \yii\web\IdentityInterface
{
    /**
     * Finds an identity by the given username
     *
     * @param string $username the username to be looked for
     * @return static|null the identity object that matches the given username
     */
    public static function findByUsername(string $username): ?IdentityInterface;

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
     * @return bool Whether the user password is valid
     */
    public function validatePassword(string $password): bool;

    /**
     * Returns the user reset password
     *
     * @return string the user reset password
     */
    public function getResetPassword(): string;

    /**
     * Returns the user identity
     *
     * @return ActiveQuery the user identity
     */
    public function getIdentity(): ActiveQuery;
}