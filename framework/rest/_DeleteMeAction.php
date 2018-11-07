<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\base\InvalidConfigException;
use yii\db\ActiveRecordInterface;
use yii\web\ServerErrorHttpException;

/**
 * Rest action to delete the current user
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class DeleteMeAction extends Action
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function init(): void {}

    /**
     * Deletes the current user
     *
     * @return void
     * @throws InvalidConfigException
     */
    public function run(): void
    {
        \Yii::createObject([
            'class' => DeleteAction::class,
            'modelClass' => \Yii::$app->user->identityClass,
        ], [$this->id, $this->controller])->run(\Yii::$app->user->id);
    }
}