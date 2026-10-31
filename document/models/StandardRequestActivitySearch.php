<?php

namespace app\modules\document\models;

use Yii;
use yii\data\ActiveDataProvider;
use app\components\CandoHelper;

class StandardRequestActivitySearch extends StandardRequestActivity {

    public $year;
    public $standard;
    public $clauseTitle;
    public $fullClauseNo;
    public $clauseActivityTitle;
    public $companyName;
    public $standardRequestId;
    public $clauseActivityId;
    public $isLock;
    // public $radehStatus;
    // public $radehDescription;
    public $radehUser;
    // public $radehUpdateDate;
    // public $holdingStatus;
    // public $holdingDescription;
    public $holdingUser;
    // public $holdingUpdateDate;

    public static function getDb() {
        return \app\components\Utility::db();
    }

    public function rules() {
        return [
            [
                []
//                ['id', 'companyName', 'year', 'clauseTitle', 'fullClauseNo', 'clauseActivityTitle', 'radehDescription', 'radehStatus', 'radehUpdateDate', 'holdingDescription', 'holdingStatus', 'holdingUpdateDate']
                , 'safe']
        ];
    }

    public function search($standardRequestIdRef, $params) {
        $userTable = \app\modules\account\models\User::tableName();
        $clauseTable = \app\modules\document\models\Clause::tableName();
        $personTable = \app\modules\OBS\models\Person::tableName();
        $clauseActivityTable = \app\modules\document\models\ClauseActivity::tableName();
        $standardClauseTable = \app\modules\document\models\StandardClause::tableName();
        $standardRequestTable = \app\modules\document\models\StandardRequest::tableName();
        $standardRequestActivityTable = \app\modules\document\models\StandardRequestActivity::tableName();

        $activeQuery = self::find()
            ->select([
                "$personTable.companyName",
                "$standardRequestActivityTable.id",
                "$standardRequestActivityTable.color",
                "$standardRequestActivityTable.fileName",
                "$standardRequestActivityTable.radehStatus",
                "$standardRequestActivityTable.radehDescription",
                "$standardRequestActivityTable.radehUpdateDate",
                "$standardRequestActivityTable.activityDocumentIdRef",
                "concat(radehUser.FirstName, ' ', radehUser.LastName) as radehUser",
                "$standardRequestActivityTable.holdingStatus",
                "$standardRequestActivityTable.holdingDescription",
                "$standardRequestActivityTable.holdingUpdateDate",
                "concat(holdingUser.FirstName, ' ', holdingUser.LastName) as holdingUser",
                "$clauseActivityTable.clauseIdRef",
                "$standardRequestTable.id as standardRequestIdRef",
                "$standardRequestTable.year",
                "$standardRequestTable.isLock",
                "$clauseTable.fullClauseNo",
                "$clauseTable.title as clauseTitle",
                "$clauseActivityTable.id as clauseActivityIdRef",
                "$clauseActivityTable.title as clauseActivityTitle"
            ])
            ->rightJoin($clauseActivityTable, "$clauseActivityTable.id = $standardRequestActivityTable.clauseActivityIdRef AND $standardRequestActivityTable.standardRequestIdRef=$standardRequestIdRef")
            ->innerJoin($clauseTable, "$clauseTable.id = $clauseActivityTable.clauseIdRef")
            ->innerJoin($standardClauseTable, "$standardClauseTable.ClauseIdRef = $clauseTable.id")
            ->innerJoin($standardRequestTable, "FIND_IN_SET($standardClauseTable.StandardIdRef, $standardRequestTable.standard) > 0")
            ->innerJoin($personTable, "$personTable.PersonID = $standardRequestTable.companyIdRef")
            ->leftJoin("$userTable as radehUser", "radehUser.UserID = $standardRequestActivityTable.radehUserIdref")
            ->leftJoin("$userTable as holdingUser", "holdingUser.UserID = $standardRequestActivityTable.holdingUserIdRef")
            ->where(["$standardRequestTable.id" => $standardRequestIdRef])
            ->andWhere(["$clauseActivityTable.isDeleted" => 0])
            // ->where("$standardRequestActivityTable.standardRequestIdRef=$standardRequestIdRef OR $standardRequestActivityTable.standardRequestIdRef IS NULL")
            ->groupBy("$clauseActivityTable.id");

        $dataProvider = new ActiveDataProvider([
            'db' => \app\components\Utility::db(),
            'query' => $activeQuery,
            'pagination' => [
                'pageSize' => 200,
            ],
            'sort' => [
                'attributes' => [
                    'id' => [
                        'asc' => ["$standardRequestActivityTable.id" => SORT_ASC],
                        'desc' => ["$standardRequestActivityTable.id" => SORT_DESC],
                    ],
                    'companyName' => [
                        'asc' => ["$standardRequestTable.companyName" => SORT_ASC],
                        'desc' => ["$standardRequestTable.companyName" => SORT_DESC],
                    ],
                    'year' => [
                        'asc' => ["$standardRequestTable.year" => SORT_ASC],
                        'desc' => ["$standardRequestTable.year" => SORT_DESC],
                    ],
                    'fullClauseNo' => [
                        'asc' => ["$clauseTable.sort" => SORT_ASC],
                        'desc' => ["$clauseTable.sort" => SORT_DESC],
                    ],
                    'clauseTitle' => [
                        'asc' => ["$clauseTable.title" => SORT_ASC],
                        'desc' => ["$clauseTable.title" => SORT_DESC],
                    ],
                    'clauseActivityTitle' => [
                        'asc' => ["$clauseActivityTable.title" => SORT_ASC],
                        'desc' => ["$clauseActivityTable.title" => SORT_DESC],
                    ],
                    'radehDescription' => [
                        'asc' => ["$standardRequestActivityTable.radehDescription" => SORT_ASC],
                        'desc' => ["$standardRequestActivityTable.radehDescription" => SORT_DESC],
                    ],
                    'radehStatus' => [
                        'asc' => ["$standardRequestActivityTable.radehStatus" => SORT_ASC],
                        'desc' => ["$standardRequestActivityTable.radehStatus" => SORT_DESC],
                    ],
                    'radehUpdateDate' => [
                        'asc' => ["$standardRequestActivityTable.radehUpdateDate" => SORT_ASC],
                        'desc' => ["$standardRequestActivityTable.radehUpdateDate" => SORT_DESC],
                    ],
                    'holdingDescription' => [
                        'asc' => ["$standardRequestActivityTable.holdingDescription" => SORT_ASC],
                        'desc' => ["$standardRequestActivityTable.holdingDescription" => SORT_DESC],
                    ],
                    'holdingStatus' => [
                        'asc' => ["$standardRequestActivityTable.holdingStatus" => SORT_ASC],
                        'desc' => ["$standardRequestActivityTable.holdingStatus" => SORT_DESC],
                    ],
                    'holdingUpdateDate' => [
                        'asc' => ["$standardRequestActivityTable.holdingUpdateDate" => SORT_ASC],
                        'desc' => ["$standardRequestActivityTable.holdingUpdateDate" => SORT_DESC],
                    ]
                ],
            ],
        ]);
        if ((!$this->load($params)) and $this->validate()) {
            return $dataProvider;
        }

        $activeQuery
                ->andFilterWhere(['=', "$standardRequestTable.companyIdRef", $this->companyName])
                ->andFilterWhere(['LIKE', "$standardRequestTable.year", $this->year])
                ->andFilterWhere(['LIKE', "$standardRequestActivityTable.id", $this->id])
                ->andFilterWhere(['LIKE', "$clauseTable.title", $this->clauseTitle])
                ->andFilterWhere(['LIKE', "$clauseTable.fullClauseNo", $this->fullClauseNo])
                ->andFilterWhere(['LIKE', "$clauseActivityTable.title", $this->clauseActivityTitle])
                ->andFilterWhere(['LIKE', "$standardRequestActivityTable.radehDescription", $this->radehDescription])
                ->andFilterWhere(['LIKE', "$standardRequestActivityTable.radehStatus", $this->radehStatus])
                ->andFilterWhere(
                        ['or',
                            ['LIKE', "$standardRequestActivityTable.radehUpdateDate", $this->radehUpdateDate],
                            ['LIKE', "concat(radehUser.FirstName, ' ', radehUser.LastName)", $this->radehUpdateDate]
                        ]
                )
                ->andFilterWhere(['LIKE', "$standardRequestActivityTable.holdingDescription", $this->holdingDescription])
                ->andFilterWhere(['LIKE', "$standardRequestActivityTable.holdingStatus", $this->holdingStatus])
                ->andFilterWhere(
                        ['or',
                            ['LIKE', "$standardRequestActivityTable.holdingUpdateDate", $this->holdingUpdateDate],
                            ['LIKE', "concat(holdingUser.FirstName, ' ', holdingUser.LastName)", $this->holdingUpdateDate]
                        ]
                );

        return $dataProvider;
    }

}
