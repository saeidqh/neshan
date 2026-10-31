<?php

namespace app\modules\document\models;

use Yii;
use app\components\jDateTime;
use yii\web\UploadedFile;
use app\components\Documents;

/**
 * This is the model class for table "tblContractRevisionFiles".
 *
 * @property integer $id
 * @property integer $companyIdRef
 * @property integer $activityIdRef
 * @property integer $imsDocumentIdRef
 * @property string $fileName
 * @property integer $lastUserIdRef
 * @property string $lastUpdateDate
 * @property integer $lastUserIdRef
 */
class CompanyActivityDocument extends \yii\db\ActiveRecord {
    use \app\components\DocumentTraitPro;
    
    public function getConfigDoc() {
        return [
            'folder' => 'companyClauseActivity',
            'files'=>[
                'file'=>null,
            ],
        ];
    }

    public $file;

    public static function tableName() {
        if (Yii::$app->params['dmbs'] == 'mysql') {
            return 'cando_table_ims_company_activity_document';
        } else {
            return 'tblCompanyActivityDocument';
        }
    }

    public static function primaryKey() {
        return ['id'];
    }

    public static function getDb() {
        return \app\components\Utility::centralDb();
    }

    public function scenarios() {
        $scenario = parent::scenarios();
        return $scenario;
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['companyIdRef', 'activityIdRef'], 'required'],
            [['companyIdRef', 'activityIdRef', 'imsDocumentIdRef', 'lastUserIdRef', 'isActive'], 'integer'],
            [['fileName', 'lastUpdateDate'], 'string'],
            ['file', 'file', 'skipOnEmpty' => true],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'ID' => 'ID',
            'RiderID' => 'Rider ID',
            'FileName' => Yii::t('contract','File Name'),
            'Description' => Yii::t('contract','Description'),
            'hasChange' => 'Has Change',
            'isDeleted' => 'Is Deleted',
            'userIdRef1' => 'User Id Ref1',
            'insertDateHijri' => 'Insert Date Hijri',
            'userIdRef2' => 'User Id Ref2',
            'lastUpdateHijri' => 'Last Update Hijri',
            'workflowContractIdRef' => 'Workflow Contract Id Ref',
            'file'  => Yii::t('contract','File'),
        ];
    }

    public function beforeValidate() {
        $jdate = new jDateTime(true, true, 'Asia/Tehran');
        $this->lastUpdateDate = $jdate->date("Y/m/d", false, false);
        $this->lastUserIdRef = Yii::$app->session->get('UserID');
        return parent::beforeValidate($this);
    }

    public function saveRiderDocument() {
        $jdate = new jDateTime(true, true, 'Asia/Tehran');
        $requestDateHijri = $jdate->date("Y/m/d-hi", false, false);
        $this->file = UploadedFile::getInstance($this, 'file');
        $this->ID = static::find()->max('ID') + 1;
        $this->insertDateHijri = $requestDateHijri;
        $this->lastUpdateHijri = $requestDateHijri;
        $this->userIdRef1 = Yii::$app->user->id;
        $this->userIdRef2 = Yii::$app->user->id;
        $this->hasChange = 1;
        $this->isDeleted = 0;
        
        if ($this->file instanceof UploadedFile) {
            $this->FileName = $this->file->name;
        }
        if ($this->save() && $this->upload()) {
            
            Yii::$app->replication->synch($this);
            return 1;
        } else {
            return 0;
        }
    }

    public function getCompany() {
        return $this->hasOne(\app\modules\OBS\models\Person::className(), ['PersonID' => 'companyIdRef']);
    }

    public function getLastUser() {
        return $this->hasOne(\app\modules\account\models\User::className(), ['UserID' => 'lastUserIdRef']);
    }
    
    public function getActivity() {
        return $this->hasOne(\app\modules\document\models\ClauseActivity::className(), ['id' => 'activityIdRef']);
    }
    
    public function getImsDocument() {
        return $this->hasOne(\app\modules\document\models\ImsDocument::className(), ['id' => 'imsDocumentIdRef']);
    }

}
