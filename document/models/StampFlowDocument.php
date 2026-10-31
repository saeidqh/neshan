<?php

namespace app\modules\document\models;

use app\components\jDateTime;
use app\modules\OBS\models\Obsnew;
use app\modules\automation\models\HumanResource;
use Yii;
use \app\components\DocumentTraitPro;

class StampFlowDocument extends \yii\db\ActiveRecord
{
    use DocumentTraitPro;
    public $FirstName;
    public $LastName;
    public $NationalCode;
    public $mobile;
    public $EmploymentType;
    public $Assistance_Title;
    public $FinalStampFile;
    public $SerialNumber;
    public $AttachmentFile;

    public static function tableName()
    {
        return 'document_table_stamp_flow';
    }

    public static function getDb()
    {
        return \app\components\Utility::centralDb();
    }

    public static function primaryKey()
    {
        return ['FlowId'];
    }

    public function rules()
    {
        return [
            [['ReceivedDate', 'RefundDate', 'Created_at', 'Update_at'], 'string'],
            [['ObsIDRef', 'HumanIDRef', 'StampIDRef', 'Created_by', 'Update_by'], 'integer'],
            
            [['ReceivedDate', 'ObsIDRef', 'NationalCode'], 'required'],
            [['ReceivedDate', 'RefundDate'], 'validateHijriDate'],

            [['SerialNumber'], 'integer', 'min' => 0, 'max' => 999999999999999],
            [['FinalStampFile'], 'file', 'extensions' => 'jpg, jpeg, png', 'skipOnEmpty' => true],
            [['SerialNumber'], 'validateRequiredSerialNumber'],
            [['FinalStampFile'], 'validateRequiredFinalStampFile'],
           ['AttachmentFile', 'file',
            'extensions' => 'pdf, doc, docx, jpg, jpeg, png',
            'maxSize' => 500 * 1024,
            'skipOnEmpty' => true,
            'tooBig' => 'حجم فایل مستندات نباید بیشتر از ۵۰۰ کیلوبایت باشد.',
            'wrongExtension' => 'فرمت فایل مستندات مجاز نیست.',
        ],
            [[
                'ObsIDRef',
                'ReceivedDate',
                'RefundDate',
                'NationalCode',
                'SerialNumber',
                'FinalStampFile',
                'StampIDRef',
                'HumanIDRef',
                'Created_at',
                'Created_by',
                'Update_at',
                'Update_by',
                'AttachmentDocument',
                'AttachmentFile',
            ], 'safe'],
            
        ];
    }

    public function scenarios(): array
    {
        $scenario = parent::scenarios();

        $scenario['create'] = [
            'ObsIDRef',
            'ReceivedDate',
            'RefundDate',
            'NationalCode',
            'SerialNumber',
            'FinalStampFile',
            'Created_at',
            'Created_by',
            'Update_at',
            'Update_by',
            'StampIDRef',
            'HumanIDRef',
            'AttachmentFile',
            'AttachmentDocument',
        ];

        $scenario['update'] = [
            'ObsIDRef',
            'ReceivedDate',
            'RefundDate',
            'NationalCode',
            'SerialNumber',
            'FinalStampFile',
            'Update_at',
            'Update_by',
            'HumanIDRef',
            'AttachmentFile',
            'AttachmentDocument',
        ];
        $scenario['create_from_view'] = [
    'ObsIDRef',
    'ReceivedDate',
    'RefundDate',
    'NationalCode',
    'Created_at',
    'Created_by',
    'Update_at',
    'Update_by',
    'StampIDRef',
    'HumanIDRef',
    'AttachmentFile',
    'AttachmentDocument',
];

        $scenario['destroy'] = [
            'RefundDate',
            'Created_at',
            'Created_by',
        ];

        return $scenario;
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->Assistance_Title = isset($this->position) ? $this->position->obsId : null;
    }

    public function beforeValidate()
    {
        $jdate = new jDateTime(true, true, 'Asia/Tehran');

        if ($this->isNewRecord) {
            $this->Created_at = $jdate->date("Y/m/d", false, false);
            $this->Created_by = Yii::$app->session->get('UserID');
        }

        $this->Update_at = $jdate->date("Y/m/d", false, false);
        $this->Update_by = Yii::$app->session->get('UserID');

        return parent::beforeValidate();
    }

