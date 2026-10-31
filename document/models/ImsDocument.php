<?php

namespace app\modules\document\models;

use app\components\Utility;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "tblIMSDocument".
 * @author <mail:mortezakml@gmail.com & Tel:09366263397>
 *
 * @property id
 * @property type
 * @property title
 * @property code
 * @property autoCode
 * @property status
 * @property revisionNumber
 * @property preparationDate
 * @property revisionDate
 * @property effectiveDate
 * @property expireDate
 * @property preparatorUnit
 * @property mainUnitObsIdRef
 * @property processCode
 * @property reportfile
 * @property nativefile
 * @property companyIdRef
 * @property parentID
 * @property mainDocumentID
 * @property hasChange
 * @property private
 * @property isDeleted
 * @property createdBy
 * @property createdAt
 * @property updatedBy
 * @property updatedAt
 */
class ImsDocument extends \yii\db\ActiveRecord {

    use \app\components\DocumentTraitPro;

    public function getConfigDoc() {
        return [
            'folder' => 'imsNew',
            'files' => [
                'reportObject' => 'repo',
                'nativeObject' => 'nati',
            ],
        ];
    }

    public $reportObject;
    public $nativeObject;
    public $relatedIMSDocument;
    public $relatedOBS;
    public $relatedKeyword;
    public $clause;
    public $standard;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'cando_table_ims_document_new';
    }

    public static function getDb() {
        return \app\components\Utility::centralDb();
    }

    public function typeArray() {
        return [1 => Yii::t('document', 'Instruction')
            , 2 => Yii::t('document', 'Procedure')
            , 3 => Yii::t('document', 'List/Table/Chart')
            , 4 => Yii::t('document', 'Form')
            , 5 => Yii::t('document', 'Table')
            , 6 => Yii::t('document', 'Policy')
            , 7 => Yii::t('document', 'Manual')
            , 8 => Yii::t('document', 'Objectives')
            , 9 => Yii::t('document', 'Process')
            , 10 => Yii::t('document', 'Chart')
            , 11 => Yii::t('document', 'Responsibilites')
            , 12 => Yii::t('document', 'Document')
            , 13 => Yii::t('document', 'Communicated')
            , 14 => Yii::t('document', 'External Organization')
            , 15 => Yii::t('document', 'Regulation')
        ];
    }

    public function searchTypeArray() {
        return [1 => 'Instruction'
            , 2 => 'Procedure'
            , 3 => 'List/Table/Chart'
            , 4 => 'Form'
            , 5 => 'Table'
            , 6 => 'Policy'
            , 7 => 'Manual'
            , 8 => 'Objectives'
            , 9 => 'Process'
            , 10 => 'Chart'
            , 11 => 'Responsibilites'
            , 12 => 'Document'
            , 13 => 'Communicated'
            , 14 => 'External Organization'
            , 15 => 'Regulation'
        ];
    }

    public function TypeIdsArray() {
        return [1
            , 2
            , 3
            , 4
            , 5
            , 6
            , 7
            , 8
            , 9
            , 10
            , 11
            , 12
            , 13
            , 14
            , 15
        ];
    }

    public function statusArray() {
        return [3 => Yii::t('document', 'UnKnown'), 2 => Yii::t('document', 'Compilation'), 1 => Yii::t('document', 'Valid'), 0 => Yii::t('document', 'Expire')];
    }

    public static function primaryKey() {
        return ['id'];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['preparatorUnit', 'reportfile', 'nativefile', 'code', 'title', 'autoCode'], 'string'],
            [['revisionNumber', 'companyIdRef', 'type', 'status', 'parentID', 'mainDocumentID', 'mainUnitObsIdRef'], 'integer'],
            ['companyIdRef', 'validateCompany'],
            ['mainDocumentID', 'validateMainDocument'],
            ['status', 'statusValidation', 'on' => ['update']],
            [['preparationDate', 'revisionDate', 'effectiveDate'], 'validateDate', 'skipOnEmpty' => false],
//            [['preparationDate', 'revisionDate', 'effectiveDate'], 'date'],
//            [['title'], 'required', 'on' => ['create']],
            [['companyIdRef', 'revisionNumber', 'type', 'title', 'processCode', 'mainUnitObsIdRef'], 'required'],
