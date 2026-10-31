<?php

namespace app\modules\document\models;

use Yii;

/**
 *
 *
 * @property integer id
 * @property string code
 * @property string Title
 * @property string PersianTitle
 * @property string ProcessGroup
 * @property integer isActive
 */
class ImsDocumentProcessCode extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cando_table_ims_document_process_code';
    }

    public static function getDb()
    {
        return \app\components\Utility::centralDb();
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    const MANAGEMENT_ORIENTED_PROCESS = 'MOP';
    const CUSTOMER_ORIENTED_PROCESS = 'COP';
    const SUPPORT_ORIENTED_PROCESS = 'SOP';

    public static function getProcessGroupList()
    {
        return [
            self::MANAGEMENT_ORIENTED_PROCESS => \Yii::t("document", 'MOP'),
            self::CUSTOMER_ORIENTED_PROCESS => \Yii::t("document", 'COP'),
            self::SUPPORT_ORIENTED_PROCESS => \Yii::t("document", 'SOP'),

        ];
    }

    public function getProcessGroupLabel()
    {
        $processGroupType = self::getProcessGroupList();
        return $processGroupType[$this->ProcessGroup] ?? 'Unknown';
    }

    public static function getStatus()
    {
        return [
            1 => Yii::t('document', 'Active'),
            0 => Yii::t('document', 'In Active'),
        ];
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'PersianTitle', 'ProcessGroup'], 'required'],
            [['code', 'Title', 'PersianTitle', 'ProcessGroup'], 'string'],
            [['sort', 'isActive'], 'integer'],
        ];
    }

    public function scenarios()
    {
        $scenario = parent::scenarios();
        $scenario['create'] = ['code', 'Title', 'PersianTitle', 'ProcessGroup', 'sort', 'isActive'];
        $scenario['update'] = ['code', 'Title', 'PersianTitle', 'ProcessGroup', 'sort', 'isActive'];
        return $scenario;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('document', 'ID'),
            'code' => Yii::t('document', 'Code'),
            'Title' => Yii::t('document', 'English Title'),
            'PersianTitle' => Yii::t('document', 'Persian Title'),
            'ProcessGroup' => Yii::t('document', 'Process Group'),
            'sort' => Yii::t('automation', 'sort'),
            'isActive' => Yii::t('document', 'Status'),
        ];
    }

}
