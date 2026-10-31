<?php

namespace app\modules\document\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * @see ImsDocumentSearch
 *
 * @author <mail:khodarahmpoor@gmail.com & Tel:09364388747>
 */
class ImsDocumentSearch extends ImsDocument {

    public $companyTitle;
    public $imscode;
    public $imstitle;
    public $imsType;
    public $imsprocessCode;
    public $relatedIMSDocument;
    public $relatedOBS;
    public $preparatorUnitList;
    public $companyIdList;
    public $companytitle;
    public $keywordIdList;
    public $parentID;
    public $typeList;
    public $referenceCompany;

    public function rules() {
        return [
            [['status', 'imsType', 'id', 'relatedOBS', 'referenceCompany', 'companytitle', 'typeList', 'companyIdList', 'keywordIdList', 'preparatorUnitList', 'relatedIMSDocument', 'imstitle', 'imscode', 'preparationDate', 'revisionDate', 'effectiveDate', 'revisionNumber', 'preparatorUnit', 'processCode', 'reportfile', 'nativefile', 'companyTitle', 'autoCode', 'mainDocumentID', 'clause', 'mainUnitObsIdRef'], 'safe', 'on' => 'search'],
        ];
    }

    public function scenarios() {
        $scenario = parent::scenarios();
        $scenario['search'] = ['status', 'companytitle', 'imsType', 'id', 'relatedOBS', 'referenceCompany', 'typeList', 'companyIdList', 'keywordIdList', 'preparatorUnitList', 'relatedIMSDocument', 'imstitle', 'imscode', 'preparationDate', 'revisionDate', 'effectiveDate', 'revisionNumber', 'preparatorUnit', 'processCode', 'reportfile', 'nativefile', 'companyTitle', 'autoCode', 'mainDocumentID', 'standard', 'clause', 'mainUnitObsIdRef'];
        return $scenario;
    }

