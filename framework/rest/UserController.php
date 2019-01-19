<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\base\{ActionEvent, InvalidConfigException};
use yii\db\ActiveRecordInterface;
use yii\filters\auth\{CompositeAuth, HttpBasicAuth, HttpBearerAuth, QueryParamAuth};
use yii\helpers\Inflector;
use yii\web\NotFoundHttpException;

/**
 * Rest user controller
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class UserController extends ActiveController
{
    /**
     * @var string the scenario used for updating the current user
     */
    protected $updateMeScenario = ActiveUser::SCENARIO_UPDATE_ME;

    /**
     * @return IdentityInterface|null
     */
    public function actionViewMe(): ?IdentityInterface
    {
        return \Yii::$app->user->getIdentity();
    }

    /**
     * @return ActiveRecordInterface
     * @throws InvalidConfigException
     */
    public function actionUpdateMe(): ActiveRecordInterface
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
     * @return ActiveRecordInterface
     * @throws InvalidConfigException
     */
    public function actionUpdateMy(string $property): ActiveRecordInterface
    {
        return ($action = \Yii::createObject([
            'class' => UpdateAction::class,
            'modelClass' => \Yii::$app->user->identityClass,
            'scenario' => $this->updateMeScenario,
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
     * @param string $username
     * @return void
     * @throws NotFoundHttpException
     */
    public function actionCreateRefreshPassword(string $username): void
    {
        /** @var IdentityInterface $userClass */
        $userClass = $this->modelClass;

        if (($user = $userClass::findByUsername($username)) === null)
            throw new NotFoundHttpException;

        #tbd invio mail con frammirabile/yii2-notification
    }

    /**
     * @param string $username
     * @return IdentityInterface|null
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionRefreshPassword(string $username): ?IdentityInterface
    {
        /** @var IdentityInterface $userClass */
        $userClass = $this->modelClass;

        /** @var ActiveRecord|IdentityInterface $user */
        if (($user = $userClass::findByUsername($username)) === null)
            throw new NotFoundHttpException;

        $refreshPassword = \Yii::$app->getRequest()->get('refresh_password');

        if ($refreshPassword === null || $refreshPassword != $user->getResetPassword())
            $user->addError('refresh_password', 'Invalid refresh_password');
        else
            $user = \Yii::createObject([
                'class' => UpdateAction::class,
                'modelClass' => $this->modelClass,
                'scenario' => ActiveUser::SCENARIO_RESET_PASSWORD,
            ], [ActiveUser::SCENARIO_UPDATE, $this])->run($user->getId());

        #tbd test codici 401 e 403

        return $user->hasErrors() ? $user : null; #tbd automatizzare codice di ritorno in base a risposta: null -> 204, hasErrors() -> 422, altrimenti 200
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

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     *
     * @tdb
     */
    public function actionInitPasswordReset(string $username): void
    {
        /** @var IdentityInterface $userClass */
        $userClass = $this->modelClass;

        if (($user = $userClass::findByUsername($username)) === null)
            throw new NotFoundHttpException;

        MailHelper::sendHtmlMail('resetPassword', \Yii::t('api', 'Reset your password'), 'support-noreplay', $user->getEmail(), ['user' => $user]);
    }

    /**
     * @param ActionEvent $event
     * @return void
     */
    protected function afterActivation(ActionEvent $event): void {}

    #$this->redirect('https://www.'.Url::domain().'/verification?l='.\Yii::$app->getRequest()->get('lang', \Yii::$app->language).'&r='.intval($event->result->hasErrors()));

}