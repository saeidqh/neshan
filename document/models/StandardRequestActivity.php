<?php

namespace app\modules\document\models;

use Yii;
use app\components\jDateTime;

/**
 * This is the model class for table "dbo.tblStandardRequestActivity".
 *
 * @property integer $id
 * @property integer $standardRequestIdRef
 * @property integer $clauseActivityIdRef
 * @property integer $activityDocumentIdRef
 * @property string $radehDescription
 * @property string $radehStatus
 * @property string $radehUpdateDate
 * @property integer $radehUserIdref
 * @property string $holdingDescription
 * @property string $holdingStatus
 * @property string $holdingUpdateDate
 * @property integer $holdingUserIdRef
 * @property string $color
 * 
 */
class StandardRequestActivity extends \yii\db\ActiveRecord {

    public $file;
    public $fileName;
    public $imsDocumentIdRef;
    public $standardArray;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        if (Yii::$app->params['dmbs'] == 'mysql') {
            return 'cando_table_ims_standard_request_activity';
        } else {
            return 'dbo.tblStandardRequestActivity';
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
            [['standardRequestIdRef', 'clauseActivityIdRef', 'radehStatus', 'holdingStatus'], 'required'],
            [['standardRequestIdRef', 'clauseActivityIdRef', 'activityDocumentIdRef', 'imsDocumentIdRef', 'radehUserIdRef', 'holdingUserIdRef'], 'integer'],
            [['radehDescription', 'radehStatus', 'radehUpdateDate', 'holdingDescription', 'holdingStatus', 'holdingUpdateDate', 'fileName', 'color'], 'string'],
            [['fileName'], 'string', 'max' => 255],
            ['file', 'file', 'extensions' => $this->getDynamicExtensions(), 'maxSize' => $this->getDynamicMaxSize()]
        ];
    }

    public function scenarios() {
        $scenario = parent::scenarios();
        return $scenario;
    }

    public function beforeValidate() {
        return parent::beforeValidate($this);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('document', 'ID'),
            'file' => Yii::t('document', 'File'),
            'radehDescription' => Yii::t('document', 'Radeh Description'),
            'radehStatus' => Yii::t('document', 'Radeh Status'),
            'radehUpdateDate' => Yii::t('document', 'Radeh Update Date'),
            'holdingDate' => Yii::t('document', 'Holding Date'),
            'holdingDescription' => Yii::t('document', 'Holding Description'),
            'holdingStatus' => Yii::t('document', 'Holding Status'),
            'holdingUpdateDate' => Yii::t('document', 'Holding Update Date'),
        ];
    }

    public static function getRadehStatusList() {
        $sequerityList = [
            'checking' => 'در حال بررسی',
            'verify' => 'تایید'
        ];
        return $sequerityList;
    }

    public static function getRadehStatus($status) {
        switch ($status) {
            case 'checking':
                return 'در حال بررسی';
            case 'verify':
                return 'تایید';
            default:
                return 'invalid Type';
        }
    }

    public static function getHoldingStatusList() {
        $sequerityList = [
            'checking' => 'در حال بررسی',
            'back' => 'برگشت به رده',
            'verify' => 'تایید'
        ];
        return $sequerityList;
    }

    public static function getHoldingStatus($status) {
        switch ($status) {
            case 'checking':
                return 'در حال بررسی';
            case 'back':
                return 'برگشت به رده';
            case 'verify':
                return 'تایید';
            default:
                return 'invalid Type';
        }
    }

    public function getDynamicMaxSize() {
        $clauseActivity = $this->clauseActivity;
        if ($clauseActivity->maxSize > 0) {
            return $clauseActivity->maxSize * 1024;
        }
        return 50 * 1024;
    }

    public function getDynamicExtensions() {
        $clauseActivity = $this->clauseActivity;
        if (!empty($clauseActivity->extention)) {
            return explode(',', $clauseActivity->extention);
        }
        return ['xls', 'xlsx', 'xlsb', 'doc', 'docx', 'pdf', 'png', 'jpg', 'jpeg', 'zip', 'rar'];
    }

    public function getStandardRequest() {
        return $this->hasOne(\app\modules\document\models\StandardRequest::className(), ['id' => 'standardRequestIdRef']);
    }

    public function getClauseActivity() {
        return $this->hasOne(\app\modules\document\models\ClauseActivity::className(), ['id' => 'clauseActivityIdRef']);
    }

    public function getActivityDocument() {
        return $this->hasOne(\app\modules\document\models\CompanyActivityDocument::className(), ['id' => 'activityDocumentIdRef']);
    }

    public function getRadehUser() {
        return $this->hasOne(\app\modules\account\models\User::className(), ['UserID' => 'radehUserIdref']);
    }

    public function getHoldingUser() {
        return $this->hasOne(\app\modules\account\models\User::className(), ['UserID' => 'holdingUserIdref']);
    }

    public function loadDocument() {
        $document = $this->activityDocument;
        if (!isset($document)) {
            $document = CompanyActivityDocument::find()->where(['companyIdRef' => $this->standardRequest->companyIdRef, 'activityIdRef' => $this->clauseActivityIdRef, 'isActive' => 1])->one();
        }
        if (isset($document)) {
            $this->fileName = $document->fileName;
            $this->imsDocumentIdRef = $document->imsDocumentIdRef;
            $this->activityDocumentIdRef = $document->id;
        }
    }

    public function saveRequestActivity() {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ((empty($this->fileName) && empty($this->imsDocumentIdRef))) {
                // || (!empty($this->activityDocumentIdRef) && !($this->file instanceof \yii\web\UploadedFile) && $this->imsDocumentIdRef == $this->activityDocument->imsDocumentIdRef)
                $res = $this->save();
                if ($res) {
                    $transaction->commit();
                    return true;
                }
            }

            $standardRequest = $this->standardRequest;
            if (empty($this->activityDocumentIdRef)) {
                $document = new CompanyActivityDocument();
                $document->companyIdRef = $standardRequest->companyIdRef;
                $document->activityIdRef = $this->clauseActivityIdRef;
            } else {
                $document = $this->activityDocument;
            }
            $document->imsDocumentIdRef = $this->imsDocumentIdRef;
            $file = $this->file;
            if ($file instanceof \yii\web\UploadedFile) {
                $document->fileName = $file->name;
            }
            $res = $document->save();
            if (!$res) {
                $transaction->rollBack();
                return false;
            }
            // $file = $this->file;
            if ($file instanceof \yii\web\UploadedFile) {
                $document->hardDeleteFile();
                // $document->fileName = $file->name;
                // $document->save(false);
                $document->file = $file;
                $document->upload();
            }

            $this->activityDocumentIdRef = $document->id;
            $res = $this->save(false);
            if (!$res) {
                $transaction->rollBack();
                return false;
            }

            $existStandardRequest = StandardRequest::find()->where(['companyIdRef' => $standardRequest->companyIdRef, 'enableSimilarActivity' => 0, 'isLock' => 0])->exists();
            $clauseActivity = $this->clauseActivity;
            if (empty($clauseActivity->similarActivity) || $existStandardRequest) {
                $similarActivities = [$this->clauseActivityIdRef];
            } else {
                $similarActivities = explode(',', $clauseActivity->similarActivity);
                array_push($similarActivities, $this->clauseActivityIdRef);
            }
            foreach ($similarActivities as $similarActivity) {
                $companyActivityDocument = CompanyActivityDocument::find()->where(['companyIdRef' => $standardRequest->companyIdRef, 'activityIdRef' => $similarActivity, 'isActive' => 1])->one();
                if (!isset($companyActivityDocument)) {
                    $companyActivityDocument = new CompanyActivityDocument();
                    $companyActivityDocument->activityIdRef = $similarActivity;
                    $companyActivityDocument->companyIdRef = $standardRequest->companyIdRef;
                }
                $companyActivityDocument->imsDocumentIdRef = $this->imsDocumentIdRef;
                $res = $companyActivityDocument->save();
                if (!$res) {
                    $transaction->rollBack();
                    return false;
                }
                if (($file instanceof \yii\web\UploadedFile) && $similarActivity != $this->clauseActivityIdRef) {
                    $companyActivityDocument->fileName = $file->name;
                    $companyActivityDocument->save(false);
                    $companyActivityDocument->file = $file;
                    $companyActivityDocument->simpleCopy($document->fullPath);
                }
                $requestActivitySubquery = StandardRequest::find()->select('id')->where(['companyIdRef' => $standardRequest->companyIdRef, 'isLock' => 0]);
                StandardRequestActivity::updateAll(
                        ['activityDocumentIdRef' => $companyActivityDocument->id],
                        ['clauseActivityIdRef' => $similarActivity, 'standardRequestIdRef' => $requestActivitySubquery]
                );
            }

            return true;
        } catch (Exception $exc) {
            $transaction->rollBack();
            echo $exc->getTraceAsString();
            return false;
        }
    }
}
