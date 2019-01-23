<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\base\{InvalidConfigException, InvalidValueException};
use yii\helpers\StringHelper;

/**
 * Rest user
 *
 * @property-read int $id
 * @property-read string $username
 * @property-read int $identity_id
 * @property-read IdentityInterface|null $identity
 * @property-read TokenInterface|null $token
 *
 * @method beforeLogin(IdentityInterface $identity, $cookieBased, $duration)
 * @method afterLogin(IdentityInterface $identity, $cookieBased, $duration)
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
     * @var UserInterface
     */
    protected $_this;

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

        if (!in_array(IdentityInterface::class, class_implements($this->identityClass)))
            throw new InvalidConfigException(\Yii::t('yii', 'Identity class must implement IdentityInterface'));

        if (!in_array(TokenInterface::class, class_implements($this->tokenClass)))
            throw new InvalidConfigException(\Yii::t('yii', 'Token class must implement TokenInterface'));
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
        return StringHelper::basename($this->identityClass).' '.$this->getIdentityId();
    }

    /**
     * {@inheritdoc}
     */
    public function hasProperty($name, $checkVars = true, $checkBehaviors = true): bool
    {
        /** @var ActiveRecord $identity */
        $identity = $this->_identity;

        return parent::hasProperty($name) || $identity->hasProperty($name);
    }

    /**
     * {@inheritdoc}
     * @return UserInterface|null
     */
    public function getIdentity($autoRenew = false): ?UserInterface
    {
        return $this->_this;
    }

    /**
     * @param IdentityInterface $identity
     */
    public function setIdentity($identity): void
    {
        if (!($identity instanceof IdentityInterface))
            throw new InvalidValueException('The identity object must implement IdentityInterface');

        $this->_identity = $identity;
    }

    /**
     * @return TokenInterface|null
     */
    public function getToken(): ?TokenInterface
    {
        return $this->_this->getToken();
    }

    /**
     * @param string $username
     * @param string $password
     * @return IdentityInterface|null
     */
    public function loginByCredentials(string $username, string $password): ?IdentityInterface
    {
        /** @var UserInterface $modelClass */
        $modelClass = $this->modelClass;

        if (($this->_this = $modelClass::findByUsername($username)) === null || !$this->_this->validatePassword($password))
            return null;

        return ($identity = $this->_this->getIdentity()) !== null && $this->_login($identity) ? $identity : null;
    }

    /**
     * @param string $token
     * @return IdentityInterface|null
     */
    public function loginByRefreshToken(string $token): ?IdentityInterface
    {
        /** @var TokenInterface $tokenClass */
        $tokenClass = $this->tokenClass;

        /** @var UserInterface $modelClass */
        $modelClass = $this->modelClass;

        return ($token = $tokenClass::findByRefresh($token)) !== null &&
               ($this->_this = $modelClass::findById($token->getUserId())) !== null &&
               ($identity = $this->_this->getIdentity()) !== null &&
               $this->_login($identity) ? $identity : null;
    }

    /**
     * {@inheritdoc}
     */
    public function loginByAccessToken($token, $type = null): ?IdentityInterface
    {
        /** @var TokenInterface $tokenClass */
        $tokenClass = $this->tokenClass;

        /** @var UserInterface $modelClass */
        $modelClass = $this->modelClass;

        return ($token = $tokenClass::findByString($token)) !== null &&
               ($this->_this = $modelClass::findById($token->getUserId())) !== null &&
               ($identity = $this->_this->getIdentity()) !== null &&
               $this->_login($identity) ? $identity : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->_this->getId();
    }

    /**
     * Returns the username
     *
     * @return string|null the username
     */
    public function getUsername(): ?string
    {
        return $this->_this->getUsername();
    }

    /**
     * Returns the identity id
     *
     * @return int the identity id
     */
    public function getIdentityId(): int
    {
        return $this->_identity->getId() ?? null;
    }

    /**
     * @param IdentityInterface $identity
     * @return bool
     */
    private function _login(IdentityInterface $identity): bool
    {
        if ($this->beforeLogin($identity, false, 0)) {
            $this->setIdentity($identity);
            \Yii::info($this->__toString().' logged in from '.\Yii::$app->request->getUserIP(), __METHOD__);
            $this->afterLogin($identity, false, 0);
        }

        return !$this->getIsGuest();
    }
}