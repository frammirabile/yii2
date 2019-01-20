<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\helpers\{ArrayHelper, UnsetArrayValue};

/**
 * Rest application
 *
 * @property-read Request $request
 * @property-read Response $response
 * @property-read User $user
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class Application extends \yii\web\Application
{
    /**
     * {@inheritdoc}
     */
    public function coreComponents(): array
    {
        return ArrayHelper::merge(parent::coreComponents(), [
            'request' => ['class' => 'yii\rest\Request'],
            'response' => ['class' => 'yii\rest\Response'],
            'session' => new UnsetArrayValue,
            'user' => ['class' => 'yii\rest\User']
        ]);
    }
}