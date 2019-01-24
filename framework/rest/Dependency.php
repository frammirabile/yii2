<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\base\{Component, InvalidConfigException};
use yii\helpers\Inflector;

/**
 * Rest active dependency
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
        if (!in_array(ActiveRecord::class, class_implements($this->class)))
            throw new InvalidConfigException(\Yii::t('yii', 'Dependency class must implement ActiveRecord'));

        if ($this->foreignKey === null) {
            /** @var ActiveRecord $modelClass */
            $modelClass = \Yii::$app->controller->modelClass;
            $this->foreignKey = Inflector::underscore($modelClass::name($this->collection)).$modelClass::foreignKey();
        }

        if ($this->name === null) {
            /** @var ActiveRecord $class */
            $class = $this->class;
            $this->name = Inflector::variablize($class::name($this->collection));
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