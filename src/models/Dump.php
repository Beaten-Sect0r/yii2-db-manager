<?php

namespace bs\dbManager\models;

use Yii;
use yii\base\Model;

/**
 * Class Dump.
 */
class Dump extends Model
{
    /**
     * @var
     */
    public $db;

    /**
     * @var bool
     */
    public $isArchive = false;

    /**
     * @var bool
     */
    public $schemaOnly = false;

    /**
     * @var bool
     */
    public $preset = null;

    /**
     * @var bool
     */
    public $runInBackground = false;

    /**
     * @var array
     */
    protected $dbList;

    /**
     * @var array
     */
    protected $customOptions;


    /**
     * Dump constructor.
     *
     * @param array $dbList
     * @param array $customOptions
     * @param array $config
     */
    public function __construct(array $dbList, array $customOptions = [], array $config = [])
    {
        $this->dbList = $dbList;
        $this->customOptions = $customOptions;

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['db', 'required'],
            ['db', 'in', 'range' => $this->dbList],
            [['isArchive','runInBackground','schemaOnly'], 'boolean'],
            ['preset', 'in', 'range' => array_keys($this->customOptions), 'skipOnEmpty' => true],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'db' => Yii::t('dbManager', 'Database'),
            'isArchive' => Yii::t('dbManager', 'gzip'),
            'schemaOnly' => Yii::t('dbManager', 'Dump only schema'),
            'preset' => Yii::t('dbManager', 'Custom dump preset'),
            'runInBackground' => Yii::t('dbManager', 'Run in background'),
        ];
    }

    /**
     * @return array
     */
    public function hasPresets()
    {
        return !empty($this->customOptions);
    }

    /**
     * @return array
     */
    public function getCustomOptions()
    {
        return $this->customOptions;
    }

    /**
     * @return array
     */
    public function makeDumpOptions()
    {
        return [
            'isArchive' => $this->isArchive,
            'schemaOnly' => $this->schemaOnly,
            'preset' => $this->preset ? $this->preset : false,
            'presetData' => $this->preset ? $this->customOptions[$this->preset] : '',
        ];
    }
}
