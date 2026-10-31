<?php

namespace app\modules\document\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * @see ImsDocumentSearch
 *
 * @author
 */

class CertificateDocumentSearch extends CertificateDocument {

    public $certificateType;
    public $backgroundActivityType;
    public $company;
    public $standard;


    public function rules() {
        return [
            [['certificateId','certificateType','issuingOrganization','issueDate','expirationDate','activityIdList','company', 'standard'], 'safe', 'on' => 'search'],
        ];
    }

//    public function scenarios() {
//        $scenario = parent::scenarios();
//        $scenario['search'] = [ 'certificateId', 'certificateType', 'issuingOrganization', 'issueDate', 'expirationDate', 'backgroundActivityType', 'company'];
//        return $scenario;
//    }

    public static function getDb() {
        return \app\components\Utility::db();
    }

    public function search($params) {

        $personTable = \app\modules\OBS\models\Person::tableName();
        $companyTable = \app\modules\OBS\models\Company::tableName();
        $standard = \app\modules\document\models\Standard::tableName();
        $certificateDocument = \app\modules\document\models\CertificateDocument::tableName();
        $certificateDocumentActivity = \app\modules\document\models\CertificateDocumentActivity::tableName();
        $certificateActivity = \app\modules\document\models\CertificateActivity::tableName();
        $certificateTypeTable = \app\modules\document\models\CertificateDocumentType::tableName();
        $personModel = \app\modules\OBS\models\Person::tableName();
        $jdate = new \app\components\jDateTime();

        $activeQuery = self::find()
            ->addSelect([
                "$standard.title as standard",
                "$certificateDocument.certificateTypeIdRef",
                "$certificateDocument.certificateId",
                "$certificateDocument.issuingOrganization",
                "$certificateDocument.issueDate",
                "$certificateDocument.expirationDate",
                "$certificateTypeTable.certificateTitle as certificateType",
                "group_concat($certificateActivity.backgroundActivityTitle) as activityIdList",
                "$personModel.PersonTitle as company",
            ])
            ->leftJoin($standard, "$standard.id = $certificateDocument.standardIdRef")
            ->leftJoin($certificateTypeTable, "$certificateTypeTable.certificateTypeId = $certificateDocument.certificateTypeIdRef")
            ->leftJoin($certificateDocumentActivity, "$certificateDocumentActivity.certificateIdRef = $certificateDocument.certificateId")
            ->leftJoin($certificateActivity, "$certificateActivity.backgroundActivityTypeId = $certificateDocumentActivity.activityIdRef")
            ->leftJoin($companyTable, "$certificateDocument.companyIdRef = $companyTable.companyIdRef")
            ->leftJoin($personTable, "$personTable.PersonID = $companyTable.companyIdRef")
            ->groupBy("$certificateDocument.certificateId");


        $dataProvider = new ActiveDataProvider([
            'query' => $activeQuery,
            'sort' => ['defaultOrder' => ['certificateId' => SORT_DESC]],
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);
        $this->load($params); 

        if ((!$this->load($params)) and $this->validate()) {
            return $dataProvider;
        }
        $activeQuery
            ->andFilterWhere(['LIKE', 'certificateId', $this->certificateId])
            ->andFilterWhere(['LIKE', "$standard.title", $this->standard])
            ->andFilterWhere(['LIKE', 'issuingOrganization', $this->issuingOrganization])
            ->andFilterWhere(['LIKE', \app\modules\document\models\CertificateDocument::tableName().'.issueDate', $this->issueDate])
            ->andFilterWhere(['LIKE', \app\modules\document\models\CertificateDocument::tableName().'.expirationDate', $this->expirationDate])
            ->andFilterWhere(['LIKE', \app\modules\document\models\CertificateDocumentType::tableName() . '.certificateTitle', $this->certificateType])
            ->andFilterWhere(['LIKE', \app\modules\document\models\CertificateActivity::tableName() . '.backgroundActivityTitle', $this->activityIdList])
            ->andFilterWhere(['LIKE', \app\modules\OBS\models\Person::tableName() . '.PersonTitle', $this->company])
            ->andFilterWhere(['LIKE', "$certificateActivity.backgroundActivityTitle", $this->activityIdList]);
        return $dataProvider;


    }






}