    public function search($params, $master, $type) {
        $obs = Yii::$app->session->get('ObsPosition');
        $companyId = Yii::$app->session->get('CompanyID');
        $personTable = \app\modules\OBS\models\Person::tableName();
        $imsDocumentTable = ImsDocument::tableName();
        $imsDocumentTypeTable = ImsDocumentType::tableName();
        $clauseTable = Clause::tableName();
        $standardTable = Standard::tableName();
        $imsDocumentProcessCodeTable = ImsDocumentProcessCode::tableName();
        $obsTable = \app\modules\OBS\models\Obsnew::tableName();
        $companyTable = \app\modules\OBS\models\Company::tableName();

        $typeId = array_search($type, $this->searchTypeArray());
        if ($typeId === FALSE) {
            $typeId = NULL;
        }
        if ($type == 'All') {
            $typeId = $this->TypeIdsArray();
        }
        $hasFormAction = \app\components\Security::formActionAccess(422) ? 1 : 0;
        $isCandoAdmin = \app\access\AccessControl::isAccess(['obs', 'check' => 'isCandoAdmin', 'errorException' => false]) ? 1 : 0;
        $activeQuery = static::find()
                ->joinWith(['clauses.standards'])
                ->company()
                ->leftJoin($imsDocumentProcessCodeTable, "$imsDocumentProcessCodeTable.id=$imsDocumentTable.processCode")
                ->leftJoin($imsDocumentTypeTable, "$imsDocumentTypeTable.ID=$imsDocumentTable.type")
                ->leftJoin($obsTable, "$obsTable.obsId=$imsDocumentTable.preparatorUnit")
                ->leftJoin("$obsTable as mainUnit", "mainUnit.obsId=$imsDocumentTable.mainUnitObsIdRef")
                ->addSelect([
                    'id' => "$imsDocumentTable.id",
                    'mainDocumentID' => "(CASE WHEN $imsDocumentTable.mainDocumentID>0 THEN 1 ELSE 0 END)",
                    'autoCode' => "$imsDocumentTable.autoCode",
                    'preparationDate' => "$imsDocumentTable.preparationDate",
                    'mainUnitObsIdRef' => "$imsDocumentTable.mainUnitObsIdRef",
                    'revisionDate' => "$imsDocumentTable.revisionDate",
                    'effectiveDate' => "$imsDocumentTable.effectiveDate",
                    'revisionNumber' => "$imsDocumentTable.revisionNumber",
                    'preparatorUnitTitle' => "$obsTable.obsTitle",
                    'preparatorUnit' => "$imsDocumentTable.preparatorUnit",
                    'reportfile' => "$imsDocumentTable.reportfile",
                    'nativefile' => "$imsDocumentTable.nativefile",
                    'imstitle' => "$imsDocumentTable.title",
                    'imscode' => "$imsDocumentTable.code",
                    'imsprocessCode' => "CONCAT($imsDocumentProcessCodeTable.code, ' - ', $imsDocumentProcessCodeTable.PersianTitle)",
                    'companyTitle' => "$personTable.PersonTitle",
                    'companyIdRef' => "$imsDocumentTable.companyIdRef",
                    'imsType' => "CONCAT(COALESCE($imsDocumentTypeTable.Code,''), ' - ', COALESCE($imsDocumentTypeTable.PersianTitle,''))",
                    'status' => "$imsDocumentTable.status",
                    'private' => "$imsDocumentTable.private",
                    'parentID' => "$imsDocumentTable.parentID",
                    'referenceCompany' => $master == 1 ? "$companyTable.shortTitle" : "$personTable.PersonTitle",
                    'clause' => "GROUP_CONCAT(DISTINCT CONCAT(mainClause,'-',subClause, ' ', $clauseTable.title) ORDER BY $clauseTable.id SEPARATOR ' | ')",
//                    'codifiedInformation' => "GROUP_CONCAT(DISTINCT CONCAT('بند ', mainClause,'-',subClause, ' ', $clauseTable.title) ORDER BY $clauseTable.id SEPARATOR ' | ')",
                    'standard' => "GROUP_CONCAT(DISTINCT $standardTable.title ORDER BY $standardTable.id SEPARATOR ' | ')"
                ])
                // فیلتر دیدن تمامی پدر های داکیومنت ها
                /* ->andWhere(["in","$imsDocumentTable.id",
                  ((new \yii\db\Query())->from(["imsDocumentTable"=>$imsDocumentTable])
                  ->andWhere("imsDocumentTable.type=$imsDocumentTable.type")
                  ->andWhere("imsDocumentTable.title=$imsDocumentTable.title")
                  ->andWhere(["in","imsDocumentTable.revisionNumber",
                  ((new \yii\db\Query())->from(["imsDocumentRevisionTable"=>$imsDocumentTable])
                  ->andWhere("imsDocumentRevisionTable.type=$imsDocumentTable.type")
                  ->andWhere("imsDocumentRevisionTable.title=$imsDocumentTable.title")
                  ->andWhere("imsDocumentRevisionTable.isDeleted is null or imsDocumentRevisionTable.isDeleted<>1")
                  ->addSelect("max(imsDocumentRevisionTable.revisionNumber)")
                  )
                  ])
                  ->addSelect("id")
                  )
                  ]) */
                ->andWhere(["in", "$imsDocumentTable.revisionNumber", ((new \yii\db\Query())->from(["imsDocumentRevisionTable" => $imsDocumentTable])
                    ->addSelect("max(imsDocumentRevisionTable.revisionNumber)")
                    ->andWhere("imsDocumentRevisionTable.parentID=$imsDocumentTable.parentID")
                    ->andWhere(["$imsDocumentTable.type" => $typeId])
                    ->andWhere("$isCandoAdmin=1 or $hasFormAction=1 or ($imsDocumentTable.private=0 AND ($imsDocumentTypeTable.ID !=10 or ($imsDocumentTypeTable.ID =10 AND $companyId=248)))  or $imsDocumentTable.companyIdRef=$companyId")
                    ->andWhere("imsDocumentRevisionTable.isDeleted is null or imsDocumentRevisionTable.isDeleted<>1")
            )])
                ->groupBy("$imsDocumentTable.id");

//        if (!$isCandoAdmin and $obs != 702) {
//            $activeQuery->andWhere(["$imsDocumentTable.status" => [1, 2]]);
//        }

        if ($master) {
            $activeQuery->master();
            $activeQuery->andWhere(["$imsDocumentTable.status" => 1]);
        }
//        echo $activeQuery->createCommand()->rawSql;
//        die();

        $dataProvider = new ActiveDataProvider([
            'query' => $activeQuery,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
        ]);

        $dataProvider->setSort(new \yii\data\Sort([
                    'defaultOrder' => ["id" => SORT_DESC],
                    'attributes' => [
                        'title',
                        'status',
                        'mainDocumentID',
                        'preparationDate',
                        'revisionDate',
                        'effectiveDate',
                        'revisionNumber',
                        'preparatorUnit',
                        'preparatorUnitTitle' => [
                            'asc' => ["$personTable.PersonTitle" => SORT_ASC],
                            'desc' => ["$personTable.PersonTitle" => SORT_DESC],
                        ],
                        'processCode' => [
                            'asc' => ["CONCAT($imsDocumentProcessCodeTable.code, ' - ', $imsDocumentProcessCodeTable.PersianTitle)" => SORT_ASC],
                            'desc' => ["CONCAT($imsDocumentProcessCodeTable.code, ' - ', $imsDocumentProcessCodeTable.PersianTitle)" => SORT_DESC],
                        ],
                        'referenceCompany' => [
                            'asc' => ["$companyTable.shortTitle" => SORT_ASC],
                            'desc' => ["$companyTable.shortTitle" => SORT_DESC],
                        ],
                        'companyTitle' => [
                            'asc' => ["$personTable.PersonTitle" => SORT_ASC],
                            'desc' => ["$personTable.PersonTitle" => SORT_DESC],
                        ],
                        'id' => [
                            'asc' => ["$imsDocumentTable.id" => SORT_ASC],
                            'desc' => ["$imsDocumentTable.id" => SORT_DESC],
                        ],
                        'imstitle' => [
                            'asc' => ["$imsDocumentTable.title" => SORT_ASC],
                            'desc' => ["$imsDocumentTable.title" => SORT_DESC],
                        ],
                        'imscode' => [
                            'asc' => ["$imsDocumentTable.code" => SORT_ASC],
                            'desc' => ["$imsDocumentTable.code" => SORT_DESC],
                        ],
                        'imsType' => [
                            'asc' => ["$imsDocumentTypeTable.Code" => SORT_ASC],
                            'desc' => ["$imsDocumentTypeTable.Code" => SORT_DESC],
                        ],
                        'reportfile' => [
                            'asc' => ["$imsDocumentTable.reportfile" => SORT_ASC],
                            'desc' => ["$imsDocumentTable.reportfile" => SORT_DESC],
                        ],
                        'nativefile' => [
                            'asc' => ["$imsDocumentTable.nativefile" => SORT_ASC],
                            'desc' => ["$imsDocumentTable.nativefile" => SORT_DESC],
                        ],
                    ],
        ]));

        if ((!$this->load($params)) and $this->validate()) {
            return $dataProvider;
        }

        // search relatedIMSDocument
        $relatedIMSDocumentArray = [];
        if (!empty($this->relatedIMSDocument)) {
            foreach ($this->relatedIMSDocument as $key => $value) {
                array_push($relatedIMSDocumentArray, $value);
            }
        }
        if (array_count_values($relatedIMSDocumentArray)) {
            $relatedImsTable = ImsDocumentRelated::tableName();
            $relatedImsModel = ImsDocumentRelated::find()->andWhere(["in", "to", $relatedIMSDocumentArray])
                    ->addSelect("from");
            $activeQuery->andFilterWhere(["in", "$imsDocumentTable.id", $relatedImsModel]);
        }

        // search OBSrelatedIMSDocument
        $obsRelatedIMSDocumentArray = [];
        if (!empty($this->relatedOBS)) {
            foreach ($this->relatedOBS as $key => $value) {
                array_push($obsRelatedIMSDocumentArray, $value);
            }
        }

        if (array_count_values($obsRelatedIMSDocumentArray)) {
            $obsRelatedImsTable = ImsDocumentOBSRelated::tableName();
            $obsRelatedImsModel = ImsDocumentOBSRelated::find()
                    ->andWhere(["in", "obsIdRef", $obsRelatedIMSDocumentArray])
                    ->addSelect("imsIdRef");
            $activeQuery->andFilterWhere(["in", "$imsDocumentTable.id", $obsRelatedImsModel]);
        }

        // search preparatorUnitList
        $preparatorUnitListArray = [];
        if (!empty($this->preparatorUnitList)) {
            foreach ($this->preparatorUnitList as $key => $value) {
                array_push($preparatorUnitListArray, $value);
            }
        }

        if (array_count_values($preparatorUnitListArray)) {
            $activeQuery->andFilterWhere(["in", "$imsDocumentTable.preparatorUnit", $preparatorUnitListArray]);
        }



        // search companyIdList
        $companyIdArray = [];
        if (!empty($this->companyIdList)) {
            foreach ($this->companyIdList as $key => $value) {
                array_push($companyIdArray, $value);
            }
        }

        if (array_count_values($companyIdArray)) {
            $activeQuery->andFilterWhere(["in", "$imsDocumentTable.companyIdRef", $companyIdArray]);
        }

        // search keywordIdList
        $keywordIdArray = [];
        if (!empty($this->keywordIdList)) {
            foreach ($this->keywordIdList as $key => $value) {
                array_push($keywordIdArray, $value);
            }
        }
        if (array_count_values($keywordIdArray)) {
            $keywordRelatedListTable = ImsDocumentKeywordRelated::tableName();
            if (array_count_values($keywordIdArray)) {
                $activeQuery->andFilterWhere(["in", "$imsDocumentTable.id", (
                            (new \yii\db\Query())->from($keywordRelatedListTable)
                            ->andWhere(["in", "keywordIdRef", $keywordIdArray])
                            ->addSelect('imsIdRef')
            )]);
            }
        }


        // search typeList
        $typeListArray = [];
        if (!empty($this->typeList)) {
            foreach ($this->typeList as $key => $value) {
                array_push($typeListArray, $value);
            }
        }

        if (array_count_values($typeListArray)) {
            $activeQuery->andFilterWhere(["in", "$imsDocumentTable.type", $typeListArray]);
        }

        if ($this->mainDocumentID !== null && $this->mainDocumentID !== '') {
            if ($this->mainDocumentID == 1) {
                $activeQuery->andWhere([">", "$imsDocumentTable.mainDocumentID", 0]);
            } else {
                $activeQuery->andWhere(["$imsDocumentTable.mainDocumentID" => null]);
            }
        }

        $activeQuery
                ->andFilterWhere(['LIKE', "$imsDocumentTable.id", $this->id])
                ->andFilterWhere(['LIKE', "$imsDocumentTable.code", $this->imscode])
                ->andFilterWhere(['LIKE', "$imsDocumentTable.autoCode", $this->autoCode])
                ->andFilterWhere(['LIKE', 'preparationDate', $this->preparationDate])
                ->andFilterWhere(['LIKE', 'revisionDate', $this->revisionDate])
                ->andFilterWhere(['=', "$imsDocumentTable.type", $this->imsType])
                ->andFilterWhere(['LIKE', 'effectiveDate', $this->effectiveDate])
                ->andFilterWhere(["$imsDocumentTable.status" => $this->status])
                ->andFilterWhere(['LIKE', 'revisionNumber', $this->revisionNumber])
                ->andFilterWhere(['LIKE', "$obsTable.obsTitle", $this->preparatorUnit])
                ->andFilterWhere(['LIKE', "mainUnit.obsTitle", $this->mainUnitObsIdRef])
                ->andFilterWhere(['LIKE', "CONCAT($imsDocumentProcessCodeTable.code, ' - ', $imsDocumentProcessCodeTable.PersianTitle)", $this->processCode])
                ->andFilterWhere(['LIKE', "$companyTable.shortTitle", $this->referenceCompany])
                ->andFilterWhere(['or', ['LIKE', "$imsDocumentTable.title", str_replace('ی', 'ي', $this->imstitle)]
                    , ['LIKE', "$imsDocumentTable.title", str_replace('ي', 'ی', $this->imstitle)]
                    , ['LIKE', "$imsDocumentTable.title", str_replace('ک', 'ك', $this->imstitle)]
                    , ['LIKE', "$imsDocumentTable.title", str_replace('ك', 'ک', $this->imstitle)]
                ])
                ->andFilterWhere(['or', ['LIKE', "$personTable.PersonTitle", str_replace('ی', 'ي', $this->companyTitle)]
                    , ['LIKE', "$personTable.PersonTitle", str_replace('ي', 'ی', $this->companyTitle)]
                    , ['LIKE', "$personTable.PersonTitle", str_replace('ک', 'ك', $this->companyTitle)]
                    , ['LIKE', "$personTable.PersonTitle", str_replace('ك', 'ک', $this->companyTitle)]
                ])
                ->andFilterWhere(['LIKE', "$imsDocumentTable.reportfile", $this->reportfile])
                ->andFilterWhere(['LIKE', "$imsDocumentTable.nativefile", $this->nativefile])
        ;
        if ($this->clause && $this->standard) {
            $activeQuery->having([
                'AND',
                ['LIKE', "clause", $this->clause],
                ['LIKE', "standard", $this->standard]
            ]);
        } else if ($this->clause) {
            $activeQuery->having(['LIKE', "clause", $this->clause]);
        } else if ($this->standard) {
            $activeQuery->having(['LIKE', "standard", $this->standard]);
        }
//        echo $activeQuery->createCommand()->rawSql;
//        die();
        return $dataProvider;
    }