//            [['code', 'type', 'revisionNumber'], 'unique', 'targetAttribute' => ['code', 'type', 'revisionNumber']],
            ['reportObject', 'file', 'extensions' => 'pdf', 'maxSize' => 20971520],
            [['nativeObject'], 'file', 'extensions' => 'xls, xlsx, xlsb, zip, rar, doc, docx, ppt, pptx, pps, ppsx,pdf,png, jpg', 'maxSize' => 20971520],
            [['reportObject', 'nativeObject'], 'validateAtLeastOneRequired', 'on' => ['revision', 'update'], 'skipOnEmpty' => false],
//            [['reportObject'], 'required', 'when' => function ($model) {
//                    return $model->reportfile == null;
//                }, 'enableClientValidation' => false, 'on' => ['revision', 'update']
//            ],
        ];
    }

    public static function find() {
        return new ImsDocumentQuery(get_called_class());
    }

    public function scenarios() {
        $scenario = parent::scenarios();
        $scenario['create'] = ['status', 'relatedOBS', 'relatedKeyword', 'relatedIMSDocument', 'type', 'title', 'code', 'preparationDate', 'revisionDate', 'effectiveDate', 'revisionNumber', 'processCode', 'reportfile', 'nativefile', 'companyIdRef', 'reportObject', 'nativeObject', 'parentID', 'mainDocumentID', 'autoCode', 'clause', 'mainUnitObsIdRef'];
        $scenario['revision'] = ['status', 'relatedOBS', 'relatedKeyword', 'relatedIMSDocument', 'type', 'title', 'code', 'preparationDate', 'revisionDate', 'effectiveDate', 'revisionNumber', 'processCode', 'reportfile', 'nativefile', 'companyIdRef', 'reportObject', 'nativeObject', 'parentID', 'mainDocumentID', 'autoCode', 'clause', 'mainUnitObsIdRef'];
        $scenario['update'] = ['status', 'relatedOBS', 'relatedKeyword', 'relatedIMSDocument', 'type', 'title', 'code', 'preparationDate', 'revisionDate', 'effectiveDate', 'expireDate', 'revisionNumber', 'processCode', 'reportfile', 'nativefile', 'companyIdRef', 'reportObject', 'nativeObject', 'mainDocumentID', 'autoCode', 'clause', 'mainUnitObsIdRef'];
        $scenario['autoCode'] = ['autoCode'];
        $scenario['change-private'] = ['private'];
        return $scenario;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('document', 'ID'),
            'type' => Yii::t('document', 'IMS Type'),
            'title' => Yii::t('document', 'Title'),
            'code' => Yii::t('document', 'Old Code'),
            'imsType' => Yii::t('document', 'IMS Type'),
            'autoCode' => Yii::t('document', 'Automatic Code'),
            'status' => Yii::t('document', 'Status'),
            'mainDocumentID' => Yii::t('document', 'Main Document'),
            'revisionNumber' => Yii::t('document', 'Revision Number'),
            'preparationDate' => Yii::t('document', 'Preparation Date'),
            'revisionDate' => Yii::t('document', 'Revision Date'),
            'effectiveDate' => Yii::t('document', 'Effective Date'),
            'preparatorUnit' => Yii::t('document', 'Preparator Unit'),
            'mainUnitObsIdRef' => Yii::t('document', 'Main Unit'),
            'processCode' => Yii::t('document', 'Process Owner'),
            'reportfile' => Yii::t('document', 'Report File'),
            'nativefile' => Yii::t('document', 'Native File'),
            'companyIdRef' => Yii::t('document', 'Company'),
            'referenceCompany' => Yii::t('document', 'Company'),
            'companyTitle' => Yii::t('document', 'Reference Company'),
            'clause' => Yii::t('document', 'Clause'),
            'standard' => Yii::t('document', 'Standard'),
            'hasChange' => Yii::t('document', 'hasChange'),
            'isDeleted' => Yii::t('document', 'isDeleted'),
            'createdBy' => Yii::t('document', 'createdBy'),
            'createdAt' => Yii::t('document', 'createdAt'),
            'updatedBy' => Yii::t('document', 'updatedBy'),
            'updatedAt' => Yii::t('document', 'updatedAt'),
            //other field
            'imscode' => Yii::t('document', 'Code'),
            'imstitle' => Yii::t('document', 'Title'),
            'reportObject' => Yii::t('document', 'Report File'),
            'nativeObject' => Yii::t('document', 'Native File'),
            'relatedOBS' => Yii::t('document', 'Related OBS'),
            'keywordIdList' => Yii::t('document', 'keyword List'),
            'relatedKeyword' => Yii::t('document', 'Related Keyword'),
            'relatedIMSDocument' => Yii::t('document', 'Related IMS Document'),
            'preparationHijriDate' => Yii::t('document', 'Preparation HijriDate'),
        ];
    }

    public function beforeValidate() {
        if ($this->scenario == 'create' || $this->scenario == 'revision') {
            $this->private = $this->type == 10 ? 1 : 0;
        }
        if ($this->scenario == 'update' && $this->getOldAttribute('status') != $this->status) {
            if ($this->status == 0) {
                $jdate = new \app\components\jDateTime(true, true, 'Asia/Tehran');
                $currentDate = $jdate->date("Y/m/d", false, false);
                $this->expireDate = $currentDate;
            } else {
                $this->expireDate = null;
            }
        }
        return parent::beforeValidate($this);
    }

    public function setRevisionNumber($scenario) {

        if ($scenario == 'create') {

            $maxRevision = ImsDocument::find()
                    ->where(['parentID' => $this->parentID])
                    ->andWhere("isDeleted is null or isDeleted<>1")
                    ->max('revisionNumber');
            if (!is_null($maxRevision)) {
                $this->revisionNumber = $maxRevision + 1;
            } else {
                $this->revisionNumber = 0;
            }
        }
    }

    public function setAutoCode($cid, $pcid, $tid, $id = 0) {
        if ($id > 0) {
            $lastRevModel = ImsDocument::find()
                    ->where(['id' => $id])
                    ->andWhere("isDeleted is null or isDeleted<>1")
                    ->one();

            $revisionNumber = $lastRevModel->revisionNumber + 1;
            $autoCode = explode('-', $lastRevModel->autoCode);
            array_pop($autoCode);
            $autoCode = implode('-', $autoCode);
        } else {
            $imsDocumentTable = ImsDocument::tableName();
            $companyArray = \app\modules\OBS\models\Company::find()
                    ->where(["companyIdRef" => $cid])
                    ->one();

            $autoCode = $companyArray->shortTitle;
            $activeQuery = static::find()
                    ->andWhere("$imsDocumentTable.companyIdRef = $cid")
                    ->andWhere("isDeleted is null or isDeleted<>1");
            if (!isset($pcid)) {
                $pcid = 1; // PS is default
            }
            $activeQuery->andWhere(["$imsDocumentTable.processCode" => $pcid]);

            $code = ImsDocumentProcessCode::find()->where(['id' => $pcid])->one()->code;
            $autoCode = $autoCode . '-' . $code;
            if (!isset($tid)) {
                $tid = 15; // RG is default
            }
            $activeQuery->andWhere(["$imsDocumentTable.type" => $tid]);

            $type = ImsDocumentType::find()->where(['ID' => $tid])->one()->Code;
            $autoCode = $autoCode . '-' . $type;

            $count = $activeQuery->select(['rev' => new \yii\db\Expression("MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(autoCode, '-', 4), '-', -1) AS INT))")])->asArray()->one();
            $count = (int) $count['rev'] + 1;
            $revisionNumber = 0;

            $len = strlen(strval($count));
            if ($len == 1) {
                $count = '000' . strval($count);
            } elseif ($len == 2) {
                $count = '00' . strval($count);
            } elseif ($len == 3) {
                $count = '0' . strval($count);
            }
            $autoCode = $autoCode . '-' . $count;
        }
        $len = strlen(strval($revisionNumber));
        if ($len == 1) {
            $revisionNumber = '0' . strval($revisionNumber);
        }
        $autoCode = $autoCode . '-' . $revisionNumber;

        return $autoCode;
    }

    public function setAutoCodeOld($cid, $pcid, $tid, $pid = 0) {
        $imsDocumentTable = ImsDocument::tableName();
        $companyArray = \app\modules\OBS\models\Company::find()
                ->where(["companyIdRef" => $cid])
                ->one();

        $autoCode = $companyArray->shortTitle;
        $activeQuery = static::find()
                ->andWhere("$imsDocumentTable.companyIdRef = $cid");
        if (!isset($pcid)) {
            $pcid = 1; // PS is default
        }
        $activeQuery->andWhere(["$imsDocumentTable.processCode" => $pcid]);

        $code = ImsDocumentProcessCode::find()->where(['id' => $pcid])->one()->code;
        $autoCode = $autoCode . '-' . $code;
        if (!isset($tid)) {
            $tid = 15; // RG is default
        }
        $activeQuery->andWhere(["$imsDocumentTable.type" => $tid]);
        $type = ImsDocumentType::find()->where(['ID' => $tid])->one()->Code;
        $autoCode = $autoCode . '-' . $type;

        $count = $activeQuery->select('parentID')->distinct()->count();
        if ($pid > 0) {
            $lastRevModel = ImsDocument::find()
                            ->where(['parentID' => $pid])
                            ->andWhere("isDeleted is null or isDeleted<>1")
                            ->orderBy(['revisionNumber' => SORT_DESC])->one();

            if ($lastRevModel->id == $lastRevModel->parentID) {
                $revisionNumber = $lastRevModel->revisionNumber;
            } else {
                $revisionNumber = $lastRevModel->revisionNumber + 1;
            }
        } else {
            $revisionNumber = 0;
            $count += 1;
        }

//        else{
//            $parentAutoCode = ImsDocument::find()
//                ->where(['parentID' => $pid])
//                ->andWhere("isDeleted is null or isDeleted<>1")
//                ->one()->autoCode;
//            $parentAutoCode = explode('-',$parentAutoCode);
//            $count = $parentAutoCode[3];
//
//        }
        $len = strlen(strval($count));
        if ($len == 1) {
            $count = '000' . strval($count);
        } elseif ($len == 2) {
            $count = '00' . strval($count);
        } elseif ($len == 3) {
            $count = '0' . strval($count);
        }
        $len = strlen(strval($revisionNumber));
        if ($len == 1) {
            $revisionNumber = '0' . strval($revisionNumber);
        }
        $autoCode = $autoCode . '-' . $count . '-' . $revisionNumber;

        return $autoCode;
    }

    public function validateCompany($attribute) {
        $imsDocument = ImsDocument::find()
                ->where(['id' => $this->id])
                ->andWhere("isDeleted is null or isDeleted<>1")
                ->one();
//        $imsDocument = ImsDocument::find()
//                ->andWhere(["code" => $this->code])
//                ->andWhere(["type" => $this->type])
//                ->one();
        if (isset($imsDocument)) {
            if ($this->companyIdRef != $imsDocument->companyIdRef) {
                $companyId = Yii::$app->session->get('CompanyID');
                if ($this->companyIdRef != 248) {  // Can Edit NSR Company For Other Company's
                    $this->addError($attribute, 'Your Company is not equal to revision ims Document');
                }
            }
        }
    }

    public function validateAtLeastOneRequired($attribute) {
        if($this->scenario == 'update' && $this->status !== 1) {
            return;
        }
        if (empty($this->reportfile) && empty($this->nativefile)) {
            $this->addError($attribute, 'مدرک یا مدرک قابل ویرایش یکی باید وارد شود');
        }
    }

    public function validateMainDocument($attribute) {
        $exists = ImsDocument::find()
                ->where(['mainDocumentID' => $this->id])
                ->andWhere("isDeleted is null or isDeleted<>1")
                ->exists();

        if ($exists && !empty($this->mainDocumentID)) {
            $this->addError($attribute, 'This Document itself is the Main Document');
        }
    }

    public function statusValidation($attribute) {
        if ($this->$attribute == 3) {
            $this->addError($attribute, 'Ims Document can not change to UnKnown Status');
        }
        if ($this->getOldAttribute($attribute) == 1 && $this->$attribute == 2) {
            $this->addError($attribute, 'Valid Ims Document can not change to Compilation');
        }
    }

    public function validateDate($attribute) {
        if (!empty($this->$attribute)) {
            $format = 'Y-m-d';
            $date = \DateTime::createFromFormat($format, $this->$attribute);
            if (!($date && $date->format($format) == $this->$attribute)) {
                $this->addError($attribute, "The format of $attribute is invalid.");
            }
        }
    }

    public function getCompany() {
        return $this->hasOne(\app\modules\OBS\models\Person::className(), ['PersonID' => 'companyIdRef']);
    }

    public function getMainDocument() {
        return $this->hasOne(ImsDocument::className(), ['id' => 'mainDocumentID']);
    }

    public function getSubDocuments() {
        return $this->hasMany(ImsDocument::className(), ['mainDocumentID' => 'id'])->where(['<>', 'COALESCE(isDeleted,0)', 1]);
    }

    public function getObs() {
        return $this->hasOne(\app\modules\OBS\models\Obsnew::className(), ['obsId' => 'preparatorUnit']);
    }
    
    public function getMainUnit() {
        return $this->hasOne(\app\modules\OBS\models\Obsnew::className(), ['obsId' => 'mainUnitObsIdRef']);
    }

    public function getImsType() {
        return $this->hasOne(ImsDocumentType::className(), ['ID' => 'type']);
    }

    public function getImsTitle() {
        return $this->hasOne(ImsDocumentTitleList::className(), ['id' => 'title']);
    }

    public function getImsCode() {
        return $this->hasOne(ImsDocumentCodeList::className(), ['id' => 'code']);
    }

    public function getProcessCode() {
        return $this->hasOne(ImsDocumentProcessCode::className(), ['id' => 'processCode']);
    }

    public function getImsCreatedBy() {
        return $this->hasOne(\app\modules\account\models\User::className(), ['UserID' => 'createdBy']);
    }

    public function getImsUpdatedBy() {
        return $this->hasOne(\app\modules\account\models\User::className(), ['UserID' => 'updatedBy']);
    }

    public function getClauses() {
        return $this->hasMany(Clause::class, ['id' => 'ClauseIdRef'])
                        ->viaTable('cando_table_ims_document_clause', ['DocumentIdRef' => 'id']);
    }

    public function saveRelatedOBS() {
        if (!empty($this->relatedOBS)) {
            foreach ($this->relatedOBS as $key => $value) {
                $imsProcessCodeRelatedModel = \app\modules\document\models\ImsDocumentProcessCodeRelated::find()->andWhere(["imsIdRef" => $this->id])->andWhere(["processCodeIdRef" => $value])->one();
                if (!isset($imsProcessCodeRelatedModel)) {
                    $imsProcessCodeRelatedModel = new \app\modules\document\models\ImsDocumentProcessCodeRelated();
                    $imsProcessCodeRelatedModel->id = \app\modules\document\models\ImsDocumentProcessCodeRelated::find()->max('id') + 1;
                    $imsProcessCodeRelatedModel->imsIdRef = $this->id;
                    $imsProcessCodeRelatedModel->processCodeIdRef = $value;
                    $imsProcessCodeRelatedModel->save();
                }
            }
        }
        \app\modules\document\models\ImsDocumentProcessCodeRelated::deleteAll(["AND", "imsIdRef=$this->id", ["NOT IN", "processCodeIdRef", $this->relatedOBS]]);
    }

    public function saveClause() {
        if ($this->mainDocumentID > 0) {
            //if document is sub read from maindocument clause
            $this->clause = ArrayHelper::getColumn(self::find()->where(['id' => $this->mainDocumentID])->one()->clauses, 'id');
        }
        $newClauses = is_array($this->clause) ? $this->clause : [];
        $oldClauses = ArrayHelper::getColumn($this->clauses, 'id');

        $clauseToAdd = array_diff($newClauses, $oldClauses);
        $clauseToDelete = array_diff($oldClauses, $newClauses);

        if ($clauseToDelete) {
            ImsDocumentClause::deleteAll([
                'DocumentIdRef' => $this->id,
                'ClauseIdRef' => $clauseToDelete
            ]);
        }

        if ($clauseToAdd) {
            foreach ($clauseToAdd as $clause) {
                $documentClause = new ImsDocumentClause();
                $documentClause->DocumentIdRef = $this->id;
                $documentClause->ClauseIdRef = $clause;
                $documentClause->save();
            }
        }

        //update sub documents clause
        $subDocuments = $this->subDocuments;
        foreach ($subDocuments as $document) {
            $oldClauses = ArrayHelper::getColumn($document->clauses, 'id');

            $clauseToAdd = array_diff($newClauses, $oldClauses);
            $clauseToDelete = array_diff($oldClauses, $newClauses);

            if ($clauseToDelete) {
                ImsDocumentClause::deleteAll([
                    'DocumentIdRef' => $document->id,
                    'ClauseIdRef' => $clauseToDelete
                ]);
            }

            if ($clauseToAdd) {
                foreach ($clauseToAdd as $clause) {
                    $documentClause = new ImsDocumentClause();
                    $documentClause->DocumentIdRef = $document->id;
                    $documentClause->ClauseIdRef = $clause;
                    $documentClause->save();
                }
            }
        }
    }

}
