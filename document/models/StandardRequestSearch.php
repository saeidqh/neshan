<?php

namespace app\modules\document\models;

use Yii;
use yii\data\ActiveDataProvider;
use app\components\CandoHelper;

class StandardRequestSearch extends StandardRequest {

    public static function getDb() {
        return \app\components\Utility::db();
    }

    public function rules() {
        return [
            [['id', 'companyIdRef', 'projectIdRef', 'standard', 'type', 'result', 'requestDate', 'holdingDate', 'auditStartDate', 'auditFinishDate', 'auditDate', 'auditPlanStartDate', 'auditPlanFinishDate'], 'safe']
        ];
    }

    public function search($params) {
        $userCompanyId = yii::$app->session->get('CompanyID');
        $companyAllChildsArray = \app\components\CandoHelper::companyChilds($userCompanyId, 'array');
        $hasFormAction = \app\components\Security::formActionAccess(452) ? true : false;
        $isCandoAdmin = \app\access\AccessControl::isAccess(['obs', 'check' => 'isCandoAdmin', 'errorException' => false]) ? 1 : 0;
        $standardRequestTable = \app\modules\document\models\StandardRequest::tableName();
        $epsTable = \app\modules\wbs\models\Epsnew::tableName();

        $activeQuery = \app\modules\document\models\StandardRequest::find()
            // ->leftJoin($epsTable, "$epsTable.EPSID = $standardRequestTable.projectIdRef")
            // ->andWhere(["$standardRequestTable.companyIdRef" => $companyAllChildsArray])
            ;
        if(!$hasFormAction && !$isCandoAdmin) {
            $activeQuery = $activeQuery->andWhere(["$standardRequestTable.companyIdRef" => $companyAllChildsArray]);
        }
        $dataProvider = new ActiveDataProvider([
            'db' => \app\components\Utility::db(),
            'query' => $activeQuery,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
            'sort' => [
                'attributes' => [
                    'id',
                    'companyIdRef' => [
                        'asc' => ["$standardRequestTable.companyIdRef" => SORT_ASC],
                        'desc' => ["$standardRequestTable.companyIdRef" => SORT_DESC],
                    ],
                    'projectIdRef' => [
                        'asc' => ["$epsTable.EPSTitle" => SORT_ASC],
                        'desc' => ["$epsTable.EPSTitle" => SORT_DESC],
                    ],
                    'type' => [
                        'asc' => ["$standardRequestTable.type" => SORT_ASC],
                        'desc' => ["$standardRequestTable.type" => SORT_DESC],
                    ],
                    'requestDate' => [
                        'asc' => ["$standardRequestTable.requestDate" => SORT_ASC],
                        'desc' => ["$standardRequestTable.requestDate" => SORT_DESC],
                    ],
                    'holdingDate' => [
                        'asc' => ["$standardRequestTable.holdingDate" => SORT_ASC],
                        'desc' => ["$standardRequestTable.holdingDate" => SORT_DESC],
                    ],
                    'auditPlanStartDate' => [
                        'asc' => ["$standardRequestTable.auditPlanStartDate" => SORT_ASC],
                        'desc' => ["$standardRequestTable.auditPlanStartDate" => SORT_DESC],
                    ],
                    'auditPlanFinishDate' => [
                        'asc' => ["$standardRequestTable.auditPlanFinishDate" => SORT_ASC],
                        'desc' => ["$standardRequestTable.auditPlanFinishDate" => SORT_DESC],
                    ]
                ],
            ],
        ]);
        if ((!$this->load($params)) and $this->validate()) {
            return $dataProvider;
        }

        $activeQuery
                ->andFilterWhere(['=', "$standardRequestTable.type", $this->type])
                // ->andFilterWhere(['=', "$standardRequestTable.result", $this->result])
                ->andFilterWhere(['LIKE', "$standardRequestTable.id", $this->id])
                ->andFilterWhere(['LIKE', "$standardRequestTable.companyIdRef", $this->companyIdRef])
                ->andFilterWhere(['LIKE', "$standardRequestTable.projectIdRef", $this->projectIdRef])
                ->andFilterWhere(['LIKE', "$standardRequestTable.standard", $this->standard])
                ->andFilterWhere(['LIKE', "$standardRequestTable.requestDate", $this->requestDate])
                ->andFilterWhere(['LIKE', "$standardRequestTable.auditStartDate", $this->auditStartDate])
                ->andFilterWhere(['LIKE', "$standardRequestTable.auditFinishDate", $this->auditFinishDate])
                ->andFilterWhere(['LIKE', "$standardRequestTable.holdingDate", $this->holdingDate])
                // ->andFilterWhere(['LIKE', "$standardRequestTable.auditDate", $this->auditDate])
                ->andFilterWhere(['LIKE', "$standardRequestTable.auditPlanStartDate", $this->auditPlanStartDate])
                ->andFilterWhere(['LIKE', "$standardRequestTable.auditPlanFinishDate", $this->auditPlanFinishDate]);

        return $dataProvider;
    }

}