    public function searchImsDocumentChild($imsId, $master) {
        $personTable = \app\modules\OBS\models\Person::tableName();
        $imsDocumentTable = ImsDocument::tableName();
        $imsDocumentTypeTable = ImsDocumentType::tableName();
        $imsDocumentProcessCodeTable = ImsDocumentProcessCode::tableName();
        $companyTable = \app\modules\OBS\models\Company::tableName();

        $imsDocumentModel = \app\modules\document\models\ImsDocument::find()
                ->where(['id' => $imsId])
                ->one();
        $mainDocumentID = isset($imsDocumentModel->mainDocumentID) ? $imsDocumentModel->mainDocumentID : $imsDocumentModel->id;
        $activeQuery = static::find()->company()
                ->leftJoin($imsDocumentTypeTable, "$imsDocumentTypeTable.ID=$imsDocumentTable.type")
                ->leftJoin($imsDocumentProcessCodeTable, "$imsDocumentProcessCodeTable.id=$imsDocumentTable.processCode")
                ->addSelect([
                    'id' => "$imsDocumentTable.id",
                    'mainDocumentID' => "$imsDocumentTable.mainDocumentID",
                    'autoCode' => "$imsDocumentTable.autoCode",
                    'preparationDate' => "$imsDocumentTable.preparationDate",
                    'revisionDate' => "$imsDocumentTable.revisionDate",
                    'effectiveDate' => "$imsDocumentTable.effectiveDate",
                    'revisionNumber' => "$imsDocumentTable.revisionNumber",
                    'preparatorUnit' => "$imsDocumentTable.preparatorUnit",
                    'reportfile' => "$imsDocumentTable.reportfile",
                    'nativefile' => "$imsDocumentTable.nativefile",
                    'imstitle' => "$imsDocumentTable.title",
                    'imsType' => "$imsDocumentTypeTable.Title",
                    'imsprocessCode' => "CONCAT($imsDocumentProcessCodeTable.code, ' - ', $imsDocumentProcessCodeTable.PersianTitle)",
                    'status' => "$imsDocumentTable.status",
                    'companytitle' => "$personTable.PersonTitle",
                    'companyIdRef' => "$imsDocumentTable.companyIdRef",
//                    "referenceCompany" => $master == 1 ? "$companyTable.shortTitle" : "$personTable.PersonTitle"
                ])
//                ->andWhere(["$imsDocumentTable.mainDocumentID" => $mainDocumentID])
                ->andWhere(["<>", "$imsDocumentTable.id", $imsId])
//            ->andWhere(["$imsDocumentTable.status" => 0])
                ->andWhere("$imsDocumentTable.isDeleted is null or $imsDocumentTable.isDeleted != 1")
                ->orderBy(["$imsDocumentTable.revisionNumber" => SORT_DESC])
        ;

        if ($master) {
//            $activeQuery->master();
            $activeQuery->andWhere(["$imsDocumentTable.status" => 1]);
        }
        if (isset($imsDocumentModel->mainDocumentID)) {
            $activeQuery->andWhere("$imsDocumentTable.id=$mainDocumentID or $imsDocumentTable.mainDocumentID=$mainDocumentID");
        } else {
            $activeQuery->andWhere(["$imsDocumentTable.mainDocumentID" => $mainDocumentID]);
        }
//        echo $activeQuery->createCommand()->rawSql;
        $dataProvider = new ActiveDataProvider([
            'query' => $activeQuery,
            'sort' => false,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
        ]);

        return $dataProvider;
    }

