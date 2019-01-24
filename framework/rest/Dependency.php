<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\base\Component;

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
     * @return void
     */
    public function init(): void
    {
        if ($this->foreignKey === null) {
            /** @var ActiveRecord $modelClass */
            $modelClass = \Yii::$app->controller->modelClass;
            $this->foreignKey = $modelClass::foreignKey();
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