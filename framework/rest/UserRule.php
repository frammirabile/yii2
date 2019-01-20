<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

/**
 * Rest user rule
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class UserRule extends UrlRule
{
    /**
     * {@inheritdoc}
     */
    public $prefix = '<module:(?:v\d+)?>';

    /**
     * {@inheritdoc}
     */
    public $only = ['create', 'view-me', 'update-me', 'delete-me', 'view-my', 'update-my'];

    /**
     * {@inheritdoc}
     */
    public $tokens = [
        '{username}' => '<username:[a-z0-9\.\+-_]+(?:@[a-z0-9\.\+-_]+\.[a-z]{2,}>)?',
        '{property}' => '<property:\w+>'
    ];

    /**
     * {@inheritdoc}
     */
    public $patterns = [
        'POST' => 'create',
        'GET,HEAD' => 'view-me',
        'PUT,PATCH' => 'update-me',
        'DELETE' => 'delete-me',
        'GET,HEAD {property}' => 'view-my',
        'PUT,PATCH {property}' => 'update-my',
        '{property}' => 'options',
        '' => 'options'
    ];
}