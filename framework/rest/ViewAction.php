<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use yii\db\ActiveRecordInterface;
use yii\web\NotFoundHttpException;

/**
 * ViewAction implements the API endpoint for returning the detailed information about a model.
 *
 * For more details and usage information on ViewAction, see the [guide article on rest controllers](guide:rest-controllers).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ViewAction extends Action
{
    /**
     * Displays a model
     *
     * @param string $id the primary key of the model
     * @return ActiveRecordInterface the model being displayed
     * @throws NotFoundHttpException
     */
    public function run(string $id): ActiveRecordInterface
    {
        $model = $this->findModel($id);

        if ($this->checkAccess)
            call_user_func($this->checkAccess, $this->id, $model);

        return $model;
    }
}