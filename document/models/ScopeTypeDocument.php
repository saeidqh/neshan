<?php

namespace app\modules\document\models;

use app\components\Utility;
use Yii;

/**
 * This is the model class for table "StampScopeDocument".
 *
 * @property integer $ScopeId
 * @property string $ScopeTitle
 * @property string $ScopeType
 *
 */
class ScopeTypeDocument extends \yii\db\ActiveRecord {


    public static function tableName() {
        return 'document_table_scope_type';
    }

    public static function getDb() {
        return \app\components\Utility::centralDb();
    }



    public static function primaryKey() {
        return ['ScopeId'];
    }

    const SCOPE_TYPE_INTERNAL = 'internal';
    const SCOPE_TYPE_EXTERNAL = 'external';


    public function getScopeTypeList(){
        return [
          self::SCOPE_TYPE_INTERNAL => \Yii::t("document",'internal'),
          self::SCOPE_TYPE_EXTERNAL => \Yii::t("document",'external'),
        ];
    }

    public function getStatusLabal()
    {
        $scpoeType = self::getScopeTypeList();
        return $scpoeType[$this->ScopeType] ?? 'Unknown';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['ScopeTitle', 'ScopeType'], 'string'],
        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'ScopeId' => Yii::t('document', 'ScopeId'),
            'ScopeTitle' => Yii::t('document', 'ScopeTitle'),
            'ScopeType' => Yii::t('document', 'ScopeType'),
        ];
    }




}
