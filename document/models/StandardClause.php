<?php

namespace app\modules\document\models;

use Yii;

/**
 * This is the model class for table "dbo.tblStandardClause".
 *
 * @property integer $id
 * @property integer $StandardIdRef
 * @property integer $ClauseIdRef
 */
class StandardClause extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        if (Yii::$app->params['dmbs'] == 'mysql') {
            return 'cando_table_ims_standard_clause';
        } else {
            return 'dbo.tblStandardClause';
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
            [['StandardIdRef', 'ClauseIdRef'], 'required'],
            [['StandardIdRef', 'ClauseIdRef'], 'integer'],
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
