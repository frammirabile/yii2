<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\base\{Component, InvalidConfigException};

/**
 * Rest dependency
 *
 * @property-read bool $isCollection
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class Dependency extends Component
{
    /**
     * @var string
     */
    public $primaryClass;

    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $foreignKey;

    /**
     * @var bool
     */
    public $collection = false;

    /**
     * @var string|null
     */
    public $sort;

    /**
     * @return void
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        if ($this->foreignKey === null) {
            /** @var ActiveRecord $primaryClass */
            $primaryClass = $this->primaryClass;
            $this->foreignKey = $primaryClass::foreignKey();
        }

        if ($this->sort !== null) {
            /** @var ActiveRecord $class */
            $class = $this->class;

            if ($class::getTableSchema()->getColumn($this->sort) === null)
                throw new InvalidConfigException('Dependency '.$class::name()." cannot be sorted by $this->sort");
        }
    }

    /**
     * @return bool
     */
    public function getIsCollection(): bool
    {
        return $this->collection;
    }
}