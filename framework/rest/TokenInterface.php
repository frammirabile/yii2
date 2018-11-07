<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

/**
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
interface TokenInterface
{
    /**
     * Finds a token by the given identity
     *
     * @param IdentityInterface the identity to be looked for
     * @return TokenInterface|null the token object that matches the given identity
     */
    public static function findByIdentity(IdentityInterface $identity): ?TokenInterface;

    /**
     * Finds a token by the given key
     *
     * @param string the key to be looked for
     * @return TokenInterface|null the token object that matches the given key
     */
    public static function findByKey(string $key): ?TokenInterface;

    /**
     * Finds a token by the given refresh one
     *
     * @param string the refresh token to be looked for
     * @return TokenInterface|null the token object that matches the given refresh one
     */
    public static function findByRefreshToken(string $refreshToken): ?TokenInterface;

    /**
     * Returns the expiration
     *
     * @return int|string|null the expiration
     */
    public static function getExpiration();

    /**
     * Returns the validity leeway
     *
     * @return int the validity leeway
     */
    public static function getLeeway(): int;

    /**
     * Converts a token object into its string representation
     *
     * @return string the token string
     */
    public function __toString(): string;

    /**
     * Returns the user's ID
     *
     * @return int the user's ID
     */
    public function getUserId(): int;

    /**
     * Returns the expiration time
     *
     * @return int|null the expiration time
     */
    public function getExpiresAt(): ?int;

    /**
     * Returns whether the token is valid
     *
     * @return bool whether the token is valid
     */
    public function isValid(): bool;
}