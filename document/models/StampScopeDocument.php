<?php

namespace app\modules\document\models;

use app\components\Utility;
use \yii\db\ActiveQuery;
use Yii;

/**
 * This is the model class for table "ScopeStampDocument".
 *
 * @property integer $id
 * @property string $StampIDRef
 * @property string $ScopeIDRef
 *
 */
class StampScopeDocument extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return 'document_table_stamp_scope';
    }

    public static function getDb()
    {
        return \app\components\Utility::centralDb();
    }


    public static function primaryKey()
    {
        return ['id'];
    }


    public function rules()
    {
        return [
            [['StampIDRef', 'ScopeIDRef',], 'integer'],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => Yii::t('document', 'Id'),
            'StampIDRef' => Yii::t('document', 'StampID'),
            'ScopeIDRef' => Yii::t('document', 'ScopeID'),

        ];
    }

    public function getStamp(): \yii\db\ActiveQuery
    {
        return $this->hasOne(StampDocument::class, ['StampId' => 'StampIDRef']);
    }

    public function getScope(): \yii\db\ActiveQuery
    {
        return $this->hasOne(ScopeTypeDocument::class, ['ScopeId' => 'ScopeIDRef']);
    }


}
