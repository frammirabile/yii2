<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * Rest user
 *
 * @property-read int $id
 * @property-read string $username
 * @property-read bool $isAnonymous
 * @property-read IdentityInterface|null $identity
 * @property-read TokenInterface|null $token
 *
 * @method beforeLogin(UserInterface $user, $cookieBased, $duration)
 * @method afterLogin(UserInterface $user, $cookieBased, $duration)
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class User extends \yii\web\User
{
    /**
     * @var string the model class name
     */
    public $modelClass;

    /**
     * @var string the class name of the [[token]] object
     */
    public $tokenClass;

    /**
     * @var string|null the class name of the [[identity]] object
     */
    public $identityClass;

    /**
     * @var bool whether to login without identity
     */
    public $loginWithoutIdentity = false;

    /**
     * @var UserInterface
     */
    protected $_model;

    /**
     * @var IdentityInterface|null
     */
    protected $_identity;

    /**
     * {@inheritdoc}
     *
     * @return void
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->enableSession = false;
        $this->loginUrl = null;

        if (!in_array(UserInterface::class, class_implements($this->modelClass)))
            throw new InvalidConfigException(\Yii::t('yii', 'User class must implement UserInterface'));

        if (!in_array(TokenInterface::class, class_implements($this->tokenClass)))
            throw new InvalidConfigException(\Yii::t('yii', 'Token class must implement TokenInterface'));

        if ($this->identityClass !== null && !in_array(IdentityInterface::class, class_implements($this->identityClass)))
            throw new InvalidConfigException(\Yii::t('yii', 'Identity class must implement IdentityInterface'));
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        return isset($this->$name) ? parent::__get($name) : $this->_identity->$name;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'User '.$this->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function hasProperty($name, $checkVars = true, $checkBehaviors = true): bool
    {
        /** @var ActiveRecord $identity */
        $identity = $this->_identity;

        return parent::hasProperty($name) || !$this->getIsAnonymous() && $identity->hasProperty($name);
    }

    /**
     * {@inheritdoc}
     * @return UserInterface
     */
    public function getModel(): UserInterface
    {
        return $this->_model;
    }

    /**
     * {@inheritdoc}
     * @return IdentityInterface|null
     */
    public function getIdentity($autoRenew = false): ?IdentityInterface
    {
        return $this->_identity;
    }

    /**
     * @return TokenInterface|null
     */
    public function getToken(): ?TokenInterface
    {
        return $this->_model->getToken();
    }

    /**
     * @param string $username
     * @param string $password
     * @return UserInterface|null
     */
    public function loginByCredentials(string $username, string $password): ?UserInterface
    {
        /** @var UserInterface $modelClass */
        $modelClass = $this->modelClass;

        return ($model = $modelClass::findByUsername($username)) !== null &&
               $model->validatePassword($password) && $this->_login($model) ? $model : null;
    }

    /**
     * @param string $token
     * @return UserInterface|null
     */
    public function loginByRefreshToken(string $token): ?UserInterface
    {
        /** @var TokenInterface $tokenClass */
        $tokenClass = $this->tokenClass;

        /** @var UserInterface $modelClass */
        $modelClass = $this->modelClass;

        return ($token = $tokenClass::findByRefresh($token)) !== null &&
               ($model = $modelClass::findById($token->getUserId())) !== null &&
               $this->_login($model) ? $model : null;
    }

    /**
     * {@inheritdoc}
     */
    public function loginByAccessToken($token, $type = null): ?UserInterface
    {
        /** @var TokenInterface $tokenClass */
        $tokenClass = $this->tokenClass;

        /** @var UserInterface $modelClass */
        $modelClass = $this->modelClass;

        return ($token = $tokenClass::findByString($token)) !== null &&
               ($model = $modelClass::findById($token->getUserId())) !== null &&
               $this->_login($model) ? $model : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->_model->getId();
    }

    /**
     * Returns the username
     *
     * @return string|null the username
     */
    public function getUsername(): ?string
    {
        return $this->_model->getUsername();
    }

    /**
     * @return bool
     */
    public function getIsAnonymous(): bool
    {
        return !$this->getIsGuest() && $this->_identity === null;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsGuest(): bool
    {
        return $this->_model === null;
    }

    /**
     * @param UserInterface $model
     * @return bool
     */
    private function _login(UserInterface $model): bool
    {
        if ($this->beforeLogin($model, false, 0)) {
            $this->_model = $model;

            if (($this->_identity = $model->getIdentity()) === null && !$this->loginWithoutIdentity)
                return false;

            \Yii::info('User '.$this->getId().' logged in from '.\Yii::$app->request->getUserIP(), __METHOD__);
            $this->afterLogin($model, false, 0);
        }

        return !$this->getIsGuest();
    }
}