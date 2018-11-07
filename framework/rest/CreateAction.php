<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use yii\base\{InvalidConfigException, Model};
use yii\db\{ActiveRecordInterface, Exception};
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

/**
 * CreateAction implements the API endpoint for creating a new model from the given data.
 *
 * For more details and usage information on CreateAction, see the [guide article on rest controllers](guide:rest-controllers).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CreateAction extends Action
{
    /**
     * @var string the scenario to be assigned to the new model before it is validated and saved
     */
    public $scenario = Model::SCENARIO_DEFAULT;

    /**
     * @var string the name of the view action. This property is needed to create the URL when the model is successfully created
     */
    public $viewAction = 'view';

    /**
     * @var array data to be loaded
     */
    public $data = [];

    /**
     * Creates a new model
     *
     * @return ActiveRecordInterface the model newly created
     * @throws Exception
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException if there is any error when creating the model
     */
    public function run(): ActiveRecordInterface
    {
        if ($this->checkAccess)
            call_user_func($this->checkAccess, $this->id);

        /** @var $model ActiveRecord */
        $model = new $this->modelClass(['scenario' => $this->scenario]);
        $model->load($this->data ?: \Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->save()) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));
        } elseif (!$model->hasErrors())
            throw new ServerErrorHttpException('Model cannot be created');

        return $model;
    }
}