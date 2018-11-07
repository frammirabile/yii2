<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use yii\base\InvalidConfigException;
use yii\web\{NotFoundHttpException, ServerErrorHttpException};

/**
 * DeleteAction implements the API endpoint for deleting a model.
 *
 * For more details and usage information on DeleteAction, see the [guide article on rest controllers](guide:rest-controllers).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DeleteAction extends Action
{
    /**
     * Deletes a model
     *
     * @param mixed $id id of the model to be deleted
     * @return void
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException on failure
     */
    public function run($id): void
    {
        $model = $this->findModel($id);

        if ($this->checkAccess)
            call_user_func($this->checkAccess, $this->id, $model);

        if ($model->delete() === false)
            throw new ServerErrorHttpException('Model cannot be deleted');

        \Yii::$app->getResponse()->setStatusCode(204);
    }
}