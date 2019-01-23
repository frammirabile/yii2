<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\behaviors\AttributeTypecastBehavior;
use yii\helpers\{Inflector, StringHelper};
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
     * @var bool|null
     */
    protected $savingNotAllowed;

    /**
     * @var string the active attribute suffix
     */
    protected $suffix = '_id';

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
     * {@inheritdoc}
     *
     * @return void
     *
     * @tbd sistemare
     */
    public function init(): void
    {
        foreach ($this->dependencies() as $key => $value)
            $this->_dependencies[Inflector::variablize(StringHelper::basename(is_string($key) ? $key : $value))] = [
                is_string($key) ? $key : $value, Inflector::underscore(self::name()).'_id', $value === true
            ];

        parent::init();
    }

    /**
     * {@inheritdoc}
     *
     * @tbd sistemare
     */
    public function __get($name)
    {
        if (!$this->hasDependency($name))
            return parent::__get($name);

        list($class, $primaryKey, $multiple) = $this->_dependencies[$name];

        return $this->_related[$name] ?? ($this->isNewRecord ? null : ($this->_related[$name] = ($this->{'has'.($multiple ? 'Many' : 'One')}($class, [$primaryKey => 'id']))->{$multiple ? 'all' : 'one'}()));
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     *
     * @tbd sistemare
     */
    public function __set($name, $value): void
    {
        if (!$this->hasDependency($name))
            parent::__set($name, $value);
        else {
            list($class, , $multiple) = $this->_dependencies[$name];

            if ($value instanceof $class || $value === null)
                $this->_related[$name] = $value;
            elseif ($multiple)
                $this->_related[$name] = array_map(function($data) use($class) {
                    return new $class(['scenario' => self::SCENARIO_CREATE, 'attributes' => $data]);
                }, $value);
            else {
                if (!isset($this->_related[$name]))
                    $this->_related[$name] = $this->isNewRecord ? new $class(['scenario' => self::SCENARIO_CREATE]) : $this->$name;

                $this->_related[$name]->load($value, '');
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     *
     * @tbd sistemare
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
            if ($this->hasAttribute($attribute .= $this->suffix))
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
        return [self::SCENARIO_CREATE => self::OP_INSERT];
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
            /** @var static $class */
            list($class, $primaryKey) = $this->_dependencies[$name];

            if ($dependencies === null)
                $class::deleteAll(array_combine((array) $primaryKey, $this->primaryKey));
            else {
                if (!is_array($dependencies))
                    $dependencies = [$dependencies];

                foreach ($dependencies as $dependency) {
                    $dependency->setAttribute($primaryKey, $this->primaryKey[0]);
                    $dependency->save();
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
     *
     * @tbd sistemare
     */
    private function hasDependency(string $name): bool
    {
        return isset($this->_dependencies[$name]);
    }
}