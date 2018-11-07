<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use yii\base\{ArrayAccessTrait, InvalidConfigException};
use yii\helpers\Inflector;

/**
 * ArrayFixture represents arbitrary fixture that can be loaded from PHP files.
 *
 * For more details and usage information on ArrayFixture, see the [guide article on fixtures](guide:test-fixtures).
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class ArrayFixture extends Fixture implements \IteratorAggregate, \ArrayAccess, \Countable
{
    use ArrayAccessTrait;
    use FileFixtureTrait;

    /**
     * @var array the data rows. Each array element represents one row of data (column name => column value).
     */
    public $data = [];

    /**
     * @var string the file path or [path alias](guide:concept-aliases) of the data file that contains the fixture data.
     * If this is not set, it will default to `FixturePath/data/TableName.php`,
     * where `FixturePath` stands for the directory containing this fixture class, and `TableName` stands for the
     * name of the table associated with this fixture. You can set this property to be false to prevent loading any data.
     */
    public $dataFile;

    /**
     * Loads the fixture.
     *
     * The default implementation simply stores the data returned by [[getData()]] in [[data]].
     * You should usually override this method by putting the data into the underlying database.
     *
     * @return void
     * @throws \ReflectionException
     * @throws InvalidConfigException
     */
    public function load(): void
    {
        $this->data = $this->getData();
    }

    /**
     * Returns the fixture data
     *
     * @return array the fixture data
     * @throws \ReflectionException
     * @throws InvalidConfigException if the specified data file does not exist
     */
    protected function getData(): array
    {
        $class = new \ReflectionClass($this);

        return $this->loadData($this->dataFile ?? dirname($class->getFileName()).'/data/'.Inflector::underscore(substr($class->getShortName(), 0, -7)).'.php');
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function unload(): void
    {
        parent::unload();
        $this->data = [];
    }
}