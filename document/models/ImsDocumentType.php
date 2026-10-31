<?php
namespace app\modules\document\models;

use Yii;

/**
 * @author <mail:mortezakml@gmail.com & Tel:09366263397>
 * 
 * @property integer ID
 * @property string Code
 * @property string Title
 * @property string PersianTitle
 * @property integer isActive
 */
class ImsDocumentType extends \yii\db\ActiveRecord {
    
    /**
     * @inheritdoc
    */
    
    public static function tableName() {
        return 'cando_table_ims_document_type';
    }

    public static function getDb() {
        return \app\components\Utility::centralDb();
    }
    
    public static function primaryKey() {
        return['ID'];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['Code', 'PersianTitle'], 'required'],
            [['Code', 'Title', 'PersianTitle'], 'string'],
            [['ID', 'sort', 'isActive'], 'integer'],
        ];
    }

    public function scenarios() {
        $scenario = parent::scenarios();
//        $scenario['create'] = ['Code', 'Title', 'PersianTitle'];
//        $scenario['update'] = ['Code', 'Title', 'PersianTitle'];
        return $scenario;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('document', 'ID'),
            'code' => Yii::t('document', 'code'),
            'Title' => Yii::t('document', 'Title'),
            'PersianTitle' => Yii::t('document', 'Persian Title'),
            'sort' => Yii::t('automation', 'sort'),
            'isActive' => Yii::t('document', 'Status'),
        ];
    }

}
