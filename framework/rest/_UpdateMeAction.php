<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\base\{InvalidConfigException, Model};

/**
 * Rest action to update the current user
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class UpdateMeAction extends Action
{
    /**
     * @var string the scenario to be assigned to the user before it is validated and updated
     */
    public $scenario = Model::SCENARIO_DEFAULT;

    /**
     * @var array data to be loaded
     */
    public $data = [];

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function init(): void {}

    /**
     * Updates the current user
     *
     * @return IdentityInterface the user being updated
     * @throws InvalidConfigException
     */
    public function run(): object
    {
        return ($action = \Yii::createObject([
            'class' => UpdateAction::class,
            'modelClass' => \Yii::$app->user->identityClass,
            'scenario' => $this->scenario,
            'data' => $this->data
        ], [$this->id, $this->controller]))->run(\Yii::$app->user->id);
    }
}