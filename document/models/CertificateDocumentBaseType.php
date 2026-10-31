<?php

namespace app\modules\document\models;

use Yii;

/**
 * This is the model class for table "cando_table_certificate_document_base_type".
 *
 * @property integer $id
 * @property integer $activityDocumentIdRef
 * @property integer $baseTypeIdRef
 * @property integer $value
 */
class CertificateDocumentBaseType extends \yii\db\ActiveRecord {

    const BASE_ID = 1;
    const AUTHORIZED_WORK_ID = 2;
    const AUTHORIZED_CAPACITY_ID = 3;
    const APPROVED_CONTRACT_ID = 4;

    /**
     * @inheritdoc
     */
    public static function tableName() {
            return 'cando_table_certificate_document_base_type';
    }

    public static function primaryKey() {
        return ['id'];
    }

    public static function getDb() {
        return \app\components\Utility::centralDb();
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'activityDocumentIdRef', 'baseTypeIdRef','value'], 'required'],
            [['id', 'activityDocumentIdRef', 'baseTypeIdRef', 'value'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'id',
            'activity Document Id' => 'activity Document Id',
            'baseTypeIdRef' => 'baseType Id',
            'value' => 'value',
        ];
    }

    public function getBaseType(){

        return $this->hasOne(\app\modules\document\models\CertificateBaseType::className(),['baseTypeId'=>'baseTypeIdRef']);
    }
    public function getDocumentActivity()
    {
        return $this->hasOne(\app\modules\document\models\CertificateDocumentActivity::className(), ['id' => 'activityDocumentIdRef']);
    }

}
