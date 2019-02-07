<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use yii\base\{DynamicModel, InvalidConfigException};
use yii\data\{ActiveDataFilter, ActiveDataProvider, DataFilter, Pagination, Sort};
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * IndexAction implements the API endpoint for listing multiple models.
 *
 * For more details and usage information on IndexAction, see the [guide article on rest controllers](guide:rest-controllers).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 2.0
 */
class IndexAction extends Action
{
    /**
     * @var callable a PHP callable that will be called to prepare a data provider that
     * should return a collection of the models. If not set, [[prepareDataProvider()]] will be used instead.
     * The signature of the callable should be:
     *
     * ```php
     * function (IndexAction $action) {
     *     // $action is the action object currently running
     * }
     * ```
     *
     * The callable should return an instance of [[ActiveDataProvider]].
     *
     * If [[dataFilter]] is set the result of [[DataFilter::build()]] will be passed to the callable as a second parameter.
     * In this case the signature of the callable should be the following:
     *
     * ```php
     * function (IndexAction $action, mixed $filter) {
     *     // $action is the action object currently running
     *     // $filter the built filter condition
     * }
     * ```
     */
    public $prepareDataProvider;

    /**
     * @var DataFilter|null data filter to be used for the search filter composition.
     * You must setup this field explicitly in order to enable filter processing.
     * For example:
     *
     * ```php
     * [
     *     'class' => 'yii\data\ActiveDataFilter',
     *     'searchModel' => function () {
     *         return (new \yii\base\DynamicModel(['id' => null, 'name' => null, 'price' => null]))
     *             ->addRule('id', 'integer')
     *             ->addRule('name', 'trim')
     *             ->addRule('name', 'string')
     *             ->addRule('price', 'number');
     *     },
     * ]
     * ```
     *
     * @see DataFilter
     *
     * @since 2.0.13
     */
    public $dataFilter;

    /**
     * @var bool whether to underscore filters
     */
    public $underscoreFilters = true;

    /**
     * @var ActiveQuery the query to return the collection of the models
     */
    public $query;

    /**
     * @var Pagination|bool the pagination object
     */
    public $pagination = false;

    /**
     * @var Sort|bool the sorting object
     */
    public $sort;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        if (!in_array(Filterable::class, class_implements($this->modelClass)))
            return;

        /** @var Filterable $modelClass */
        $modelClass = $this->modelClass;
        $filters = $modelClass::filters();

        $this->dataFilter = [
            'class' => ActiveDataFilter::class,
            'searchModel' => function() use($filters) {
                $searchModel = new DynamicModel(array_keys($filters));

                foreach ($filters as $attribute => $validators)
                    foreach ((array) $validators as $validator)
                        if ($validator == 'boolean')
                            $searchModel->addRule($attribute, 'filter', ['filter' => function($value) {
                                return strlen($value) == 0 || filter_var($value, FILTER_VALIDATE_BOOLEAN);
                            }]);
                        elseif (is_string($validator))
                            $searchModel->addRule($attribute, $validator);
                        elseif (is_array($validator))
                            $searchModel->addRule($attribute, reset($validator), array_slice($validator, 1));

                return $searchModel;
            },
            'attributeMap' => $this->underscoreFilters ? array_combine($filters = array_keys($filters), ArrayHelper::underscoreValues($filters)) : []
        ];
    }

    /**
     * @return ActiveDataProvider|DataFilter
     * @throws InvalidConfigException
     */
    public function run(): object
    {
        if ($this->checkAccess)
            call_user_func($this->checkAccess, $this->id);

        return $this->prepareDataProvider();
    }

    /**
     * Prepares the data provider that should return the requested collection of the models
     *
     * @return ActiveDataProvider|DataFilter
     * @throws InvalidConfigException
     */
    protected function prepareDataProvider(): object
    {
        if (empty($requestParams = \Yii::$app->getRequest()->getBodyParams()))
            $requestParams = \Yii::$app->getRequest()->getQueryParams();

        $filter = null;

        if ($this->dataFilter !== null) {
            $this->dataFilter = \Yii::createObject($this->dataFilter);

            if ($this->dataFilter->load($requestParams)) {
                $filter = $this->dataFilter->build();

                if ($filter === false)
                    return $this->dataFilter;
            }
        }

        if ($this->prepareDataProvider !== null)
            return call_user_func($this->prepareDataProvider, $this, $filter);

        if (($query = $this->query) === null) {
            /** @var $modelClass ActiveRecord */
            $modelClass = $this->modelClass;
            $query = $this->primaryModel === null
                ? $modelClass::find()
                : $this->primaryModel->getRelation(lcfirst($modelClass::name(true)));
        }

        if (!empty($filter))
            $query->andWhere($filter);

        return \Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $query,
            'pagination' => $this->pagination ?? ['params' => $requestParams],
            'sort' => $this->sort ?? ['params' => $requestParams]
        ]);
    }
}