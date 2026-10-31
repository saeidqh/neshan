<?php

namespace app\modules\document\models;

use Yii;

/**
 * This is the model class for table "dbo.tblStandard".
 *
 * @property integer $id
 * @property string $title
 * @property string $area
 * @property string $description
 * @property integer $isActive
 * @property integer $sort
 */
class Standard extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        if (Yii::$app->params['dmbs'] == 'mysql') {
            return 'cando_table_ims_standard';
        } else {
            return 'dbo.tblStandard';
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
            [['title', 'area', 'description'], 'required'],
            [['isActive', 'sort'], 'integer'],
            [['title', 'area', 'description'], 'string'],
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
            'title' => Yii::t('document', 'Standard'),
            'area' => Yii::t('document', 'Area'),
            'description' => Yii::t('document', 'Description'),
            'isActive' => Yii::t('document', 'Active'),
            'sort' => Yii::t('document', 'sort'),
        ];
    }

}
