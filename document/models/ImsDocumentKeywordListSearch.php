<?php

namespace app\modules\document\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
* @see ImsDocumentKeywordListSearch
*
* @author <mail:mortezakml@gmail.com & Tel:09366263397>
*/
class ImsDocumentKeywordListSearch extends ImsDocumentKeywordList {
    public $hasImsRelated;
   
    public function rules() {
        return [
            [['id','title'], 'safe', 'on' => 'search'],
        ];
    }

    public function scenarios() {
        $scenario = parent::scenarios();
        $scenario['search'] = ['id','title'];
        return $scenario;
    }

    public function search($params,$imsId) {
        $imsDocumentKeywordListTable= ImsDocumentKeywordList::tableName();
        $imsDocumentKeywordRelatedTable= ImsDocumentKeywordRelated::tableName();
        
        $activeQuery = static::find()->addSelect([
                    "id"=>"$imsDocumentKeywordListTable.id",
                    "title"=>"$imsDocumentKeywordListTable.title",
                    "hasImsRelated"=>"(select count(*) from $imsDocumentKeywordRelatedTable where imsIdRef=:imsId and keywordIdRef=:id)"
                ])->addParams([':imsId'=>$imsId,':id'=>$imsDocumentKeywordListTable.id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $activeQuery,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
        ]);

        $dataProvider->setSort(new \yii\data\Sort([
            'attributes' => [
                'title',
                'id',
            ],
        ]));

        if ((!$this->load($params)) and $this->validate()) {
            return $dataProvider;
        }

        $activeQuery
                ->andFilterWhere(['LIKE', "id", $this->id])
                ->andFilterWhere(['or', ['LIKE', "title", str_replace('ی', 'ي', $this->title)]
                    , ['LIKE', "title", str_replace('ي', 'ی', $this->title)]
                    , ['LIKE', "title", str_replace('ک', 'ك', $this->title)]
                    , ['LIKE', "title", str_replace('ك', 'ک', $this->title)]
                ])
        ;
        return $dataProvider;
    }

    
    public function searchImsRelated($params,$imsId) {
        $imsDocumentKeywordListTable= ImsDocumentKeywordList::tableName();
        $imsDocumentKeywordRelatedTable= ImsDocumentKeywordRelated::tableName();
        $imsDocumentTable= ImsDocument::tableName();

        $activeQuery = static::find()
                ->innerJoin($imsDocumentKeywordRelatedTable, "$imsDocumentKeywordRelatedTable.keywordIdRef=$imsDocumentKeywordListTable.id")
                ->addSelect([
                    "id"=>"$imsDocumentKeywordRelatedTable.id",
                    "title"=>"$imsDocumentKeywordListTable.title",
                ])
                ->andWhere(["$imsDocumentKeywordRelatedTable.imsIdRef"=>$imsId])
        ;

        $dataProvider = new ActiveDataProvider([
            'query' => $activeQuery,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
        ]);

        $dataProvider->setSort(new \yii\data\Sort([
            'attributes' => [
                "$imsDocumentKeywordListTable.title",
                "$imsDocumentKeywordRelatedTable.id",
            ],
        ]));

        if ((!$this->load($params)) and $this->validate()) {
            return $dataProvider;
        }

        $activeQuery
                ->andFilterWhere(['LIKE', "$imsDocumentKeywordRelatedTable.id", $this->id])
                ->andFilterWhere(['or', ['LIKE', "$imsDocumentKeywordListTable.title", str_replace('ی', 'ي', $this->title)]
                    , ['LIKE', "$imsDocumentKeywordListTable.title", str_replace('ي', 'ی', $this->title)]
                    , ['LIKE', "$imsDocumentKeywordListTable.title", str_replace('ک', 'ك', $this->title)]
                    , ['LIKE', "$imsDocumentKeywordListTable.title", str_replace('ك', 'ک', $this->title)]
                ])
        ;
        return $dataProvider;
    }

    
}
