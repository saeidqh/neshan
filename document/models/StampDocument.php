<?php

namespace app\modules\document\models;

use app\components\jDateTime;
use app\modules\OBS\models\Obsnew;
use Yii;
use app\modules\workflow\models\ProcessEntity;
use app\modules\workflow\models\ActionEntity;
use app\modules\workflow\models\Action;
use app\modules\automation\models\PositionType;
use \app\components\DocumentTraitPro;
use app\modules\workflow\models\Process;

class StampDocument extends \yii\db\ActiveRecord
{
    use \app\components\DocumentTraitPro;
    public $DestroyAttachmentFile;
    public $StampFile;
    public $FinalStampFile;
    public $internalScopeIds;
    public $externalScopeIds;
    public $Assistance_Title;
    public $FullName;
    public $NationalCode;

    public static function tableName()
    {
        return 'document_table_stamp';
    }

    public static function getDb()
    {
        return \app\components\Utility::centralDb();
    }

    public static function primaryKey()
    {
        return ['StampId'];
    }

public function getConfigDoc()
{
    $selectedFile = 'StampFile';

    if ($this->scenario === 'destroy') {
        $selectedFile = 'DestroyAttachmentFile';
    }

    return [
        'folder' => 'stamp',
        'key' => 'StampId',
        'files' => [
            'StampFile' => null,
            'FinalStampFile' => 'final',
            'DestroyAttachmentFile' => 'destroy-attachment',
        ],
        'selectedFile' => $selectedFile,
    ];
}

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_DESTROYED = 'destroyed';
    const STATUS_IN_PROCESS = 'in_process';
    const STATUS_IN_REFUND = 'in_refund';
    const LICENSE_ISSUE_UPPER = 1;    // رده بالادستی - 1181
    const LICENSE_ISSUE_INTERNAL = 2; // داخلی / واحد مالک - 1177

    public function getStatusList()
    {
        return [
            self::STATUS_PENDING => Yii::t("document", 'pending'),
            self::STATUS_APPROVED => Yii::t("document", 'approved'),
            self::STATUS_DESTROYED => Yii::t("document", 'destroyed'),
            self::STATUS_REJECTED => Yii::t("document", 'rejected'),
            self::STATUS_IN_PROCESS => Yii::t("document",  'in_process'),
            self::STATUS_IN_REFUND => Yii::t("document", 'درخواست امحاء'),
        ];
    }

    public function getStatusLabal()
    {
        $status = self::getStatusList();
        return $status[$this->Status] ?? 'Unknown';
    }

    public function rules()
    {
        return [
            [['StampTitle', 'StampPurpose','LastUpdate', 'StampCode','RequestDate','DestroyDate','DescriptionDestroy', 'Status','NationalCode','FullName'], 'string'],
            [['ObsIDRef','StampTypeIDRef','LastUserIDRef','DestroyUserIDRef', 'ProcessCodeIDRef', 'LastFlowIDRef','HumanResourceIDRef', 'OrganizationalMaker','LicenseIssueType' ], 'integer'],
            [['DestroyAttachmentFile'], 'file', 'skipOnEmpty' => true,'extensions' => 'jpg, jpeg, png, pdf', 'checkExtensionByMimeType' => false,'maxSize' => 500 * 1024,'tooBig' => 'حداکثر حجم فایل 500 کیلوبایت است.', 'on' => ['destroy'] ],
            [['SerialNumber'], 'integer'],
            [['StampTitle', 'StampTypeIDRef', 'RequestDate', 'ObsIDRef'], 'required'],
            [['internalScopeIds', 'externalScopeIds'], 'safe'],
            ['StampFile','file','extensions' => ['png', 'jpg', 'jpeg'],'maxSize' => 500 * 1024,'skipOnEmpty' => false,'on' => 'default', // یا سناریوی create
                'when' => function ($model) {
                    return $model->isNewRecord;
                },
            ],
            // برای update همیشه اختیاری
            ['StampFile','file', 'extensions' => ['png', 'jpg', 'jpeg'],'maxSize' => 500 * 1024,'skipOnEmpty' => true,],
            ['FinalStampFile', 'file', 'extensions' => 'png, jpg, jpeg', 'maxSize' => 500000, 'skipOnEmpty' => true],
            [['DestroyDate'], 'required', 'on' => 'destroy'],
            [['DestroyDate'], 'validateHijriDate', 'on' => 'destroy'],
            [['SerialNumber'], 'integer', 'min' => 0, 'max' => 999999999999999],
            [['OrganizationalMaker'], 'integer', 'min' => 1, 'max' => 2],
            [['OrganizationalMaker'], 'required', 'message' => Yii::t('document', 'Organizational Maker is required')],
            [['NationalCode', 'FullName'], 'required', 'when' => function ($model) {
                    return $model->StampTypeIDRef == StampTypeDocument::PERSONEL_STAMP_TYPE;
                },
                'whenClient' => "function (attribute, value) {
                    return $('#stampdocument-stamptypeidref').val() == 4;
                }"
            ],
            [['LicenseIssueType'], 'in', 'range' => [1, 2]],
        ];
    }

    public function scenarios(): array
    {
        $scenario = parent::scenarios();
        $scenario['destroy'] = ['DestroyDate', 'DescriptionDestroy', 'DestroyAttachmentFile', 'DestroyAttachment'];
        $scenario['Status'] = ['Status'];
        return $scenario;
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->Assistance_Title = isset($this->position) ? $this->position->obsId : null;
    }

