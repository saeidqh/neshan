<?php

namespace app\modules\document\models;

use Yii;

/**
 * This is the model class for table "dbo.tblImsDocumentClause".
 *
 * @property integer $id
 * @property integer $DocumentIdRef
 * @property integer $ClauseIdRef
 */
class ImsDocumentClause extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        if (Yii::$app->params['dmbs'] == 'mysql') {
            return 'cando_table_ims_document_clause';
        } else {
            return 'dbo.tblImsDocumentClause';
        }
    }

    public static function primaryKey() {
        return ['id'];
    }

    public static function getDb() {
        return \app\components\Utility::centralDb();
    }

    public function rules() {
        return [
            [['DocumentIdRef', 'ClauseIdRef'], 'required'],
            [['DocumentIdRef', 'ClauseIdRef'], 'integer'],
        ];
    }

    public function scenarios() {
        $scenario = parent::scenarios();
        return $scenario;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('document', 'ID'),
        ];
    }

}
