<?php

namespace app\modules\document\models;

use Yii;
use yii\base\Model;

/**
 * CertificateActivityBaseTypeForm is the model behind the contact form.
 */
class CertificateActivityBaseTypeForm extends Model
{
    public $base;
    public $authorizedWork;
    public $authorizedCapacity;
    public $approvedContract;
    public $isNewRecord;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['base', 'authorizedWork', 'authorizedCapacity', 'approvedContract'], 'required'],
            [['base', 'authorizedWork', 'authorizedCapacity', 'approvedContract'], 'integer'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'base' => \Yii::t("document", 'Base'),
            'authorizedWork' => \Yii::t("document", 'Authorized Work'),
            'authorizedCapacity' => \Yii::t("document", 'Authorized Capacity'),
            'approvedContract' => \Yii::t("document", 'Approved Contract Point'),
        ];
    }

    public function loadCertificateDocumentActivity($adid) {
        $activityTableName = \app\modules\document\models\CertificateActivity::tableName();
        $activityDocumentTableName = \app\modules\document\models\CertificateDocumentActivity::tableName();
        $activityDocumentBaseTypeTableName = \app\modules\document\models\CertificateDocumentBaseType::tableName();
        $documentTableName = \app\modules\document\models\CertificateDocument::tableName();
        $baseQuery = CertificateDocumentBaseType::find()
            ->addSelect(["$activityDocumentBaseTypeTableName.value"])->where([
                "$activityDocumentBaseTypeTableName.activityDocumentIdRef" => new \yii\db\Expression($activityDocumentTableName . ".id"),
                "$activityDocumentBaseTypeTableName.baseTypeIdRef" => CertificateDocumentBaseType::BASE_ID
            ])->createCommand()->rawSql;

        $authorizedWorkQuery = CertificateDocumentBaseType::find()
            ->addSelect(["$activityDocumentBaseTypeTableName.value"])->where([
                "$activityDocumentBaseTypeTableName.activityDocumentIdRef" => new \yii\db\Expression($activityDocumentTableName . ".id"),
                "$activityDocumentBaseTypeTableName.baseTypeIdRef" => CertificateDocumentBaseType::AUTHORIZED_WORK_ID
            ])->createCommand()->rawSql;

        $authorizedCapacityQuery = CertificateDocumentBaseType::find()
            ->addSelect(["$activityDocumentBaseTypeTableName.value"])->where([
                "$activityDocumentBaseTypeTableName.activityDocumentIdRef" => new \yii\db\Expression($activityDocumentTableName . ".id"),
                "$activityDocumentBaseTypeTableName.baseTypeIdRef" => CertificateDocumentBaseType::AUTHORIZED_CAPACITY_ID
            ])->createCommand()->rawSql;

        $approvedContractQuery = CertificateDocumentBaseType::find()
            ->addSelect(["$activityDocumentBaseTypeTableName.value"])->where([
                "$activityDocumentBaseTypeTableName.activityDocumentIdRef" => new \yii\db\Expression($activityDocumentTableName . ".id"),
                "$activityDocumentBaseTypeTableName.baseTypeIdRef" => CertificateDocumentBaseType::APPROVED_CONTRACT_ID
            ])->createCommand()->rawSql;

        $activeQuery = CertificateDocument::find()
            ->innerJoin($activityDocumentTableName, "$activityDocumentTableName.certificateIdRef = $documentTableName.certificateId")
            ->leftJoin($activityTableName, "$activityTableName.backgroundActivityTypeId = $activityDocumentTableName.activityIdRef")
            ->addSelect([
                'base' => '(' . $baseQuery . ')',
                'authorizedWork' => '(' . $authorizedWorkQuery . ')',
                'authorizedCapacity' => '(' . $authorizedCapacityQuery . ')',
                'approvedContract' => '(' . $approvedContractQuery . ')',
            ])->where(["$activityDocumentTableName.id" => $adid])->one();

        if(!empty($activeQuery)){
            $this->base = $activeQuery->base;
            $this->authorizedWork = $activeQuery->authorizedWork;
            $this->authorizedCapacity = $activeQuery->authorizedCapacity;
            $this->approvedContract = $activeQuery->approvedContract;
        }
        if(empty($this->base) and empty($this->authorizedWork) and empty($this->authorizedCapacity) and empty($this->approvedContract) ){
            $this->isNewRecord =true;
        }else{
            $this->isNewRecord =false;
        }
    }


}