public function beforeValidate()
{
    if (!parent::beforeValidate()) {
        return false;
    }

    if (empty($this->ProcessCodeIDRef) && !empty($this->ObsIDRef)) {
        $this->ProcessCodeIDRef = $this->getProcessCodeIdRef();
    }
    if ($this->scenario != 'destroy') {
        $now = new \DateTime('now', new \DateTimeZone('Asia/Tehran'));
        $requestDate = $now->format('Y-m-d');
        $jdate = new jDateTime(false, true, 'Asia/Tehran');
        $requestDateHijri = $jdate->date('Y/m/d-Hi', $now->getTimestamp());
    } else {
        $this->DestroyUserIDRef = Yii::$app->session->get('UserID');
    }
    if ($this->isNewRecord && $this->scenario != 'destroy') {
        if ((int)$this->OrganizationalMaker === 2) {
            $this->LicenseIssueType = self::LICENSE_ISSUE_INTERNAL;
        }
        /*
         * OrganizationalMaker = 1
         * یعنی نشان وجود دارد
         * پس فعلاً مشخص نیست که مسیر نهایی از 1181 بوده یا 1177
         * بنابراین تا پایان فرآیند NULL می‌ماند.
         */
        elseif ((int)$this->OrganizationalMaker === 1) {
            $this->LicenseIssueType = null;
        }
    }

    return true;
}

    public function attributeLabels()
    {
        return [
            'StampId' => Yii::t('document', 'StampId'),
            'StampTitle' => Yii::t('document', 'Stamp Title'),
            'StampPurpose' => Yii::t('document', 'Stamp Purpose'),
            'ObsIDRef' => Yii::t('document', 'ObsIDRef'),
            'StampTypeIDRef' => Yii::t('document', 'Stamp Type'),
            'StampCode' => Yii::t('document', 'Stamp Code'),
            'RequestDate' => Yii::t('document', 'RequestDate'),
            'ProcessCodeIDRef' => Yii::t('document', 'Process Code'),
            'LastUserIDRef' => Yii::t('document', 'LastUserIDRef'),
            'DestroyUserIDRef' => Yii::t('document', 'DestroyUserIDRef'),
            'DestroyDate' => Yii::t('document', 'DestroyDate'),
            'Status' => Yii::t('document', 'Status'),
            'StampFile' => Yii::t('document', 'StampFile'),
            'LastUpdate' => Yii::t('document', 'LastUpdate'),
            'LastFlowIDRef' => Yii::t('document', 'Last Holder'),
            'NationalCode' => Yii::t('document', 'Owner NationalCode'),
            'FullName' => Yii::t('document', 'Owner FullName'),
            'OrganizationalMaker' => 'سازنده نشان سازمانی',
            'SerialNumber' => 'شناسه یکتا نشان',
            'DescriptionDestroy' => 'توضیحات',
            'FinalStampDocument' => 'تصویر نهایی نشان',
            'FinalStampFile' => 'آپلود تصویر نهایی نشان',
            'DestroyAttachment' => 'مستندات امحا',
            'DestroyAttachmentFile' => 'مستندات امحا',
            'LicenseIssueType' => 'صدور مجوز',
        ];
    }

    public function saveStamp($internalScopeIds, $externalScopeIds)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($this->save()) {
                $currentInternalScopes = $this->internalScope;
                $currentExternalScopes = $this->externalScope;

                foreach ($currentInternalScopes as $scope) {
                    if ($internalScopeIds == null || !in_array($scope->ScopeId, $internalScopeIds)) {
                        StampScopeDocument::deleteAll([
                            'StampIDRef' => $this->StampId,
                            'ScopeIDRef' => $scope->ScopeId
                        ]);
                    }
                }

                if ($internalScopeIds != null) {
                    foreach ($internalScopeIds as $internalScopeId) {
                        $internalScope = StampScopeDocument::find()->where([
                            'StampIDRef' => $this->StampId,
                            'ScopeIDRef' => $internalScopeId
                        ])->one();
                        if (!$internalScope) {
                            $internalScope = new StampScopeDocument();
                            $internalScope->StampIDRef = $this->StampId;
                            $internalScope->ScopeIDRef = $internalScopeId;
                            $internalScope->save(false);
                        }
                    }
                }

                foreach ($currentExternalScopes as $scope) {
                    if ($externalScopeIds == null || !in_array($scope->ScopeId, $externalScopeIds)) {
                        StampScopeDocument::deleteAll([
                            'StampIDRef' => $this->StampId,
                            'ScopeIDRef' => $scope->ScopeId
                        ]);
                    }
                }

                if ($externalScopeIds != null) {
                    foreach ($externalScopeIds as $externalScopeId) {
                        $externalScope = StampScopeDocument::find()->where([
                            'StampIDRef' => $this->StampId,
                            'ScopeIDRef' => $externalScopeId
                        ])->one();
                        if (!$externalScope) {
                            $externalScope = new StampScopeDocument();
                            $externalScope->StampIDRef = $this->StampId;
                            $externalScope->ScopeIDRef = $externalScopeId;
                            $externalScope->save(false);
                        }
                    }
                }

                $this->upload();
                $this->refresh();
            } else {
                $transaction->rollBack();
                return false;
            }

            $transaction->commit();
            return true;
        } catch (\Exception $exc) {
            $transaction->rollBack();
            echo $exc->getTraceAsString();
            return false;
        }
    }

    public function setVerifyStatus(): bool
    {
        $this->Status = ($this->Status == self::STATUS_APPROVED) ? self::STATUS_PENDING : self::STATUS_APPROVED;
        return $this->save(false);
    }

    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(\app\modules\account\models\User::className(), ['UserId' => 'LastUserIDRef']);
    }

    public function getObs(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Obsnew::class, ['obsId' => 'ObsIDRef']);
    }

    public function getStampType(): \yii\db\ActiveQuery
    {
        return $this->hasOne(StampTypeDocument::class, ['StampTypeId' => 'StampTypeIDRef']);
    }

    public function getProcessCode(): \yii\db\ActiveQuery
    {
        return $this->hasOne(ImsDocumentProcessCode::class, ['id' => 'ProcessCodeIDRef']);
    }

    public function getScopeStamps(): \yii\db\ActiveQuery
    {
        return $this->hasMany(StampScopeDocument::class, ['StampIDRef' => 'StampId']);
    }

    public function getInternalScope(): \yii\db\ActiveQuery
    {
        return $this->hasMany(ScopeTypeDocument::class, ['ScopeId' => 'ScopeIDRef'])
            ->viaTable('document_table_stamp_scope', ['StampIDRef' => 'StampId'])
            ->where(['ScopeType' => 'internal']);
    }

    public function getExternalScope(): \yii\db\ActiveQuery
    {
        return $this->hasMany(ScopeTypeDocument::class, ['ScopeId' => 'ScopeIDRef'])
            ->viaTable('document_table_stamp_scope', ['StampIDRef' => 'StampId'])
            ->where(['ScopeType' => 'external']);
    }

    public function getScopes(): \yii\db\ActiveQuery
    {
        return $this->hasMany(ScopeTypeDocument::class, ['ScopeIDRef' => 'ScopeId'])->via('ScopeStamps');
    }

    public function getLastFlow(): \yii\db\ActiveQuery
    {
        return $this->hasOne(StampFlowDocument::class, ['FlowId' => 'LastFlowIDRef']);
    }

    public function getHumanResource()
    {
        return $this->hasOne(\app\modules\automation\models\HumanResource::className(), ['Id' => 'HumanResourceIDRef']);
    }

    public function validateHijriDate($attribute)
    {
        if (!(jDateTime::mCheckdate($this->$attribute) > 0)) {
            $this->addError($attribute, 'Invalid Hijri Date');
        }
    }

    public function getAssistanceTitle()
    {
        $position = $this->obs;
        if (isset($position)) {
            $position = $position->allParentTitle;
            (count($position) > 0) ? array_pop($position) : null;
            return implode(" | ", $position);
        }
        return null;
    }

    public function getPosition()
    {
        return $this->hasOne(\app\modules\OBS\models\Obsnew::className(), ['ObsIDRef' => 'Position']);
    }

    public function getOrganizationalMakerLabel()
    {
        $labels = [
            1 => Yii::t('document', 'رده بالا دستی'),
            2 => Yii::t('document', 'رده'),
        ];

        if ($this->OrganizationalMaker === null || !isset($labels[$this->OrganizationalMaker])) {
            return 'Value: ' . var_export($this->OrganizationalMaker, true) . ' (مشخص نشده)';
        }

        return 'Value: ' . $this->OrganizationalMaker . ' (' . $labels[$this->OrganizationalMaker] . ')';
    }

    public static function getOrganizationalMakerList()
    {
        return [
            '1' => Yii::t('document', 'رده بالا دستی'),
            '2' => Yii::t('document', 'رده'),
        ];
    }

    public static function getOrganizationalMakerListInForm()
    {
        return [
            1 => Yii::t('document', 'وجود دارد'),
            2 => Yii::t('document', 'وجود ندارد'),
        ];
    }

    public function getOrganizationalMakerListLabel()
    {
        $organizationalMaker = self::getOrganizationalMakerList();
        return $organizationalMaker[$this->OrganizationalMaker] ?? 'نامشخص';
    }

    public function saveStampWorkflow()
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!$this->save()) {
                $transaction->rollBack();
                return false;
            }

            if ($this->StampFile instanceof \yii\web\UploadedFile || $this->FinalStampFile instanceof \yii\web\UploadedFile) {
                $this->upload();
            }

            $pid = 85;
            $ip = Yii::$app->request->userIP;

            $jdate = new \app\components\jDateTime(true, true, 'Asia/Tehran');
            $requestDateHijri = $jdate->date("Y/m/d-H:i");
            $requestDate = $jdate->date("Y-m-d");

            $startAction = Action::find()
                ->where(['ProcessIDRef' => $pid, 'isStartAction' => 1])
                ->one();

            if (!$startAction) {
                $transaction->rollBack();
                return false;
            }

            $processEntity = new ProcessEntity();
            $processEntity->ProcessEntityID = ProcessEntity::find()->max('ProcessEntityID') + 1;
            $processEntity->ProcessIDRef = $pid;
            $processEntity->LastUserIDRef = Yii::$app->session->get('UserID');
            $processEntity->LastActionIDRef = $startAction->ActionID;
            $processEntity->LastActionDateHijri = $requestDateHijri;
            $processEntity->StampIDRef = $this->StampId;
            $processEntity->Status = 1;
            $processEntity->hasChange = 1;
            $processEntity->ObjectIDRef = $this->StampId;

            if (!$processEntity->save()) {
                $transaction->rollBack();
                return false;
            }

            $actionEntity = new ActionEntity();
            $actionEntity->ProcessEntityIDRef = $processEntity->ProcessEntityID;
            $actionEntity->FromActionIDRef = null;
            $actionEntity->ActionIDRef = $startAction->ActionID;
            $actionEntity->ActionDate = $requestDate;
            $actionEntity->ActionDateHijri = $requestDateHijri;
            $actionEntity->UserIDRef = Yii::$app->session->get('UserID');
            $actionEntity->hasChange = 1;
            $actionEntity->ipAddress = $ip;
            $actionEntity->ComputerName = \app\components\Utility::findComputerNameByIp($ip);
            $actionEntity->OBSCategory3IDRef = Yii::$app->session->get('ObsPosition');

            if (!$actionEntity->save()) {
                $transaction->rollBack();
                return false;
            }

            $this->LastFlowIDRef = $actionEntity->ID;
            $this->save(false);

            $transaction->commit();
            return $processEntity->ProcessEntityID;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            return false;
        }
    }

    public function getTargetObsForAction()
    {
        if ($this->OrganizationalMaker != 1) {
            return null;
        }

        $processEntity = ProcessEntity::find()
            ->where(['ObjectIDRef' => $this->StampId, 'ProcessIDRef' => 85])
            ->one();

        if (!$processEntity) {
            return null;
        }

        $lastActionEntity = ActionEntity::find()
            ->where([
                'ProcessEntityIDRef' => $processEntity->ProcessEntityID,
                'ActionIDRef' => 1172
            ])
            ->orderBy(['ID' => SORT_DESC])
            ->one();

        if (!$lastActionEntity) {
            return null;
        }

        $requesterObsId = $lastActionEntity->OBSCategory3IDRef;

        $targetObs = Obsnew::find()
            ->where(['obsId' => $requesterObsId])
            ->select(['relatedObsIdRef'])
            ->scalar();

        return $targetObs ?: null;
    }

    public function isPositionManagerial()
    {
        if (!$this->obs) {
            return false;
        }

        $positionTypeId = $this->obs->positionTypeIdRef ?? null;
        if (!$positionTypeId) {
            return false;
        }

        $positionType = PositionType::find()
            ->where(['PositionTypeID' => $positionTypeId])
            ->one();

        return $positionType && $positionType->IsManagerial == 1;
    }

    public function softDelete()
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $jdate = new \app\components\jDateTime(true, true, 'Asia/Tehran');
            $now = $jdate->date("Y/m/d", false, false);
            $userId = Yii::$app->user->id ?? 0;

            $processEntities = \app\modules\workflow\models\ProcessEntity::find()
                ->where(['StampIDRef' => $this->StampId])
                ->all();

            foreach ($processEntities as $processEntity) {
                Yii::$app->db->createCommand()
                    ->update(
                        'workflow_table_action_entity',
                        ['isDeleted' => 1],
                        ['ProcessEntityIDRef' => $processEntity->ProcessEntityID]
                    )->execute();
            }

            Yii::$app->db->createCommand()
                ->update(
                    'workflow_table_process_entity',
                    ['isDeleted' => 1, 'status' => 0],
                    ['StampIDRef' => $this->StampId]
                )->execute();

            Yii::$app->db->createCommand()
                ->update(
                    'document_table_stamp_scope',
                    ['isDeleted' => 1],
                    ['StampIDRef' => $this->StampId]
                )->execute();

            Yii::$app->db->createCommand()
                ->update(
                    'document_table_stamp_flow',
                    ['isDeleted' => 1],
                    ['StampIDRef' => $this->StampId]
                )->execute();

            $this->isDeleted = 1;
            $this->DeletedAt = $now;
            $this->DeletedBy = $userId;
            $this->Status = 'deleted';

            if (!$this->save(false)) {
                $transaction->rollBack();
                return false;
            }

            $transaction->commit();
            return true;

        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    public function getProcessCodeIdRef()
    {
        if (empty($this->ObsIDRef)) {
            return null;
        }

        $obs = \app\modules\OBS\models\Obsnew::findOne($this->ObsIDRef);
        if (!$obs) {
            return null;
        }

        if (!empty($obs->processCodeIdRef)) {
            return $obs->processCodeIdRef;
        }

        if (!empty($obs->relatedObsIdRef)) {
            $relatedObs = \app\modules\OBS\models\Obsnew::findOne($obs->relatedObsIdRef);
            if ($relatedObs && !empty($relatedObs->processCodeIdRef)) {
                return $relatedObs->processCodeIdRef;
            }
        }

        return null;
    }

    public function getProcessCodeTitle()
    {
        $processCodeId = $this->getProcessCodeIdRef();
        if (!$processCodeId) {
            return null;
        }

        $processCode = \app\modules\document\models\ImsDocumentProcessCode::findOne($processCodeId);
        return $processCode ? $processCode->code : null;
    }

