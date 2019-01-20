<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\behaviors;

use api\models\Translation;
use yii\base\Behavior;
use yii\db\{ActiveRecord, Exception};
use yii\helpers\{ArrayHelper, Json};
use yii\validators\TranslationValidator;
use yii\web\ServerErrorHttpException;

/**
 * Translation behavior
 *
 * @property ActiveRecord $owner
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 *
 * @tbd sistemare e rivedere logica, magari utilizzando I18N
 */
class TranslationBehavior extends Behavior
{
    /**
     * @var bool
     */
    public $translate = true;

    /**
     * @var string[]
     */
    public $attributes = [];

    /**
     * @var bool
     */
    public $compact = false;

    /**
     * @var Translation[]
     */
    private $_translations = [];

    /**
     * {@inheritdoc}
     */
    public function events(): array
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave'
        ];
    }

    /**
     * @return void
     */
    public function beforeValidate(): void
    {
        foreach ($this->attributes as $attribute)
            if ($this->translate && !is_string($this->owner->$attribute))
                $this->owner->addError($attribute, ucfirst($attribute).' must be a string');
            elseif (!$this->translate && ($this->owner->isAttributeRequired($attribute) || $this->owner->$attribute !== null))
                (new TranslationValidator)->validateAttribute($this->owner, $attribute);
    }

    /**
     * @return void
     */
    public function afterFind(): void
    {
        if (!$this->translate) {
            $attributes = [];

            foreach ($this->attributes as $attribute)
                if ($this->owner->$attribute !== null) {
                    $this->owner->$attribute = [\Yii::$app->params['languages'][0] => $this->owner->$attribute];
                    $attributes[] = $attribute;
                }

            foreach ($this->compact ? (array) $this->owner->getAttribute('translation') : array_map(function($text) { return Json::decode($text, false); }, Translation::findByObject($this->owner)->column()) as $language => $translation)
                foreach ($attributes as $attribute)
                    if (!empty($translation->$attribute))
                        $this->owner->$attribute += [$language => $translation->$attribute];
        } elseif (\Yii::$app->language != \Yii::$app->sourceLanguage)
            $this->owner->setAttributes(array_intersect_key($this->compact
                ? $this->owner->getAttribute('translation')->{\Yii::$app->language} ?? []
                : (array) json_decode(Translation::findByObject($this->owner)->andWhere(['language' => \Yii::$app->language])->scalar()), array_flip($this->attributes)
            ), false);
    }

    /**
     * @return void
     */
    public function beforeSave(): void
    {
        foreach ($this->attributes as $attribute)
            if (is_array($this->owner->$attribute)) {
                $_attribute = $this->owner->$attribute;
                $this->owner->$attribute = ArrayHelper::remove($_attribute, \Yii::$app->params['languages'][0]);

                if ($this->compact)
                    $this->owner->setAttribute('translation', array_map(function($name) use($attribute) { return [$attribute => $name]; }, $_attribute));
                else
                    foreach ($_attribute as $language => $text)
                        $this->_translations[$language][$attribute] = $text;
            }

        $this->_translations = array_map(function($text, $language) {
            return new Translation(['attributes' => ['language' => $language, 'text' => $text]]);
        }, array_values($this->_translations), array_keys($this->_translations));
    }

    /**
     * @return void
     * @throws Exception
     * @throws ServerErrorHttpException
     *
     * @tbd use db transactions and fix it
     */
    public function afterSave(): void
    {
        if (!$this->compact) {
            if (!$this->owner->isNewRecord)
                Translation::deleteByObject($this->owner);

            $object = Translation::decodeObject($this->owner);

            foreach ($this->_translations as $translation) {
                $translation->object = $object;

                if (!$translation->save())
                    throw new ServerErrorHttpException(\Yii::t('api', 'Translation {lang} cannot be saved', ['lang' => $translation->language]));
            }
        }

        $this->owner->trigger(ActiveRecord::EVENT_AFTER_FIND); #tbd set cest classes types for update cases
    }
}