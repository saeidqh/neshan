<?php

namespace app\modules\document\models;

use Yii;
use yii\data\ActiveDataProvider;

class ImsDocumentProcessCodeSearch extends ImsDocumentProcessCode {

    public function rules() {
        return [
            [['id', 'code', 'Title', 'PersianTitle', 'ProcessGroup', 'sort', 'isActive'
                ], 'safe', 'on' => 'search'
            ],
        ];
    }

    public static function getDb() {
        return \app\components\Utility::db();
    }

    public function search($params) {

        $activeQuery = ImsDocumentProcessCode::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $activeQuery,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
            'sort' => [
                'defaultOrder' => ['isActive' => SORT_DESC, 'sort' => SORT_ASC],
//                'defaultOrder' => ['sort' => SORT_ASC],
                'attributes' => [
                    'id', 'code', 'Title', 'PersianTitle', 'ProcessGroup', 'sort', 'isActive'
                ]
            ],
        ]);
//        $this->load($params);

        if ((!$this->load($params)) and $this->validate()) {
            return $dataProvider;
        }
        $activeQuery
        ->andFilterWhere(['LIKE', 'id', $this->id])
        ->andFilterWhere(['LIKE', 'code', $this->code])
        ->andFilterWhere(['LIKE', 'Title', $this->Title])
        ->andFilterWhere(['LIKE', 'IsManagerial', $this->PersianTitle])
        ->andFilterWhere(['LIKE', 'ProcessGroup', $this->ProcessGroup])
        ->andFilterWhere(['LIKE', 'sort', $this->sort])
        ->andFilterWhere(['LIKE', 'isActive', $this->isActive]);
        return $dataProvider;
    }

}
