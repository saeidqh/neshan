<?php

namespace app\modules\document\models;

use Yii;
use yii\data\ActiveDataProvider;

class ImsDocumentTypeSearch extends ImsDocumentType {

    public function rules() {
        return [
            [['ID', 'Code', 'Title', 'PersianTitle', 'sort', 'isActive'
                ], 'safe', 'on' => 'search'
            ],
        ];
    }

    public static function getDb() {
        return \app\components\Utility::db();
    }

    public function search($params) {

        $activeQuery = ImsDocumentType::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $activeQuery,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
            'sort' => [
                'defaultOrder' => ['isActive' => SORT_DESC, 'sort' => SORT_ASC],
                'attributes' => [
                    'ID', 'Code', 'Title', 'PersianTitle', 'sort', 'isActive'
                ]
            ],
        ]);

        if ((!$this->load($params)) and $this->validate()) {
            return $dataProvider;
        }
        $activeQuery
        ->andFilterWhere(['LIKE', 'ID', $this->ID])
        ->andFilterWhere(['LIKE', 'Code', $this->Code])
        ->andFilterWhere(['LIKE', 'Title', $this->Title])
        ->andFilterWhere(['LIKE', 'PersianTitle', $this->PersianTitle])
        ->andFilterWhere(['LIKE', 'sort', $this->sort])
        ->andFilterWhere(['LIKE', 'isActive', $this->isActive]);
        return $dataProvider;
    }

}
