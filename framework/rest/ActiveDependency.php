<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\base\{BaseObject, InvalidConfigException};
use yii\db\ActiveRecordInterface;

/**
 * Rest active dependency
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class ActiveDependency extends BaseObject
{
    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $foreignKey;

    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $collection = false;

    /**
     * @return void
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        if (!in_array(ActiveRecordInterface::class, class_implements($this->class)))
            throw new InvalidConfigException(\Yii::t('yii', 'User class must implement UserInterface'));

        if ($this->foreignKey === null)
            $this->foreignKey = '';

        #tbd verifica esistenza chiave esterna

        if ($this->name === null)
            $this->name = '';
    }
}