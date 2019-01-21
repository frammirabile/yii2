<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\behaviors;

/**
 * Expirable behavior
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class ExpirableBehavior extends TimestampBehavior
{
    /**
     * {@inheritdoc}
     */
    public $createdAtAttribute = 'expires_at';

    /**
     * {@inheritdoc}
     */
    public $updatedAtAttribute = false;

    /**
     * @var int|string|null
     */
    public $expiration;

    /**
     * {@inheritdoc}
     */
    protected function getValue($event): ?int
    {
        return $this->expiration === null ? null : (is_int($this->expiration) ? time() + $this->expiration : strtotime('+'.$this->expiration));
    }
}