public function hasOldStampImage()
{
    return !empty($this->StampDocument);
}

public function hasFinalStampImage()
{
    return !empty($this->FinalStampDocument);
}

public function getStampDocumentUrl()
{
    if (empty($this->StampDocument)) {
        return null;
    }

    return Yii::$app->urlManager->createAbsoluteUrl([
        'document/stamp-document/logo-image',
        'id' => $this->StampId
    ]);
}

public function getFinalStampDocumentUrl()
{
    if (empty($this->FinalStampDocument)) {
        return null;
    }

    return Yii::$app->urlManager->createAbsoluteUrl([
        'document/stamp-document/final-logo-image',
        'id' => $this->StampId
    ]);
}
public function getCompany()
{
    return $this->hasOne(\app\modules\OBS\models\Person::className(), ['PersonID' => 'companyIdRef'])
        ->via('obs');
}
public function generateDestroyAttachmentFileName($file)
{
    if ($file === null) {
        return null;
    }

    $ext = strtolower($file->extension);
    return $this->StampId . '-destroy-attachment.' . $ext;
}
public function getDestroyAttachmentUploadPath()
{
    $candoDocPath = Yii::getAlias('@candoDocs');
    $mainDir = 'stamp';

    $folderNum = ceil($this->StampId / 1000);
    $subDir = str_pad((string)$folderNum, 5, '0', STR_PAD_LEFT);

    $path = $candoDocPath . DIRECTORY_SEPARATOR . $mainDir . DIRECTORY_SEPARATOR . $subDir;

    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }

    $indexFile = $path . DIRECTORY_SEPARATOR . 'index.php';
    if (!file_exists($indexFile)) {
        $handle = fopen($indexFile, 'w');
        fclose($handle);
    }

    return $path;
}
public function resolvePermit($peid)
{
    if (empty($peid)) {
        return [
            'result' => false,
            'message' => ['شناسه فرآیند ارسال نشده است.'],
        ];
    }

    /*
     * اگر نشان وجود ندارد باشد،
     * مسیر صدور داخلی است.
     */
    if ((int)$this->OrganizationalMaker === 2) {
        $permit = self::LICENSE_ISSUE_INTERNAL;
    } else {

        /*
         * بررسی عبور از رده بالادستی - اکشن 1181
         */
        $hasUpper = \app\modules\workflow\models\ActionEntity::find()
            ->where([
                'ProcessEntityIDRef' => $peid,
                'ActionIDRef' => 1181,
            ])
            ->andWhere("COALESCE(isDeleted, 0) <> 1")
            ->exists();

        /*
         * بررسی عبور از واحد داخلی - اکشن 1177
         */
        $hasInternal = \app\modules\workflow\models\ActionEntity::find()
            ->where([
                'ProcessEntityIDRef' => $peid,
                'ActionIDRef' => 1177,
            ])
            ->andWhere("COALESCE(isDeleted, 0) <> 1")
            ->exists();

        if ($hasUpper) {
            $permit = self::LICENSE_ISSUE_UPPER;
        } elseif ($hasInternal) {
            $permit = self::LICENSE_ISSUE_INTERNAL;
        } else {
            return [
                'result' => true,
                'message' => [],
            ];
        }
    }

    if ((int)$this->LicenseIssueType === (int)$permit) {
        return [
            'result' => true,
            'message' => [],
        ];
    }

    $this->LicenseIssueType = $permit;

    if (!$this->save(false, ['LicenseIssueType'])) {
        return [
            'result' => false,
            'message' => ['خطا در ذخیره صدور مجوز نشان.'],
        ];
    }

    return [
        'result' => true,
        'message' => [],
    ];
}
// متدهای مربوط به فرآیند امحاء

