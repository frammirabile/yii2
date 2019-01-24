<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\behaviors;

use yii\base\{Behavior, InvalidConfigException};
use yii\db\ActiveRecord;
use yii\helpers\Inflector;

/**
 * Status behavior
 *
 * @property ActiveRecord $owner
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 *
 * @tbd sistemare
 */
class StatusBehavior extends Behavior
{
    /**
     * @var string
     */
    public $attribute = 'status';

    /**
     * @var int
     */
    public $default = 1;

    /**
     * @var string[]
     */
    private $_status = [];

    /**
     * {@inheritdoc}
     */
    public function events(): array
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            ActiveRecord::EVENT_AFTER_VALIDATE => 'afterValidate',
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert'
        ];
    }

    /**
     * @return void
     * @throws InvalidConfigException
     */
    public function beforeValidate(): void
    {
        if ($this->owner->isAttributeActive($this->attribute) && !$this->hasStatus($this->getStatus()))
            $this->owner->addError($this->attribute, 'Invalid status');
    }

    /**
     * @return void
     * @throws InvalidConfigException
     */
    public function afterValidate(): void
    {
        if ($this->owner->isAttributeActive($this->attribute) && is_string($attribute = $this->owner->getAttribute('status')))
            $this->owner->setAttribute($this->attribute, $this->getStatus($attribute));
    }

    /**
     * @return void
     * @throws InvalidConfigException
     * @throws \ReflectionException
     */
    public function afterFind(): void
    {
        if (is_array($status = (new \ReflectionClass($this->owner))->getConstant('STATUS')))
            $this->_status = array_map(function($status) { return strtolower(Inflector::humanize($status)); }, array_flip($status));

        if (empty($this->_status))
            throw new InvalidConfigException(\Yii::t('api', 'Status must be set'));
    }

    /**
     * @return void
     */
    public function beforeInsert(): void
    {
        $this->owner->setAttribute($this->attribute, $this->default);
    }

    /**
     * @return void
     */
    public function afterInsert(): void
    {
        $this->owner->trigger(ActiveRecord::EVENT_AFTER_FIND);
    }

    /**
     * @param null|string $text
     * @return int|false
     * @throws InvalidConfigException
     */
    public function getStatus(?string $text = null)
    {
        if ($text === null) {
            if (!$this->owner->hasProperty($this->attribute))
                throw new InvalidConfigException(\Yii::t('api', 'Current status must be set'));

            /** @noinspection PhpUndefinedFieldInspection */
            return $this->owner->getAttribute($this->attribute);
        }

        return array_search($text, $this->_status);
    }

    /**
     * @param null|int $id
     * @return string|false
     * @throws InvalidConfigException
     */
    public function getStatusText(?int $id = null)
    {
        if ($id === null)
            $id = $this->getStatus();

        return $this->hasStatus($id) ? $this->_status[$id] : false;
    }

    /**
     * @return string[]
     */
    public function getStates(): array
    {
        return $this->_status;
    }

    /**
     * @param int|string $status
     * @return bool
     */
    public function hasStatus($status): bool
    {
        return is_int($status) ? isset($this->_status[$status]) : in_array($status, $this->_status);
    }
}