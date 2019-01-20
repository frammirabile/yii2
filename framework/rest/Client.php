<?php
namespace yii\rest;

/**
 * Rest client model
 *
 * @property-read int $id
 * @property-read string $name
 * @property-read string $secret
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class Client extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    protected $savingNotAllowed = true;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'name', 'secret'], 'required'],
            [['id', 'secret'], 'string', 'max' => 32],
            ['name', 'string', 'max' => 10],
            ['id', 'unique'],
            ['name', 'unique'],
            ['secret', 'unique']
        ];
    }
}