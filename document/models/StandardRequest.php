<?php

namespace app\modules\document\models;

use Yii;
use app\components\jDateTime;

/**
 * This is the model class for table "dbo.tblStandardRequest".
 *
 * @property integer $id
 * @property integer $companyIdRef
 * @property integer $projectIdRef
 * @property string $type
 * @property string $year
 * @property string $requestDate
 * @property string $holdingDate
 * @property string $auditStartDate
 * @property string $auditFinishDate
 * @property string $managerialDate
 * @property string $auditDate
 * @property string $auditPlanStartDate
 * @property string $auditPlanFinishDate
 * @property integer $createdBy
 * @property integer $managerialUser
 * @property integer $holdingUserIdRef
 * @property integer $enableSimilarActivity
 * @property integer $isLock
 */
class StandardRequest extends \yii\db\ActiveRecord {

    public $holdingVerify;
    public $standardArray;
    public $projectArray;
    /**
     * @inheritdoc
     */
    public static function tableName() {
        if (Yii::$app->params['dmbs'] == 'mysql') {
            return 'cando_table_ims_standard_request';
        } else {
            return 'dbo.tblStandardRequest';
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
            [['companyIdRef', 'projectArray','standardArray', 'type', 'year', 'auditStartDate', 'auditFinishDate', 'enableSimilarActivity', 'faManagementSystemScope', 'enManagementSystemScope'], 'required'],
//            [['companyIdRef', 'projectArray','standardArray', 'type', 'year', 'auditStartDate', 'auditFinishDate', 'enableSimilarActivity', 'faManagementSystemScope', 'enManagementSystemScope'], 'required', 'when' => function () {
//                    return empty($this->managerialDate);
//                },
//            ],            
            [['companyIdRef', 'createdBy', 'year', 'managerialUser', 'holdingUserIdRef', 'enableSimilarActivity', 'isLock', 'holdingVerify'], 'integer'],
            [['type', 'year', 'requestDate', 'holdingDate', 'auditStartDate', 'auditFinishDate', 'managerialDate', 'auditDate', 'auditPlanStartDate', 'auditPlanFinishDate', 'faManagementSystemScope', 'enManagementSystemScope'], 'string'],
            [['requestDate', 'auditStartDate', 'auditFinishDate', 'auditDate', 'auditPlanStartDate', 'auditPlanFinishDate'], 'validatDate'],
            [['auditStartDate'], 'validatAuditStartDate', 'skipOnEmpty' => true],
            [['auditFinishDate'], 'validatAuditFinishDate', 'skipOnEmpty' => true],
            [['enManagementSystemScope'], 'enManagementSystemScopeValidate', 'skipOnEmpty' => true]
        ];
    }

    public function scenarios() {
        $scenario = parent::scenarios();
        $scenario['holdingUpdate'] = ['holdingVerify', 'auditPlanStartDate', 'auditPlanFinishDate', 'isLock'];
        return $scenario;
    }

