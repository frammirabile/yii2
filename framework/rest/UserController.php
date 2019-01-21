<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\base\InvalidConfigException;
use yii\filters\auth\{CompositeAuth, HttpBearerAuth};
use yii\helpers\Inflector;

/**
 * Rest user controller
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class UserController extends ActiveController
{
    /**
     * @return IdentityInterface|null
     */
    public function actionViewMe(): ?IdentityInterface
    {
        return \Yii::$app->user->getIdentity();
    }

    /**
     * @return IdentityInterface
     * @throws InvalidConfigException
     */
    public function actionUpdateMe(): IdentityInterface
    {
        return ($action = \Yii::createObject([
            'class' => UpdateAction::class,
            'modelClass' => \Yii::$app->user->identityClass,
            'scenario' => $this->updateMeScenario,
            'data' => $this->data
        ], [$this->id, $this]))->run(\Yii::$app->user->getId());
    }

    /**
     * @return void
     * @throws InvalidConfigException
     */
    public function actionDeleteMe(): void
    {
        \Yii::createObject([
            'class' => DeleteAction::class,
            'modelClass' => \Yii::$app->user->identityClass,
        ], [$this->id, $this->controller])->run(\Yii::$app->user->getId());
    }

    /**
     * @param string $property
     * @return mixed
     */
    public function actionViewMy(string $property)
    {
        return method_exists($this, $getter = 'get'.Inflector::camelize($property))
            ? $this->$getter()
            : \Yii::$app->user->getIdentity()->$property;
    }

    /**
     * @param string $property
     * @return IdentityInterface
     * @throws InvalidConfigException
     */
    public function actionUpdateMy(string $property): IdentityInterface
    {
        return ($action = \Yii::createObject([
            'class' => UpdateAction::class,
            'modelClass' => \Yii::$app->user->identityClass,
            'scenario' => $this->updateScenario,
            'data' => [$property => \Yii::$app->getRequest()->getRawBody()]
        ], [$this->id, $this]))->run(\Yii::$app->user->getId());
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function getActivation(): string
    {
        \Yii::createObject([
            'class' => UpdateAction::class,
            'checkAccess' => [$this, 'checkAccess'],
            'modelClass' => \Yii::$app->user->identityClass,
            'scenario' => ActiveUser::SCENARIO_ACTIVATE_ME,
            'data' => ['active' => 1],
            'on afterRun' => [$this, 'afterActivation']
        ], [$this->id, $this])->run(\Yii::$app->user->getId());
    }

    /**
     * {@inheritdoc}
     */
    protected function verbs(): array
    {
        return [
            'view-me' => ['GET', 'HEAD'],
            'update-me' => ['PUT', 'PATCH'],
            'delete-me' => ['DELETE'],
            'view-my' => ['GET', 'HEAD'],
            'update-my' => ['PUT', 'PATCH'],
            'create-refreshPassword' => ['POST'],
            'refresh-password' => ['PUT']
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function authMethods(): array
    {
        return [
            'class' => CompositeAuth::class,
            'authMethods' => [
                'bearer' => [
                    'class' => HttpBearerAuth::class,
                    'only' => ['*-me']
                ]/* tbd,
                'header' => [
                    'class' => HttpBasicAuth::class,
                    'only' => ['create', 'init-password-refresh', 'update-password']
                ],
                'query' => [
                    'class' => QueryParamAuth::class,
                    'only' => ['activate']
                ]*/
            ]
        ];
    }
}