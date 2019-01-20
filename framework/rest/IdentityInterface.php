<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
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