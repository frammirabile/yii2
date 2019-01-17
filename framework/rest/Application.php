<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\helpers\{ArrayHelper, UnsetArrayValue};

/**
 * {@inheritdoc}
 * @property-read Request $request
 * @property-read Response $response
 * @property-read null $session
 * @property-read User $user
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