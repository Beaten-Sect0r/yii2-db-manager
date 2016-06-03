<?php

/**
 * Created by solly [02.06.16 9:46]
 */

namespace bs\dbManager\models;

use Yii;
use yii\base\Model;

/**
 * Class Dump
 *
 * @package bs\dbManager\models
 */
class Restore extends Model
{
    /**
     * @var
     */
    public $db;

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
            [['runInBackground'], 'boolean'],
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
            'preset'  => Yii::t('dbManager', 'Custom restore preset'),
            'runInBackground' => Yii::t('dbManager', 'run in background'),
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
    public function makeRestoreOptions()
    {
        return [
            'preset' => $this->preset ? $this->preset : false,
            'presetData' => $this->preset ? $this->customOptions[$this->preset] : '',
        ];
    }

    public function getDBList()
    {
        return array_combine($this->dbList, $this->dbList);
    }
}
