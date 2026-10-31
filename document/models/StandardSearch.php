<?php

namespace app\modules\document\models;

use yii\data\ActiveDataProvider;

class StandardSearch extends Standard {

    public static function getDb() {
        return \app\components\Utility::db();
    }

    public function rules() {
        return [
            [['id', 'title', 'area', 'description', 'isActive', 'sort'], 'safe', 'on' => 'search']
        ];
    }

    public function search($params) {
        $activeQuery = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $activeQuery,
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => [
                'defaultOrder' => ['sort' => SORT_ASC],
                'attributes' => ['id', 'title', 'area', 'description', 'sort', 'isActive'],
            ],
        ]);

        if ((!$this->load($params)) and $this->validate()) {
            return $dataProvider;
        }

        $activeQuery
                ->andFilterWhere(['LIKE', 'id', $this->id])
                ->andFilterWhere(['LIKE', 'title', $this->title])
                ->andFilterWhere(['LIKE', 'area', $this->area])
                ->andFilterWhere(['LIKE', 'description', $this->description])
                ->andFilterWhere(['LIKE', 'isActive', $this->isActive])
                ->andFilterWhere(['LIKE', 'sort', $this->sort]);
        return $dataProvider;
    }

}