    public function beforeValidate() {
        if(is_array($this->standardArray))
            $this->standard = implode(',', $this->standardArray);
        if(is_array($this->projectArray))
            $this->projectIdRef = implode(',', $this->projectArray);
        return parent::beforeValidate($this);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('document', 'ID'),
            'companyIdRef' => Yii::t('document', 'Company'),
            'projectIdRef' => Yii::t('document', 'Project Scope'),
            'type' => Yii::t('document', 'Request Type'),
            'year' => Yii::t('document', 'Year'),
            'requestDate' => Yii::t('document', 'Request Date'),
            'holdingDate' => Yii::t('document', 'Holding Date'),
            'auditStartDate' => Yii::t('document', 'Audit Start Date'),
            'auditFinishDate' => Yii::t('document', 'Audit Finish Date'),
            'auditDate' => Yii::t('document', 'Audit Date'),
            'auditPlanStartDate' => Yii::t('document', 'Audit Plan Start Date'),
            'auditPlanFinishDate' => Yii::t('document', 'Audit Plan Finish Date'),
            'result' => Yii::t('document', 'Standard Result'),
            'standard' => Yii::t('document', 'Standard'),
            'standardArray' => Yii::t('document', 'Standard'),
            'projectArray' => Yii::t('document', 'Project Scope'),
            'isLock' => Yii::t('document', 'Lock Standard Request'),
        ];
    }

    public function enManagementSystemScopeValidate($attribute) {
        $patern = "/^[a-zA-Z\s0-9().,-_:;&$#]+$/";
        if(!preg_match($patern, $this->$attribute)) {
            $this->addError($attribute, 'فقط حروف لاتین مجاز می باشد');
        }
    }

    public function getTypeList() {
        $sequerityList = [
            'new' => 'صدور',
            'extend' => 'مراقبتی'
        ];
        return $sequerityList;
    }

    public function getType($type) {
        switch ($type) {
            case 'new':
                return 'صدور';
            case 'extend':
                return 'مراقبتی';
            default:
                return 'invalid Type';
        }
    }

    public function getResultList() {
        $sequerityList = [
            'confirmation' => 'تایید',
            'conditional' => 'مشروط',
            'not-approved' => 'عدم تایید'
        ];
        return $sequerityList;
    }

    public function getResultType($result) {
        switch ($result) {
            case 'confirmation':
                return 'تایید';
            case 'conditional':
                return 'مشروط';
            case 'not-approved':
                return 'عدم تایید';    
            default:
                return 'invalid Result';
        }
    }

    public function getEnableSimilarActivityList() {
        $similarActivityList = [
            1 => 'بله رسیده ایم',
            0 => 'در دوره بعدی خواهیم رسید'
        ];
        return $similarActivityList;
    }

    public function validatDate($attribute) {
        if (($this->$attribute != null && strlen($this->$attribute) != 0) && !(jDateTime::mCheckdate($this->$attribute) > 0))
            $this->addError($attribute, 'تاریخ معتبر نمی باشد');
    }

    public function validatAuditStartDate($attribute) {
        if(!empty($this->$attribute)) {
            $firstTime = new \DateTime($this->requestDate);
            $secondTime = new \DateTime($this->$attribute);
            $interval = $firstTime->diff($secondTime);
            if ($interval->invert || $interval->days < 7)
                $this->addError($attribute, 'تاریخ شروع آمادگی ممیزی باید حداقل 7 روز بعد از تاریخ درخواست باشد');
        }
    }

    public function validatAuditFinishDate($attribute) {
        if(!empty($this->$attribute) && !empty($this->auditStartDate)) {
            $firstTime = new \DateTime($this->auditStartDate);
            $secondTime = new \DateTime($this->$attribute);
            $interval = $firstTime->diff($secondTime);
            if ($interval->invert || $interval->days < 7)
                $this->addError($attribute, 'تاریخ پایان آمادگی ممیزی باید حداقل 7 روز بعد از تاریخ شروع آمادگی ممیزی باشد');
        }
    }

    public function getCompany() {
        return $this->hasOne(\app\modules\OBS\models\Person::className(), ['PersonID' => 'companyIdRef']);
    }
    public function getProject() {
        return $this->hasOne(\app\modules\wbs\models\Epsnew::className(), ['EPSID' => 'projectIdRef']);
    }

    public function getCreatedUser() {
        return $this->hasOne(\app\modules\account\models\User::className(), ['UserID' => 'createdBy']);
    }

    public function getHoldingUser() {
        return $this->hasOne(\app\modules\account\models\User::className(), ['UserID' => 'holdingUserIdRef']);
    }

    public function getManagerial() {
        return $this->hasOne(\app\modules\account\models\User::className(), ['UserID' => 'managerialUser']);
    }

    public function getStandardTitle() {
        $standards = Standard::find()->where(['id' => explode(',', $this->standard)])->all();
        $standardTitle = '';
        foreach($standards as $standard) {
            if($standardTitle != '') $standardTitle .= ' | ';
            $standardTitle .= $standard->title;
        }
        return $standardTitle;
    }

    public function getProjectTitle() {
        $projects = \app\modules\wbs\models\Epsnew::find()->where(['EPSID' => explode(',', $this->projectIdRef)])->all();
        $projectTitle = '';
        foreach($projects as $project) {
            if($projectTitle != '') $projectTitle .= ' | ';
            $projectTitle .= $project->EPSTitle . ' - ' . $project->projectCode;
        }
        return $projectTitle;
    }

}
