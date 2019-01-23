<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\helpers\ArrayHelper;

/**
 * Rest request
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class Request extends \yii\web\Request
{
    /**
     * {@inheritdoc}
     */
    public $enableCsrfValidation = false;

    /**
     * {@inheritdoc}
     */
    public $enableCsrfCookie = false;

    /**
     * {@inheritdoc}
     */
    public $enableCookieValidation = false;

    /**
     * {@inheritdoc}
     */
    public $parsers = [
        'application/json' => 'yii\web\JsonParser',
        '*/*' => 'yii\web\JsonParser'
    ];

    /**
     * @var Client
     */
    public $client;

    /**
     * @var bool whether to underscore the parameters keys
     */
    public $underscoreKeys = true;

    /**
     * {@inheritdoc}
     */
    public function getBodyParams(): array
    {
        $bodyParams = parent::getBodyParams();

        return $this->underscoreKeys ? ArrayHelper::underscoreKeys($bodyParams) : $bodyParams;
    }
}