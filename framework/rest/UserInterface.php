<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

/**
 * Rest user interface
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
interface UserInterface
{
    /**
     * Finds a user by the given id
     *
     * @param int $id the id to be looked for
     * @return self|null the user object that matches the given id
     */
    public static function findById(int $id): ?self;

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
     * Returns the id that uniquely identifies a user
     *
     * @return int the id that uniquely identifies a user
     */
    public function getId(): int;

    /**
     * Returns the username
     *
     * @return string the username
     */
    public function getUsername(): string;

    /**
     * Sets the password
     *
     * @param string $password the password
     * @return void
     */
    public function setPassword(string $password): void;

    /**
     * Returns whether the password is valid
     *
     * @param string $password the password
     * @return bool whether the password is valid
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
     * Returns whether the user is active
     *
     * @return bool whether the user is active
     */
    public function getIsActive(): bool;

    /**
     * Sets whether the user is active
     *
     * @param bool $active
     * @return void
     */
    public function setIsActive(bool $active): void;

    /**
     * Returns the identity
     *
     * @return IdentityInterface|null the identity
     */
    public function getIdentity(): ?IdentityInterface;

    /**
     * Sets the identity
     *
     * @param IdentityInterface $identity the identity
     */
    public function setIdentity(IdentityInterface $identity): void;

    /**
     * Returns the token
     *
     * @return TokenInterface|null the token
     */
    public function getToken(): ?TokenInterface;
}