<?php

namespace app\modules\document\models;

use Yii;
use app\components\jDateTime;

/**
 * This is the model class for table "tblIMSDocumentCompany".
 *
 * @property ID
 * @property DocumentIDRef
 * @property CompanyIDRef
 * @property isDeleted
 * @property LastUserIDRef
 * @property LastUpdate
 */
class ImsDocumentCompany extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'cando_table_ims_document_company';
    }

    public static function getDb() {
        return \app\components\Utility::centralDb();
    }

    public static function primaryKey() {
        return ['ID'];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['LastUpdate'], 'string'],
            [['DocumentIDRef', 'CompanyIDRef', 'isDeleted', 'LastUserIDRef'], 'integer'],
            [['DocumentIDRef', 'CompanyIDRef'], 'required'],
            [['LastUserIDRef', 'LastUpdate'], 'required'],
        ];
    }

    public function scenarios() {
        $scenario = parent::scenarios();
        $scenario['create'] = ['DocumentIDRef', 'CompanyIDRef', 'isDeleted', 'LastUserIDRef', 'LastUpdate'];
        $scenario['delete'] = ['isDeleted', 'LastUserIDRef', 'LastUpdate'];
        return $scenario;
    }

    public function beforeValidate() {
        $jdate = new jDateTime(true, true, 'Asia/Tehran');
        $this->LastUpdate = $jdate->date("Y/m/d", false, false);
        $this->LastUserIDRef = Yii::$app->session->get('UserID');
        if ($this->scenario == 'create') {
            $this->CompanyIDRef = Yii::$app->session->get('CompanyID');
        }
        return parent::beforeValidate($this);
    }

    public function attributeLabels() {
        return [
            'id' => Yii::t('document', 'ID'),
        ];
    }

    public function getCompany() {
        return $this->hasOne(\app\modules\OBS\models\Person::className(), ['PersonID' => 'CompanyIDRef']);
    }

    public function getImsDocument() {
        return $this->hasOne(ImsDocument::className(), ['id' => 'DocumentIDRef']);
    }

    public function getLastUser() {
        return $this->hasOne(\app\modules\account\models\User::className(), ['UserID' => 'LastUserIDRef']);
    }

}