    public function searchImsDocumentExpire($imsDocumentModel) {
        $personTable = \app\modules\OBS\models\Person::tableName();
        $imsDocumentTable = ImsDocument::tableName();
        $imsDocumentProcessCodeTable = ImsDocumentProcessCode::tableName();
        $personTable = \app\modules\OBS\models\Person::tableName();

        $activeQuery = static::find()->company()
                ->leftJoin($imsDocumentProcessCodeTable, "$imsDocumentProcessCodeTable.id=$imsDocumentTable.processCode")
                ->addSelect([
                    'id' => "$imsDocumentTable.id",
                    'preparationDate' => "$imsDocumentTable.preparationDate",
                    'revisionDate' => "$imsDocumentTable.revisionDate",
                    'effectiveDate' => "$imsDocumentTable.effectiveDate",
                    'revisionNumber' => "$imsDocumentTable.revisionNumber",
                    'preparatorUnit' => "$imsDocumentTable.preparatorUnit",
                    'reportfile' => "$imsDocumentTable.reportfile",
                    'nativefile' => "$imsDocumentTable.nativefile",
                    'imstitle' => "$imsDocumentTable.title",
                    'imscode' => "$imsDocumentTable.code",
                    'imsprocessCode' => "$imsDocumentProcessCodeTable.code",
                    'status' => "$imsDocumentTable.status",
                    'companytitle' => "$personTable.PersonTitle",
                ])
                ->andWhere(["$imsDocumentTable.parentID" => $imsDocumentModel->parentID])
                ->andWhere(["!=", "$imsDocumentTable.id", $imsDocumentModel->id])
                ->andWhere(["$imsDocumentTable.status" => 0])
                ->orderBy(["$imsDocumentTable.revisionNumber" => SORT_DESC])
                ->andWhere("$imsDocumentTable.isDeleted is null or $imsDocumentTable.isDeleted != 1")
        ;
//        echo $activeQuery->createCommand()->rawSql;
        $dataProvider = new ActiveDataProvider([
            'query' => $activeQuery,
            'sort' => false,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
        ]);

        return $dataProvider;
    }

