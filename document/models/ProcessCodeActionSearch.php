<?php

namespace app\modules\document\models;

use Yii;
use yii\data\ActiveDataProvider;

class ProcessCodeActionSearch extends ProcessCodeAction
{

    public $actionId;
    public $actionTitle;
    public $description;
    public $obsTitle;
    public $module;
    public $SecurityLevel;
    public $actionType;

    //--------
    public $processId;
    public $workflowActionId;
    public $workflowActionTitle;
    public $processTitle;

    public function rules()
    {
        return [
            [['actionId', 'actionTitle', 'obsTitle', 'OBSCategory3IDRef',
                'module', 'description', 'SecurityLevel', 'actionType','processId',
                'workflowActionId','workflowActionTitle','processTitle'], 'safe', 'on' => 'search'],
        ];
    }

    public static function getDb()
    {
        return \app\components\Utility::db();
    }

    public function search($params)
    {
        $activeQuery = \app\components\ActiveModel::find('app\modules\document\models\ProcessCodeAction')
            ->joinWith('action')
            ->where(['CodeIDRef' => $params['pcId']])
            ->andWhere(ProcessCodeAction::tableName() . ".ActionType = 'form'");

        $dataProvider = new ActiveDataProvider([
            'query' => $activeQuery,
//            'key' => 'ActionIDRef',
            'pagination' => [
                'pageSize' => 5000,
            ],
        ]);

        if ((!$this->load($params)) and $this->validate()) {
            return $dataProvider;
        }
        $activeQuery
            ->andFilterWhere(['LIKE', \app\modules\account\models\FormAction::tableName() . '.ActionID', $this->actionId])
            ->andFilterWhere(['LIKE', \app\modules\account\models\FormAction::tableName() . '.ActionTitle', $this->actionTitle])
            ->andFilterWhere([\app\modules\account\models\FormAction::tableName() . '.module' => $this->module])
            ->andFilterWhere(['LIKE', \app\modules\account\models\FormAction::tableName() . '.Description', $this->description])
            ->andFilterWhere([\app\modules\account\models\FormAction::tableName() . '.SecurityLevel' => $this->SecurityLevel])
            ->andFilterWhere(['LIKE', \app\modules\account\models\FormAction::tableName() . '.actionType', $this->actionType]);

        return $dataProvider;
    }

    public function searchActionProcess($aid, $params)
    {
        $processCodeActionTable = \app\modules\document\models\ProcessCodeActionSearch::tableName();

        $activeQuery = \app\modules\document\models\ProcessCodeAction::find()
            ->joinWith('processCode')
            ->joinWith('obs')
            ->where(['ActionIDRef' => $aid])
            ->andWhere("$processCodeActionTable.isActive = 1 or $processCodeActionTable.ActionType = 'form'");

        $dataProvider = new ActiveDataProvider([
            'query' => $activeQuery,
            'key' => 'CodeIDRef',
            'pagination' => [
                'pageSize' => 5000,
            ],
        ]);

        if ((!$this->load($params)) and $this->validate()) {
            return $dataProvider;
        }
        $activeQuery->andFilterWhere(['LIKE', 'CodeIDRef', $this->CodeIDRef])
            ->andFilterWhere(['LIKE', Obsnew::tableName() . '.obsTitle', $this->obsTitle]);
        return $dataProvider;
    }

    public function searchWorckflowAction($params)
    {
        $processTable = \app\modules\workflow\models\Process::tableName();

        $activeQuery = \app\components\ActiveModel::find('app\modules\document\models\ProcessCodeAction')
            ->joinWith('workflowAction', 'workflowAction.process')
            ->where(['CodeIDRef' => $params['pcId']])
            ->andWhere(ProcessCodeAction::tableName() . ".ActionType = 'workflow'");

        $dataProvider = new ActiveDataProvider([
            'query' => $activeQuery,
            'pagination' => [
                'pageSize' => 5000,
            ],
        ]);

        if ((!$this->load($params)) and $this->validate()) {
            return $dataProvider;
        }
        $activeQuery->andFilterWhere(['LIKE', 'ActionIDRef', $this->workflowActionId])
            ->andFilterWhere(['LIKE', \app\modules\workflow\models\Action::tableName() . '.ActionTitle1', $this->workflowActionTitle])
            ->andFilterWhere(['LIKE', "$processTable.ProcessID", $this->processId])
            ->andFilterWhere(['LIKE', "$processTable.ProcessTitle", $this->processTitle]);
        return $dataProvider;
    }

}
