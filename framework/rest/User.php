<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\base\InvalidConfigException;
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
        return isset($name) ? parent::__get($name) : $this->_identity->$name;
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($name): bool
    {
        return parent::__isset($name) || isset($this->_identity->$name);
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
     */
    public function canGetProperty($name, $checkVars = true, $checkBehaviors = true): bool
    {
        /** @var ActiveRecord $identity */
        $identity = $this->_identity;

        return $this->hasProperty($name) ? parent::canGetProperty($name) : $identity->canGetProperty($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity($autoRenew = true): ?IdentityInterface
    {
        return $this->_identity;
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
     * @return bool
     */
    public function authenticate(string $username, string $password): bool
    {
        /** @var UserInterface $modelClass */
        $modelClass = $this->modelClass;

        if (($user = $modelClass::findByUsername($username)) === null || !$user->validatePassword($password))
            return false;

        /** @var \yii\web\IdentityInterface $identity */
        return ($identity = $user->getIdentity()) !== null && $this->login($identity);
    }

    /**
     * @param string $token
     * @return bool
     */
    public function authenticateByRefreshToken(string $token): bool
    {
        /** @var TokenInterface $tokenClass */
        $tokenClass = $this->tokenClass;

        /** @var UserInterface $modelClass */
        $modelClass = $this->modelClass;

        /** @var \yii\web\IdentityInterface $identity */
        return ($token = $tokenClass::findByRefresh($token)) !== null &&
               ($user = $modelClass::findById($token->getUserId())) !== null &&
               ($identity = $user->getIdentity()) !== null &&
               $this->login($identity);
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
               ($user = $modelClass::findById($token->getUserId())) !== null &&
               ($identity = $user->getIdentity()) !== null &&
               $this->login($identity) ? $identity : null;
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
}