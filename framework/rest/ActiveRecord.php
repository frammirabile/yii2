<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\behaviors\AttributeTypecastBehavior;
use yii\helpers\{ArrayHelper, Inflector, StringHelper};
use yii\validators\Validator;

/**
 * Rest active record
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
abstract class ActiveRecord extends \yii\db\ActiveRecord
{
    /**
     * The name of scenario for creating model
     */
    const SCENARIO_CREATE = 'create';

    /**
     * The name of scenario for updating model
     */
    const SCENARIO_UPDATE = 'update';

    /**
     * @var string the foreign key suffix
     */
    protected static $suffix = '_id';

    /**
     * @var bool|null
     */
    protected $savingNotAllowed;

    /**
     * @var static[]
     */
    private $_related = [];

    /**
     * @var Dependency[]
     */
    private $_dependencies = [];

    /**
     * @param bool $pluralize
     * @return string
     */
    public static function name(bool $pluralize = false): string {
        $name = StringHelper::basename(get_called_class());

        return $pluralize ? Inflector::pluralize($name) : $name;
    }

    /**
     * @param bool $pluralize
     * @return string
     */
    public static function property(bool $pluralize = false): string
    {
        return Inflector::variablize(static::name($pluralize));
    }

    /**
     * @return string
     */
    public static function foreignKey(): string
    {
        return Inflector::underscore(static::name()).static::$suffix;
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function init(): void
    {
        foreach ($this->dependencies() as $name => $config)
            $this->_dependencies[$name] = new Dependency(array_merge(['primaryClass' => static::class], is_array($config) ? $config : ['class' => $config]));

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        if (!$this->hasDependency($name))
            return parent::__get($name);

        $dependency = $this->_dependencies[$name];

        return $this->_related[$name] ?? ($this->isNewRecord ? null : ($this->_related[$name] = ($this->{'has'.($dependency->isCollection ? 'Many' : 'One')}($dependency->class, array_combine((array) $dependency->foreignKey, array_keys($this->getPrimaryKey(true))))->where($dependency->filter)->orderBy($dependency->sort))->{$dependency->isCollection ? 'all' : 'one'}())); #tbd rivedere logica chiavi esterne
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     *
     * @tbd aggiornamento delle dipendenze
     */
    public function __set($name, $value): void
    {
        if (!$this->hasDependency($name))
            parent::__set($name, $value);
        else {
            $dependency = $this->_dependencies[$name];

            if ($value instanceof $dependency->class || $value === null)
                $this->_related[$name] = $value;
            elseif ($dependency->isCollection)
                $this->_related[$name] = array_map(function($data) use($dependency) {
                    /** @var static $dependency */
                    $dependency = new $dependency->class;
                    $dependency->scenario = static::SCENARIO_CREATE;
                    $dependency->setAttributes($data);

                    return $dependency;
                }, $value);
            else {
                if (!isset($this->_related[$name]))
                    $this->_related[$name] = $this->isNewRecord ? new $dependency->class(['scenario' => static::SCENARIO_CREATE]) : $this->$name;

                $this->_related[$name]->load($value, '');
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function __unset($name): void
    {
        !$this->hasDependency($name)
            ? parent::__unset($name)
            : $this->_related[$name] = null;
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [AttributeTypecastBehavior::class];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        return $this->savingNotAllowed !== false
            ? parent::scenarios()
            : ArrayHelper::replaceKeys(parent::scenarios(), ['/'.self::SCENARIO_DEFAULT.'/' => static::SCENARIO_CREATE]);
    }

    /**
     * {@inheritdoc}
     *
     * @tbd sistemare
     */
    public function createValidators(): \ArrayObject
    {
        $validators = parent::createValidators();

        if (!empty($dependencies = array_keys($this->_dependencies))) {
            $validator = Validator::createValidator('model', $this, $dependencies);
            $validators->append($validator);
        }

        return $validators;
    }

    /**
     * {@inheritdoc}
     */
    public function activeAttributes(): array
    {
        $activeAttributes = parent::activeAttributes();
        $attributes = array_diff($activeAttributes, array_keys($this->attributes));

        foreach ($attributes as $key => $attribute)
            if ($this->hasAttribute($attribute .= static::$suffix))
                $activeAttributes[$key] = $attribute;

        return $activeAttributes;
    }

    /**
     * {@inheritdoc
     */
    public function getRelatedRecords(): array
    {
        return parent::getRelatedRecords() + $this->_related;
    }

    /**
     * {@inheritdoc}
     */
    public function transactions(): array
    {
        return [static::SCENARIO_CREATE => self::OP_INSERT];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert): bool
    {
        return parent::beforeSave($insert) && ($this->savingNotAllowed === null || !$this->savingNotAllowed && $insert);
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     *
     * @tbd sistemare e utilizzare transazioni
     */
    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);

        foreach ($this->_related as $name => $dependencies) {
            $dependency = $this->_dependencies[$name];

            /** @var static $class */
            $class = $dependency->class;

            if ($dependencies === null)
                $class::deleteAll([$dependency->foreignKey => $this->primaryKey]);
            else {
                if (!is_array($dependencies))
                    $dependencies = [$dependencies];

                foreach ($dependencies as $model) {
                    $model->setAttributes(array_combine((array) $dependency->foreignKey, array_values($this->getPrimaryKey(true))));
                    $model->save();
                }
            }
        }
    }

    /**
     * @return array
     */
    protected function dependencies(): array
    {
        return [];
    }

    /**
     * Returns whether the active record has a dependency with the specified name
     *
     * @param string $name the name of the dependency
     * @return bool whether the active record has a dependency with the specified name
     */
    private function hasDependency(string $name): bool
    {
        return isset($this->_dependencies[$name]);
    }
}