    public function searchImsDocumentExpireOld($type, $code) {
        $personTable = \app\modules\OBS\models\Person::tableName();
        $imsDocumentTable = ImsDocument::tableName();
        $imsDocumentTitleListTable = ImsDocumentTitleList::tableName();
        $imsDocumentCodeListTable = ImsDocumentCodeList::tableName();
        $imsDocumentProcessCodeTable = ImsDocumentProcessCode::tableName();
        $personTable = \app\modules\OBS\models\Person::tableName();

        $typeId = array_search($type, $this->typeArray());

        $activeQuery = static::find()->company()
                ->innerJoin($imsDocumentTitleListTable, "$imsDocumentTitleListTable.id=$imsDocumentTable.title")
                ->innerJoin($imsDocumentCodeListTable, "$imsDocumentCodeListTable.id=$imsDocumentTable.code")
                ->innerJoin($imsDocumentProcessCodeTable, "$imsDocumentProcessCodeTable.id=$imsDocumentTable.processCode")
                ->addSelect([
                    'id' => "$imsDocumentTable.id",
                    'preparationDate' => "$imsDocumentTable.preparationDate",
                    'revisionDate' => "$imsDocumentTable.revisionDate",
                    'effectiveDate' => "$imsDocumentTable.effectiveDate",
                    'revisionNumber' => "$imsDocumentTable.revisionNumber",
                    'preparatorUnit' => "$imsDocumentTable.preparatorUnit",
                    'reportfile' => "$imsDocumentTable.reportfile",
                    'nativefile' => "$imsDocumentTable.nativefile",
                    'imstitle' => "$imsDocumentTitleListTable.title",
                    'imscode' => "$imsDocumentCodeListTable.title",
                    'imsprocessCode' => "$imsDocumentProcessCodeTable.code",
                    'status' => "$imsDocumentTable.status",
                    'companytitle' => "$personTable.PersonTitle",
                ])
                ->andWhere(["$imsDocumentTable.type" => $type])
                ->andWhere(["$imsDocumentTable.code" => $code])
                ->andWhere(["$imsDocumentTable.status" => 0])
                ->orderBy(["$imsDocumentTable.revisionNumber" => SORT_DESC])
                ->andWhere("$imsDocumentTable.isDeleted is null or $imsDocumentTable.isDeleted != 1")
        ;

        $dataProvider = new ActiveDataProvider([
            'query' => $activeQuery,
            'sort' => false,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
        ]);

        return $dataProvider;
    }

}
