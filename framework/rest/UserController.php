<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\filters\auth\{CompositeAuth, HttpBearerAuth};
use yii\helpers\Inflector;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Rest user controller
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class UserController extends ActiveController
{
    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->modelClass = \Yii::$app->user->identityClass;

        parent::init();
    }

    /**
     * @return IdentityInterface|null
     */
    public function actionViewMe(): ?IdentityInterface
    {
        return \Yii::$app->user->getIdentity();
    }

    /**
     * @param null|string $property
     * @param null|mixed $value
     * @return IdentityInterface
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionUpdateMe(): IdentityInterface
    {
        return $this->actionUpdateMy(null);
    }

    /**
     * @return void
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionDeleteMe(): void
    {
        /** @var DeleteAction $action */
        $action = $this->createAction('delete');
        $action->run(\Yii::$app->user->getIdentityId());
    }

    /**
     * @param string $property
     * @return mixed
     */
    public function actionViewMy(string $property)
    {
        return \Yii::$app->user->hasProperty($property) ? \Yii::$app->user->$property : $this->{__METHOD__.ucfirst($property)}();
    }

    /**
     * @param string|null $property
     * @param null|mixed $value
     * @return IdentityInterface
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionUpdateMy(?string $property, ? $value = null): IdentityInterface
    {
        /** @var UpdateAction $action */
        $action = $this->createAction('update');

        if ($property !== null)
            $action->data = [$property => $value ?: \Yii::$app->getRequest()->getRawBody()];

        /** @var IdentityInterface $identity */
        $identity = $action->run(\Yii::$app->user->getIdentityId());

        return $identity;
    }

    /**
     * @return void
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionViewMyActivation(): void
    {
        $this->actionUpdateMy('active', 1);
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