<?php

namespace app\modules\document\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * @see StampDocumentSearch
 *
 * @author
 */
class StampDocumentSearch extends StampDocument
{

    public $Company;
    public $Position;
    public $lastUser;


    public function rules()
    {
        return [
            [['LastFlowIDRef','StampId', 'StampTitle', 'StampPurpose', 'Obs', 'StampTypeIDRef', 'StampCode',
                'RequestDate', 'lastUser', 'DestroyDate', 'Status', 'Company', 'Position','OrganizationalMaker', 'NationalCode', 'SerialNumber'], 'safe', 'on' => 'search'],
        ];
    }


    public static function getDb()
    {
        return \app\components\Utility::db();
    }

    public function search($params, $companyAllChildsArray)
    {

        $companyTable = \app\modules\OBS\models\Person::tableName();
        $obsNewTable = \app\modules\OBS\models\Obsnew::tableName();
        $stampTable = \app\modules\document\models\StampDocument::tableName();
        $userTable = \app\modules\account\models\User::tableName();
        $stampFlowTable = \app\modules\document\models\StampFlowDocument::tableName();
        $humanTable = \app\modules\automation\models\HumanResource::tableName();
        
        $activeQuery = StampDocument::find()
            ->leftJoin($stampFlowTable, "$stampFlowTable.FlowId = $stampTable.LastFlowIDRef")
            ->leftJoin($humanTable, "$humanTable.Id = $stampFlowTable.HumanIDRef")
            ->leftJoin("$humanTable as owner_human", "owner_human.Id = $stampTable.HumanResourceIDRef")
            ->leftJoin($obsNewTable, "$obsNewTable.obsId = $stampTable.ObsIDRef")
            ->leftJoin($companyTable, "$companyTable.PersonID = $obsNewTable.companyIdRef")
            ->leftJoin($userTable, "$userTable.UserID = $stampTable.LastUserIDRef")
            ->andWhere(['in', "$obsNewTable.companyIdRef", $companyAllChildsArray])
            ->andWhere(["$stampTable.isDeleted" => 0]);            // 2. سندهای حذف شده نمایش داده نشوند


        $dataProvider = new ActiveDataProvider([
            'query' => $activeQuery,
            'sort' => ['defaultOrder' => ['StampId' => SORT_DESC]],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
        $this->load($params);

        if ((!$this->load($params)) and $this->validate()) {
            return $dataProvider;
        }
        $activeQuery
            ->andFilterWhere(['LIKE', 'StampId', $this->StampId])
            ->andFilterWhere(['LIKE', 'OrganizationalMaker', $this->OrganizationalMaker])
            ->andFilterWhere(['LIKE', 'StampTitle', $this->StampTitle])
            ->andFilterWhere(['LIKE', 'StampCode', $this->StampCode])
            ->andFilterWhere(['LIKE', 'RequestDate', $this->RequestDate])
            ->andFilterWhere(['LIKE', 'DestroyDate', $this->DestroyDate])
            ->andFilterWhere(['LIKE', 'SerialNumber', $this->SerialNumber])
            ->andFilterWhere(['=', 'StampTypeIDRef', $this->StampTypeIDRef])
            ->andFilterWhere(['LIKE', $stampTable . '.Status', $this->Status])
            ->andFilterWhere(['LIKE', $obsNewTable . '.obsTitle', $this->Position])
            ->andFilterWhere(['LIKE', $companyTable . '.PersonID', $this->Company])
            ->andFilterWhere(
                ['or',
                    ['LIKE', "$stampTable.lastUpdate", $this->lastUser],
                    ['LIKE', "concat($userTable.FirstName, ' ', $userTable.LastName)", $this->lastUser]
                ])
            ->andFilterWhere(
                ['or',
                    ['LIKE', "$humanTable.NationalCode", $this->LastFlowIDRef],
                    ['LIKE', "concat($humanTable.FirstName, ' ', $humanTable.LastName)", $this->LastFlowIDRef]
                ])
                ->andFilterWhere(
                ['or',
                    ['LIKE', "owner_human.NationalCode", $this->NationalCode],
                    ['LIKE', "concat(owner_human.FirstName, ' ', owner_human.LastName)", $this->NationalCode]
            ]);
        return $dataProvider;


    }


}