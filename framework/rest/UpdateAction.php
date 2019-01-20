<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use yii\base\{InvalidConfigException, Model};
use yii\db\Exception;
use yii\helpers\Inflector;
use yii\web\{NotFoundHttpException, ServerErrorHttpException};

/**
 * UpdateAction implements the API endpoint for updating a model.
 *
 * For more details and usage information on UpdateAction, see the [guide article on rest controllers](guide:rest-controllers).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class UpdateAction extends Action
{
    /**
     * @var string the scenario to be assigned to the model before it is validated and updated
     */
    public $scenario = Model::SCENARIO_DEFAULT;

    /**
     * @var array data to be loaded
     */
    public $data = [];

    /**
     * Updates a model
     *
     * @param string $id the model primary key
     * @return ActiveRecord the model being updated
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function run(string $id): ActiveRecord
    {
        try {
            $model = $this->findModel($id);
        } catch (NotFoundHttpException $e) {
            if ($this->primaryModel === null)
                throw $e;

            /** @var CreateAction $action */
            $action = $this->controller->createAction('create');

            /** @var ActiveRecord $modelClass */
            $action->modelClass = $modelClass = $action->primaryModel->getRelation(Inflector::pluralize($action->controller->id))->modelClass;
            $action->data = array_combine($modelClass::primaryKey(), [$action->primaryModel->getPrimaryKey(), $id]) + \Yii::$app->getRequest()->getBodyParams();

            /** @var ActiveRecord $model */
            $model = $action->run();

            return $model;
        }

        if ($this->checkAccess)
            call_user_func($this->checkAccess, $this->id, $model);

        $model->scenario = $this->scenario;
        $model->load($this->data ?: \Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->save() === false && !$model->hasErrors())
            throw new ServerErrorHttpException('Model cannot be updated');

        return $model;
    }
}