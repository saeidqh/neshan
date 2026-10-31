<?php

namespace app\modules\document\models;

use app\components\Utility;
use Yii;

/**
 * This is the model class for table "CertificateDocument".
 *
 * @property integer $certificateId
 * @property integer $certificateTypeIdRef
 * @property string $issuingOrganization
 * @property string $issueDate
 * @property string $expirationDate
 * @property integer $companyIdRef
 * @property integer $standardIdRef
 * @property string $certificateDocument
 *
 */
class CertificateDocument extends \yii\db\ActiveRecord {

    use \app\components\DocumentTraitPro;

    public function getConfigDoc() {
        return [
            'folder' => 'certificate',
            'files' => [
                'certificateFile' => null,
            ],
        ];
    }

    public $certificateFile;
    public $certificateParentType;
    public $activityIdList;
    public $certificateType;
    public $company;
    public $activityId;
    public $activityTitle;
    public $base;
    public $authorizedWork;
    public $authorizedCapacity;
    public $approvedContract;
    public $activityDocumentId;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'cando_table_certificate_document';
    }

    public static function getDb() {
        return \app\components\Utility::centralDb();
    }



    public static function primaryKey() {
        return ['certificateId'];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['issuingOrganization', 'issueDate', 'expirationDate', 'certificateDocument'], 'string'],
            /*[['certificateParentType'], 'required', 'when' => function ($model) {
                return ($model->certificateTypeIdRef == 4)or($model->certificateTypeIdRef == 5)or($model->certificateTypeIdRef == 6)or($model->certificateTypeIdRef == 7);
            }],*/
            [['activityIdList'], 'required', 'when' => function () {
                return false;
            }, 'whenClient' => "function (attribute, value) {
                    return false;
                }"
            ],
            [[ 'certificateId', 'certificateTypeIdRef', 'companyIdRef','certificateParentType', 'standardIdRef'], 'integer'],
            [['issuingOrganization', 'issueDate', 'expirationDate', 'companyIdRef', 'standardIdRef'], 'required'],
            ['certificateFile', 'file', 'extensions' => 'xls, xlsx, zip, rar, doc, docx, ppt, pptx, pps, pdf, png, jpg', 'maxSize' => 100 * 1024],

        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'certificateId' => Yii::t('document', 'certificate Id'),
            'certificateTypeIdRef' => Yii::t('document', 'certificate Type'),
            'issuingOrganization' => Yii::t('document', 'issuing Organization'),
            'issueDate' => Yii::t('document', 'issue Date'),
            'expirationDate' => Yii::t('document', 'expiration Date'),
            'companyIdRef' => Yii::t('document', 'company'),
            'certificateFile' => Yii::t('document', 'certificate File'),
            'activityIdList' => Yii::t('document', 'Activity'),
            'standardIdRef' => Yii::t('document', 'Standard/Model'),
        ];
    }

    public function getCompany() {
        return $this->hasOne(\app\modules\OBS\models\Company::className(), ['companyIdRef' => 'companyIdRef']);
    }

    public function getCertificateType() {
        return $this->hasOne(\app\modules\document\models\CertificateDocumentType::className(), ['certificateTypeId' => 'certificateTypeIdRef']);
    }

    public function getCertificateActivity(){
        return $this->hasMany(\app\modules\document\models\CertificateActivity::className(),['certificateIdRef'=>'certificateId']);
    }

    public function getActivityDocument(){
        return $this->hasMany(\app\modules\document\models\CertificateDocumentActivity::className(),['certificateIdRef'=>'certificateId']);
    }

    public function getStandard()
    {
        $this->hasOne(\app\modules\document\models\Standard::className(), ['id' => 'standardIdRef']);
    }
}
