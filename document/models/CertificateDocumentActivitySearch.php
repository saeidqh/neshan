<?php

namespace app\modules\document\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * @see ImsDocumentSearch
 *
 * @author
 */

class CertificateDocumentActivitySearch extends CertificateDocumentActivity {



    public function rules() {
        return [
            [['activityId','activityTitle','base'], 'safe', 'on' => 'search'],
        ];
    }

    public $activityId;
    public $activityTitle;
    public $base;
    public $authorizedWork;
    public $authorizedCapacity;
    public $approvedContract;
    public $activityDocumentId;

    public static function getDb() {
        return \app\components\Utility::db();
    }

    public function searchActivitiesDocument($did) {

        $activityTableName = \app\modules\document\models\CertificateActivity::tableName();
        $activityDocumentTableName = \app\modules\document\models\CertificateDocumentActivity::tableName();
        $activityDocumentBaseTypeTableName = \app\modules\document\models\CertificateDocumentBaseType::tableName();
        $documentTableName = \app\modules\document\models\CertificateDocument::tableName();

        //برای تبدیل سطر های بیس تایپ به ستون برای نمایش در جدول باید کوئری های زیر را بزنیم.
        $baseQuery = CertificateDocumentBaseType::find()
            ->addSelect(["$activityDocumentBaseTypeTableName.value"])->where([
             "$activityDocumentBaseTypeTableName.activityDocumentIdRef" => new \yii\db\Expression($activityDocumentTableName.".id"),
            "$activityDocumentBaseTypeTableName.baseTypeIdRef" => CertificateDocumentBaseType::BASE_ID
        ])->createCommand()->rawSql;

        $authorizedWorkQuery = CertificateDocumentBaseType::find()
            ->addSelect(["$activityDocumentBaseTypeTableName.value"])->where([
                "$activityDocumentBaseTypeTableName.activityDocumentIdRef" => new \yii\db\Expression($activityDocumentTableName.".id"),
                "$activityDocumentBaseTypeTableName.baseTypeIdRef" => CertificateDocumentBaseType::AUTHORIZED_WORK_ID
            ])->createCommand()->rawSql;

        $authorizedCapacityQuery = CertificateDocumentBaseType::find()
            ->addSelect(["format($activityDocumentBaseTypeTableName.value,0)"])->where([
                "$activityDocumentBaseTypeTableName.activityDocumentIdRef" => new \yii\db\Expression($activityDocumentTableName.".id"),
                "$activityDocumentBaseTypeTableName.baseTypeIdRef" => CertificateDocumentBaseType::AUTHORIZED_CAPACITY_ID
            ])->createCommand()->rawSql;

        $approvedContractQuery = CertificateDocumentBaseType::find()
            ->addSelect(["format($activityDocumentBaseTypeTableName.value,0)"])->where([
                "$activityDocumentBaseTypeTableName.activityDocumentIdRef" => new \yii\db\Expression($activityDocumentTableName.".id"),
                "$activityDocumentBaseTypeTableName.baseTypeIdRef" => CertificateDocumentBaseType::APPROVED_CONTRACT_ID
            ])->createCommand()->rawSql;


        $activeQuery = CertificateDocument::find()
            ->innerJoin($activityDocumentTableName,"$activityDocumentTableName.certificateIdRef = $documentTableName.certificateId")
            ->leftJoin($activityTableName,"$activityTableName.backgroundActivityTypeId = $activityDocumentTableName.activityIdRef")
            ->addSelect([
                'activityId'=> "$activityTableName.backgroundActivityTypeId",
                'activityDocumentId'=> "$activityDocumentTableName.id",
                'activityTitle'=>"$activityTableName.backgroundActivityTitle",
                'base' => '('.$baseQuery.')',
                'authorizedWork' => '('.$authorizedWorkQuery.')',
                'authorizedCapacity' => '('.$authorizedCapacityQuery.')',
                'approvedContract' => '('.$approvedContractQuery.')',
            ])->where(['certificateId'=>$did]);


        $dataProvider = new ActiveDataProvider([
            'query' => $activeQuery,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
        ]);


        return $dataProvider;


    }






}

