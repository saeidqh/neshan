<?php

namespace app\modules\document\models;


use Yii;
use yii\data\ActiveDataProvider;

class StampFlowDocumentSearch extends StampFlowDocument
{

    public $Position;
    public $FirstName;
    public $LastName;
    public $FullName;
    public $NationalCode;
    public $Mobile;

    public function rules()
    {
        return [
            [['FlowId', 'Position', 'FirstName', 'LastName', 'NationalCode','FullName'
                , 'Mobile', 'ReceivedDate', 'RefundDate', 'Created_at', 'Created_by'
                , 'Update_at', 'Update_by'], 'safe', 'on' => 'search'
            ],
        ];
    }

    public static function getDb()
    {
        return \app\components\Utility::db();
    }

    public function search($params, $sid)
    {
        $StampFlowTable = \app\modules\document\models\StampFlowDocument::tableName();
        $obsNewTable = \app\modules\OBS\models\Obsnew::tableName();
        $humanResourceTable = \app\modules\automation\models\HumanResource::tableName();
        $positionTypeObsTable = \app\modules\automation\models\PositionType::tableName();
        $userTable = \app\modules\account\models\User::tableName();

        $activeQuery = self::find()
            ->leftJoin($obsNewTable, "$obsNewTable.obsId = $StampFlowTable.ObsIDRef")
            ->leftJoin($humanResourceTable, "$humanResourceTable.Id = $StampFlowTable.HumanIDRef")
            ->where(['StampIDRef' => $sid]);

        $dataProvider = new ActiveDataProvider([
            'query' => $activeQuery,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
            'sort' => [
                'defaultOrder' => ['FlowId' => SORT_DESC],
                'attributes' => [
                    'FlowId', 'FullName', 'NationalCode', 'Update_at', 'Update_by',
                    'ObsIDRef', 'ReceivedDate', 'RefundDate', 'Created_at', 'Created_by',
                ]
            ],
        ]);
        $this->load($params);

        if ((!$this->load($params)) and $this->validate()) {
            return $dataProvider;
        }
        $activeQuery
            ->andFilterWhere(['LIKE', 'FlowId', $this->FlowId])
            ->andFilterWhere(['LIKE', $obsNewTable . '.obsTitle', $this->Position])
            ->andFilterWhere(['LIKE', 'ReceivedDate', $this->ReceivedDate])
            ->andFilterWhere(['LIKE', 'RefundDate', $this->RefundDate])
            ->andFilterWhere(['LIKE', $humanResourceTable . '.NationalCode', $this->NationalCode])
            ->andFilterWhere(['LIKE', $humanResourceTable . '.Mobile', $this->Mobile])
            ->andFilterWhere(['LIKE', "concat($humanResourceTable.FirstName, ' ', $humanResourceTable.LastName)", $this->FullName])
            ->andFilterWhere(
                ['or',
                    ['LIKE', "$StampFlowTable.Create_by", $this->Created_by],
                    ['LIKE', "concat($userTable.FirstName, ' ', $userTable.LastName)", $this->Created_by]

                ])
            ->andFilterWhere(
                ['or',
                    ['LIKE', "$StampFlowTable.Update_at", $this->Update_at],
                    ['LIKE', "concat($userTable.FirstName, ' ', $userTable.LastName)", $this->Update_by]

                ]);
        return $dataProvider;
    }

}