public function canStartRefund(): bool
{
    if ((int)$this->isDeleted === 1) {
        return false;
    }
    if ($this->Status !== self::STATUS_APPROVED) {
        return false;
    }
    return !self::hasOpenRefundProcess((int)$this->StampId);
}

public static function hasOpenRefundProcess(int $stampId): bool
{
    return ProcessEntity::find()
        ->alias('pe')
        ->innerJoin(['p' => Process::tableName()], 'p.ProcessID = pe.ProcessIDRef')
        ->where([
            'pe.ObjectIDRef'      => $stampId,
            'p.ProccessTypeIDRef' => Process::REFUND_STAMP_PROCESS_TYPE,
            'pe.isDeleted'        => 0,
        ])
        ->exists();
}

public function saveStampRefund()
{
    $this->setScenario('destroy');

    $transaction = Yii::$app->db->beginTransaction();
    try {
        $this->Status = self::STATUS_IN_REFUND;
        $this->DestroyUserIDRef = Yii::$app->session->get('UserID');

        if (!$this->save()) {
            $transaction->rollBack();
            return false;
        }

        // آپلود مستندات امحا (DocumentTraitPro + configDoc)
        if ($this->DestroyAttachmentFile instanceof \yii\web\UploadedFile) {
            $this->upload();
        }

            $pid = 87; // فرآیند امحاء نشان سازمانی
            $ip = Yii::$app->request->userIP;
            $now = new \DateTime('now', new \DateTimeZone('Asia/Tehran'));
            $requestDate = $now->format('Y-m-d');
            $jdate = new jDateTime(false, true, 'Asia/Tehran');
            $requestDateHijri = $jdate->date('Y/m/d-Hi', $now->getTimestamp());

        $startAction = Action::find()
            ->where(['ProcessIDRef' => $pid, 'isStartAction' => 1])
            ->one();

        if (!$startAction) {
            $transaction->rollBack();
            return false;
        }

        $processEntity = new ProcessEntity();
        $processEntity->ProcessEntityID = ProcessEntity::find()->max('ProcessEntityID') + 1;
        $processEntity->ProcessIDRef = $pid;
        $processEntity->LastUserIDRef = Yii::$app->session->get('UserID');
        $processEntity->LastActionIDRef = $startAction->ActionID; // 1195
        $processEntity->LastActionDateHijri = $requestDateHijri;
        $processEntity->ObjectIDRef = $this->StampId;
        // اگر فیلد StampIDRef هم داری:
        if ($processEntity->hasAttribute('StampIDRef')) {
            $processEntity->StampIDRef = $this->StampId;
        }
        $processEntity->Status = 1;
        $processEntity->hasChange = 1;
        $processEntity->isDeleted = 0;

        if (!$processEntity->save()) {
            $transaction->rollBack();
            return false;
        }

        $actionEntity = new ActionEntity();
        $actionEntity->ProcessEntityIDRef = $processEntity->ProcessEntityID;
        $actionEntity->FromActionIDRef = null;
        $actionEntity->ActionIDRef = $startAction->ActionID;
        $actionEntity->ActionDate = $requestDate;
        $actionEntity->ActionDateHijri = $requestDateHijri;
        $actionEntity->UserIDRef = Yii::$app->session->get('UserID');
        $actionEntity->OBSCategory3IDRef = Yii::$app->session->get('ObsPosition');
        $actionEntity->ipAddress = $ip;
        $actionEntity->ComputerName = \app\components\Utility::findComputerNameByIp($ip);
        $actionEntity->hasChange = 1;

        if (!$actionEntity->save()) {
            $transaction->rollBack();
            return false;
        }

        $transaction->commit();
        return (int) $processEntity->ProcessEntityID;
    } catch (\Exception $e) {
        $transaction->rollBack();
        Yii::error($e->getMessage(), __METHOD__);
        return false;
    }
}
/**
 * دریافت ProcessEntity مربوط به امحا (ProcessType=20)
 */
public function getRefundProcessEntity()
{
    return ProcessEntity::find()
        ->alias('pe')
        ->innerJoin(['p' => Process::tableName()], 'p.ProcessID = pe.ProcessIDRef')
        ->where([
            'pe.ObjectIDRef' => $this->StampId,
            'p.ProccessTypeIDRef' => Process::REFUND_STAMP_PROCESS_TYPE,
            'pe.isDeleted' => 0,
        ])
        ->one();
}

}