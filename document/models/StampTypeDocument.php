<?php

namespace app\modules\document\models;

use app\components\Utility;
use Yii;

/**
 * This is the model class for table "StampTypeDocument".
 *
 * @property integer $StampTypeId
 * @property string $TypeName
 * @property string $Description
 *
 */
class StampTypeDocument extends \yii\db\ActiveRecord {
    
    const PERSONEL_STAMP_TYPE = 4;

    public static function tableName() {
        return 'document_table_stamp_type';
    }

    public static function getDb() {
        return \app\components\Utility::centralDb();
    }

    public static function primaryKey() {
        return ['StampTypeId'];
    }


    public function rules() {
        return [
            [['TypeName', 'Description'], 'string'],
        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'StampTypeId' => Yii::t('document', 'StampTypeId'),
            'TypeName' => Yii::t('document', 'TypeName'),
            'Description' => Yii::t('document', 'Description'),
        ];
    }


}
