<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use yii\console\Exception;
use yii\web\BadRequestHttpException;

/**
 * Action is the base class for all controller action classes.
 *
 * Action provides a way to reuse action method code. An action method in an Action
 * class can be used in multiple controllers or in different projects.
 *
 * Derived classes must implement a method named `run()`. This method
 * will be invoked by the controller when the action is requested.
 * The `run()` method can have parameters which will be filled up
 * with user input values automatically according to their names.
 * For example, if the `run()` method is declared as follows:
 *
 * ```php
 * public function run($id, $type = 'book') { ... }
 * ```
 *
 * And the parameters provided for the action are: `['id' => 1]`.
 * Then the `run()` method will be invoked as `run(1)` automatically.
 *
 * For more details and usage information on Action, see the [guide article on actions](guide:structure-controllers).
 *
 * @property string $uniqueId The unique ID of this action among the whole application. This property is
 * read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class Action extends Component
{
    /**
     * @event ActionEvent an event raised right before running an action
     */
    const EVENT_BEFORE_RUN = 'beforeRun';

    /**
     * @event ActionEvent an event raised right after running an action
     */
    const EVENT_AFTER_RUN = 'afterRun';

    /**
     * @var string ID of the action
     */
    public $id;

    /**
     * @var Controller|\yii\web\Controller|\yii\console\Controller the controller that owns this action
     */
    public $controller;

    /**
     * Constructor
     *
     * @param string $id the ID of this action
     * @param Controller $controller the controller that owns this action
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct(string $id, Controller $controller, array $config = [])
    {
        $this->id = $id;
        $this->controller = $controller;

        parent::__construct($config);
    }

    /**
     * Returns the unique ID of this action among the whole application
     *
     * @return string the unique ID of this action among the whole application
     */
    public function getUniqueId(): string
    {
        return $this->controller->getUniqueId() . '/' . $this->id;
    }

    /**
     * Runs this action with the specified parameters.
     * This method is mainly invoked by the controller.
     *
     * @param array $params the parameters to be bound to the action's run() method
     * @return mixed the result of the action
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws InvalidConfigException if the action class does not have a run() method
     */
    public function runWithParams(array $params)
    {
        if (!method_exists($this, 'run'))
            throw new InvalidConfigException(get_class($this) . ' must define a "run()" method.');

        $args = $this->controller->bindActionParams($this, $params);
        \Yii::debug('Running action: ' . get_class($this) . '::run()', __METHOD__);

        if (\Yii::$app->requestedParams === null)
            \Yii::$app->requestedParams = $args;

        if ($this->beforeRun()) {
            $result = call_user_func_array([$this, 'run'], $args);
            $this->afterRun($result);

            return $result;
        }

        return null;
    }

    /**
     * This method is called right before `run()` is executed.
     * You may override this method to do preparation work for the action run.
     * If the method returns false, it will cancel the action.
     *
     * @return bool whether to run the action
     */
    protected function beforeRun(): bool
    {
        $event = new ActionEvent($this);
        $this->trigger(self::EVENT_BEFORE_RUN, $event);

        return $event->isValid;
    }

    /**
     * This method is called right after `run()` is executed.
     * You may override this method to do post-processing work for the action run.
     *
     * @param mixed $result
     * @return void
     */
    protected function afterRun($result): void
    {
        $event = new ActionEvent($this);
        $event->result = $result;
        $this->trigger(self::EVENT_AFTER_RUN, $event);
    }
}