<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\filters\auth;

use yii\rest\Client;

/**
 * OAuth2 authentication
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class HttpOAuth2 extends HttpBasicAuth
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function init(): void
    {
        $this->auth = function(string $id, string $secret): ?bool {
            return (\Yii::$app->request->client = Client::findOne(['id' => $id, 'secret' => $secret])) === null ? null : false;
        };
    }
}