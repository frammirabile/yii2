<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

/**
 * Rest action to display the current user
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class ViewMeAction extends Action
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function init(): void {}

    /**
     * Displays the current user
     *
     * @return IdentityInterface the user being displayed
     */
    public function run(): IdentityInterface
    {
        return \Yii::$app->user->identity;
    }
}