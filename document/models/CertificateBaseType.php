<?php

namespace app\modules\document\models;

use Yii;

/**
 * This is the model class for table "cando_table_certificate_base_type".
 *
 * @property integer $baseTypeId
 * @property integer $baseTypeTitle
 */
class CertificateBaseType extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
            return 'cando_table_certificate_base_type';
    }

    public static function primaryKey() {
        return ['baseTypeId'];
    }

    public static function getDb() {
        return \app\components\Utility::centralDb();
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['baseTypeId', 'baseTypeTitle'], 'required'],
            [['baseTypeId'], 'integer'],
            [['baseTypeTitle'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'baseTypeId' => 'base Type Id',
            'baseTypeTitle' => 'Base Type Title',
        ];
    }

    public function getDocumentBase(){
        return $this->hasMany(\app\modules\document\models\CertificateDocumentBaseType::className(),['baseTypeIdRef'=>'baseTypeId']);
    }

}
