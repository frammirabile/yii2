<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\helpers\{ArrayHelper, StringHelper, UnsetArrayValue};

/**
 * ActiveController implements a common set of actions for supporting RESTful access to ActiveRecord.
 *
 * The class of the ActiveRecord should be specified via [[modelClass]], which must implement [[\yii\db\ActiveRecordInterface]].
 * By default, the following actions are supported:
 *
 * - `index`: list of models
 * - `view`: return the details of a model
 * - `create`: create a new model
 * - `update`: update an existing model
 * - `delete`: delete an existing model
 * - `options`: return the allowed HTTP methods
 *
 * You may disable some of these actions by overriding [[actions()]] and unsetting the corresponding actions.
 *
 * To add a new action, either override [[actions()]] by appending a new action class or write a new action method.
 * Make sure you also override [[verbs()]] to properly declare what HTTP methods are allowed by the new action.
 *
 * You should usually override [[checkAccess()]] to check whether the current user has the privilege to perform
 * the specified action against the specified model.
 *
 * For more details and usage information on ActiveController, see the [guide article on rest controllers](guide:rest-controllers).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 2.0
 */
abstract class ActiveController extends Controller
{
    /**
     * @var string the model class name
     */
    public $modelClass;

    /**
     * @var string the scenario used for creating a model
     */
    public $createScenario = ActiveRecord::SCENARIO_CREATE;

    /**
     * @var string the scenario used for updating a model
     */
    public $updateScenario = ActiveRecord::SCENARIO_UPDATE;

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function init(): void
    {
        if ($this->modelClass === null)
            $this->modelClass = \Yii::$app->modelNamespace.'\\'.StringHelper::basename(get_class($this), 'Controller');
    }

    /**
     * {@inheritdoc}
     *
     * @tbd:
     *  - gestire formato e lingua per controller/action
     *  - ottimizzare autorizzazione con livello per action: 1 pubblica, 2 privata, 3 autorizzazione
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'contentNegotiator' => [
                'formats' => ['text/plain' => Response::FORMAT_RAW]
            ],
            'authenticator' => new UnsetArrayValue,
            [
                'class' => Cors::class,
                'cors' => ['Access-Control-Request-Headers' => ['Accept', 'Accept-Language', 'Authorization', 'Content-Type']]
            ],
            $this->authMethods()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'create' => [
                'class' => CreateAction::class,
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->createScenario
            ],
            'view' => [
                'class' => ViewAction::class,
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess']
            ],
            'index' => [
                'class' => IndexAction::class,
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess']
            ],
            'update' => [
                'class' => UpdateAction::class,
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->updateScenario
            ],
            'delete' => [
                'class' => DeleteAction::class,
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess']
            ],
            'options' => OptionsAction::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function verbs(): array
    {
        return [
            'create' => ['POST'],
            'view' => ['GET', 'HEAD'],
            'index' => ['GET', 'HEAD'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE']
        ];
    }

    /**
     * @return array
     */
    protected function authMethods(): array
    {
        return ['class' => HttpBearerAuth::class];
    }

    /**
     * Checks the privilege of the current user.
     *
     * This method should be overridden to check whether the current user has the privilege
     * to run the specified action against the specified data model.
     * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
     *
     * @param string $action the ID of the action to be executed
     * @param null|object $model the model to be accessed. If null, it means no specific model is being accessed.
     * @param array $params additional parameters
     * @return void
     */
    public function checkAccess(string $action, ?object $model = null, array $params = []): void {}
}