    public function attributeLabels()
    {
        return [
            'FlowId' => Yii::t('document', 'FlowId'),
            'ReceivedDate' => Yii::t('document', 'Received Date'),
            'RefundDate' => Yii::t('document', 'Refund Date'),
            'ObsIDRef' => Yii::t('document', 'ObsIDRef'),
            'Created_at' => Yii::t('document', 'Created Date'),
            'Created_by' => Yii::t('document', 'Created by'),
            'Update_at' => Yii::t('document', 'Update at'),
            'Update_by' => Yii::t('document', 'Update by'),
            'EmploymentType' => Yii::t('automation', 'EmploymentType'),
            'SerialNumber' => 'شماره سریال',
            'FinalStampFile' => 'آپلود تصویر نهایی نشان',
            'AttachmentFile' => 'آپلود مستندات',
            'AttachmentDocument' => 'مستندات',
        ];
    }

    public function validateHijriDate($attribute)
    {
        if (!(jDateTime::mCheckdate($this->$attribute) > 0)) {
            $this->addError($attribute, 'Invalid Hijri Date');
        }
    }
    public function validateRequiredSerialNumber($attribute)
{
    $stampModel = $this->stamp;

    if ($stampModel === null && !empty($this->StampIDRef)) {
        $stampModel = StampDocument::findOne(['StampId' => $this->StampIDRef]);
    }

    if ($stampModel === null) {
        return;
    }

    $stampSerialIsEmpty = empty($stampModel->SerialNumber);
    $currentSerialIsEmpty = trim((string)$this->$attribute) === '';

    if ($stampSerialIsEmpty && $currentSerialIsEmpty) {
        $this->addError($attribute, 'شماره سریال الزامی است.');
    }
}

public function validateRequiredFinalStampFile($attribute)
{
    $stampModel = $this->stamp;

    if ($stampModel === null && !empty($this->StampIDRef)) {
        $stampModel = StampDocument::findOne(['StampId' => $this->StampIDRef]);
    }

    if ($stampModel === null) {
        return;
    }

    $finalStampIsEmpty = empty($stampModel->FinalStampDocument);
    $currentFileIsEmpty = empty($this->$attribute);

    if ($finalStampIsEmpty && $currentFileIsEmpty) {
        $this->addError($attribute, 'آپلود تصویر نهایی نشان الزامی است.');
    }
}

    public function getStamp(): \yii\db\ActiveQuery
    {
        return $this->hasOne(StampDocument::class, ['StampId' => 'StampIDRef']);
    }

    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(\app\modules\account\models\User::className(), ['UserId' => 'Created_by']);
    }

    public function getUserUpdate(): \yii\db\ActiveQuery
    {
        return $this->hasOne(\app\modules\account\models\User::className(), ['UserId' => 'Update_by']);
    }

    public function getPosition(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Obsnew::class, ['obsId' => 'ObsIDRef']);
    }

    public function getHuman(): \yii\db\ActiveQuery
    {
        return $this->hasOne(HumanResource::className(), ['Id' => 'HumanIDRef']);
    }

    public function getEmploymentTypeList()
    {
        return [
            'contractual' => 'قرارداد موقت',
            'official' => 'رسمی',
        ];
    }

    public function loadHumanResource()
    {
        $humanResource = $this->human;
        if ($humanResource !== null) {
            $this->FirstName = $humanResource->FirstName;
            $this->LastName = $humanResource->LastName;
            $this->NationalCode = $humanResource->NationalCode;
            $this->EmploymentType = $humanResource->EmploymentType;
        }

        if ($this->stamp) {
            $this->SerialNumber = $this->stamp->SerialNumber;
        }
    }

    public function getAssistanceTitle()
    {
        $position = $this->position;
        if ($position !== null) {
            $titles = $position->allParentTitle;
            if (count($titles) > 0) {
                array_pop($titles);
            }
            return implode(" | ", $titles);
        }

        return null;
    }
    public function getConfigDoc()
{
    return [
        'folder' => 'stampFlow',
        'key' => 'FlowId',
        'files' => [
            'AttachmentFile' => 'attachment',
        ],
        'selectedFile' => 'AttachmentFile',
    ];
}
}