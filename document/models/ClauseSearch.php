<?php

namespace app\modules\document\models;

use yii\data\ActiveDataProvider;

class ClauseSearch extends Clause {

    public $activityCount;
    public $activityFileCount;
    
    public static function getDb() {
        return \app\components\Utility::db();
    }

    public function rules() {
        return [
            [['id', 'standard', 'title', 'type', 'mainClause', 'subClause', 'fullClauseNo', 'codifiedInformation', 'description', 'isActive', 'weight', 'sort'], 'safe', 'on' => 'search']
        ];
    }

    public function search($params) {
        $clauseTable = Clause::tableName();
        $standardTable = Standard::tableName();
        $clauseActivityTable = \app\modules\document\models\ClauseActivity::tableName();
        $activeQuery = self::find()->joinWith(['standards','activity'])
                ->addSelect(["$clauseTable.*", "GROUP_CONCAT(DISTINCT $standardTable.title SEPARATOR ' | ') AS standard", "COUNT(distinct $clauseActivityTable.id) AS activityCount", "COUNT(distinct $clauseActivityTable.id, $clauseActivityTable.fileName) AS activityFileCount"])
                ->groupBy("$clauseTable.id");

        $dataProvider = new ActiveDataProvider([
            'query' => $activeQuery,
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => [
                'defaultOrder' => ['sort' => SORT_ASC],
                'attributes' => ['id', 'title', 'type', 'codifiedInformation', 'description', 'isActive', 'sort', 'weight'],
            ],
        ]);

        if ((!$this->load($params)) and $this->validate()) {
            return $dataProvider;
        }

        $activeQuery
                ->andFilterWhere(['LIKE', 'id', $this->id])
                ->andFilterWhere(['LIKE', "$clauseTable.title", $this->title])
                ->andFilterWhere(['LIKE', 'type', $this->type])
                ->andFilterWhere(['LIKE', 'mainClause', $this->mainClause])
                ->andFilterWhere(['LIKE', 'subClause', $this->subClause])
                ->andFilterWhere(['LIKE', 'fullClauseNo', $this->fullClauseNo])
                ->andFilterWhere(['LIKE', 'codifiedInformation', $this->codifiedInformation])
                ->andFilterWhere(['LIKE', 'description', $this->description])
                ->andFilterWhere(['LIKE', "$clauseTable.isActive", $this->isActive])
                ->andFilterWhere(['LIKE', "$clauseTable.weight", $this->weight])
                ->andFilterWhere(['LIKE', 'sort', $this->sort]);
        
        if($this->standard) {
            $activeQuery->having(['LIKE', "standard", $this->standard]);
        }
        return $dataProvider;
    }

}
