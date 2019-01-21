<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

/**
 * Rest identity interface
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
interface IdentityInterface
{
    /**
     * Finds an identity by the given id
     *
     * @param int $id the id to be looked for
     * @return IdentityInterface|null the identity object that matches the given id
     */
    public static function findById(int $id): ?IdentityInterface;

    /**
     * Returns the id that uniquely identifies an identity
     *
     * @return int the id that uniquely identifies an identity
     */
    public function getId(): int;

    /**
     * Returns the identity email
     *
     * @return string the identity email
     */
    public function getEmail(): string;
}