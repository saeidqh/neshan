<?php

namespace app\modules\document\models;

use Yii;

/**
 * This is the model class for table "process_code_action".
 *
 * @property integer $id
 * @property integer $ActionIDRef
 * @property integer $CodeIDRef
 * @property string $ActionType
 */
class ProcessCodeAction extends \yii\db\ActiveRecord
{

    public static function getDb()
    {
        return \app\components\Utility::centralDb();
    }

    public static function tableName()
    {
        return 'process_code_action';
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    public function rules()
    {
        return [
            [['CodeIDRef', 'ActionIDRef'], 'required'],
            [['CodeIDRef', 'ActionIDRef'], 'integer'],
            [['ActionType'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('document', 'ID'),
            'CodeIDRef' => Yii::t('document', 'Code'),
            'ActionIDRef' => Yii::t('document', 'Action'),
        ];
    }


    public function getAction(): \yii\db\ActiveQuery
    {
        return $this->hasOne(\app\modules\account\models\FormAction::className(), ['ActionID' => 'ActionIDRef']);
    }

    public function getProcessCode(): \yii\db\ActiveQuery
    {
        return $this->hasOne(ImsDocumentProcessCode::class, ['id' => 'CodeIDRef']);
    }

    public function getWorkflowAction(): \yii\db\ActiveQuery
    {
        return $this->hasOne(\app\modules\workflow\models\Action::className(), ['ActionID' => 'ActionIDRef']);
    }

}
