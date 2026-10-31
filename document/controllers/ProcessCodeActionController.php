<?php

namespace app\modules\document\controllers;

use Yii;
use yii\helpers\Html;

/**
 * Description of ProcessCodeActionController
 *
 * @author Reza_Jahan
 */
class ProcessCodeActionController extends \yii\web\Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['list', 'create', 'delete'],
                'rules' => [
                    [
                        'actions' => ['list', 'create', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action, $exteraData = []) {
                            return \app\access\AccessControl::isAccess([
                                'or',
                                ['formAction', 'aid' => 418],
                                ['obs', 'check' => 'isCandoAdmin'],
                            ]);
                        },
                    ],
                ],
            ],
        ];
    }

    // Form Action Method :

    public function actionList($pcId)
    {

        $processCodeModel = \app\modules\document\models\ImsDocumentProcessCode::find()->where(['id' => $pcId])->one();
        //-----------
        $processCodeActionSearchModel = new \app\modules\document\models\ProcessCodeActionSearch();
        $processCodeActionSearchModel->setScenario('search');
        $processCodeActionDataProvider = $processCodeActionSearchModel->search(Yii::$app->request->queryParams);


        return $this->render('list', [
            'processCodeModel' => $processCodeModel,
            'processCodeActionSearchModel' => $processCodeActionSearchModel,
            'processCodeActionDataProvider' => $processCodeActionDataProvider,
            'pcId' => $pcId,
        ]);
    }

    public function actionCreate($pcId)
    {

        $formActionSearchModel = new \app\modules\account\models\FormActionSearch();
        $formActionSearchModel->setScenario('search');
        $formActionDataProvider = $formActionSearchModel->search(Yii::$app->request->queryParams);

        $formActionDataProvider->pagination->pageSize = 10000;

        $params = Yii::$app->request->queryParams;
        $selectedActions = isset($params['actions']) ? $params['actions'] : null;
        $saveRes = false;

        if (count($selectedActions) > 0) {
            $oldProcessCodeActions = \app\modules\document\models\ProcessCodeAction::find()
                ->where(['CodeIDRef' => $pcId])
                ->andWhere(\app\modules\document\models\ProcessCodeAction::tableName() . ".ActionType = 'form' ")
                ->asArray()->all();
            // جلوگیری از درج تکراری رکورد
            for ($i = 0; $i < count($selectedActions); $i++) {
                $find = false;
                for ($j = 0; $j < count($oldProcessCodeActions); $j++) {
                    if ($selectedActions[$i] == $oldProcessCodeActions[$j]['ActionIDRef']) {
                        $find = true;
                    }
                }
                if (!$find) {
                    // add new item
                    $ProcessCodeAction = new \app\modules\document\models\ProcessCodeAction();
                    $ProcessCodeAction->CodeIDRef = $pcId;
                    $ProcessCodeAction->ActionIDRef = $selectedActions[$i];
                    $ProcessCodeAction->ActionType = 'form';
                    if ($ProcessCodeAction->save()) {
                        $saveRes = true;
                    } else {
                        $saveRes = false;
                    }
                }
            }
            if (!$saveRes) {
                Yii::$app->session->setFlash('warning', 'The selected items are duplicates!');
            }
        }
        if ($saveRes) {
            return 'true*Selected action(s) successfully added to Process Code.';
        }

        return $this->renderAjax('_actionList', [
            'formActionSearchModel' => $formActionSearchModel,
            'formActionDataProvider' => $formActionDataProvider,
            'pcId' => $pcId,
        ]);
    }

    public function actionDelete($pcId)
    {

        echo "Delete $pcId";
        return false;

        $params = Yii::$app->request->queryParams;
        $res = true;
        if (isset($params['actions'])) {
            $transaction = Yii::$app->dbTehran->beginTransaction();
            $userObsFormActionIds = "";
            $actionIds = "";
            foreach ($params['actions'] as $key) {
                $actionIds .= $key . ', ';
                //delete action from obs
                $obsFormActionModel = \app\modules\OBS\models\ObsFormAction::find()
                    ->andWhere(\app\modules\OBS\models\ObsFormAction::tableName() . '.isDeleted is null '
                        . 'or ' . \app\modules\OBS\models\ObsFormAction::tableName() . '.isDeleted != 1')
                    ->andWhere("ActionIDRef=:key")
                    ->andWhere("OBSCategory3IDRef=:obsId")
                    ->addParams([':key' => $key, ':obsId' => $obsId])
                    ->one();
                if ($obsFormActionModel) {
                    $obsFormActionModel->isDeleted = 1;
                    $obsFormActionModel->hasChange = 1;
                    if ($obsFormActionModel->save()) {
                        //delete action from related user
                        $arrRelatedUsers = \app\modules\account\models\UserObs::find()
                            ->joinWith(['user', 'obs'])
                            ->where(['OBSCategory3IDRef' => $obsId])
                            ->asArray()
                            ->all();
                        $strRelatedUserIds = '';
                        foreach ($arrRelatedUsers as $item) {
                            $strRelatedUserIds .= (($strRelatedUserIds == '') ? '' : ',') . $item['UserIDRef'];
                        }

                        $userObsFormActionTable = \app\modules\account\models\UserOBSFormAction::tableName();
                        $userObsFormActionQuery = (new \yii\db\Query())
                            ->select(["strIds" => "GROUP_CONCAT(ID)"])
                            ->from($userObsFormActionTable)
                            ->where("UserIDRef in ($strRelatedUserIds)")
                            ->andWhere(['OBSCategory3IDRef' => $obsId, 'FormActionIDRef' => $key])
                            ->andWhere("$userObsFormActionTable.isDeleted is null or $userObsFormActionTable.isDeleted != 1")
                            ->one();

                        $userObsFormActionIds .= $userObsFormActionQuery['strIds'];

                        $res = \app\modules\account\models\UserOBSFormAction::updateAll([
                            'isDeleted' => 1,
                            'hasChange' => 1,
                        ], (($strRelatedUserIds != '') ? "UserIDRef in ($strRelatedUserIds) and" : '') .
                            " OBSCategory3IDRef = :obsId " .
                            "and FormActionIDRef = :key and (isDeleted is null or isDeleted != 1)", [':obsId' => $obsId, ':key' => $key]);
                        if (!$res) {
                            $transaction->rollback();
                            $res = false;
                            break;
                        }
                    } else {
                        $transaction->rollback();
                        $res = false;
                        break;
                    }
                }
            }


            $transaction->commit();
        }
        if ($res) {
            return 'true*Action(s) has been deleted';
        } else {
            return 'false*dose not delete Action(s)';
        }
    }


    //Workflow Action Method :

    public function actionWorkflowActionList($pcId)
    {
        $pcId = Html::encode($pcId);
        $processCodeModel = \app\modules\document\models\ImsDocumentProcessCode::find()->where(['id' => $pcId])->one();
        //-----------------
        $processCodeActionSearchModel = new \app\modules\document\models\ProcessCodeActionSearch();
        $processCodeActionSearchModel->setScenario('search');
        $processCodeActionDataProvider = $processCodeActionSearchModel->searchWorckflowAction(Yii::$app->request->queryParams);


        return $this->render('workflowActionList', [
            'processCodeModel' => $processCodeModel,
            'processCodeActionSearchModel' => $processCodeActionSearchModel,
            'processCodeActionDataProvider' => $processCodeActionDataProvider,
            'pcId' => $pcId,
        ]);

    }

    public function actionWorkflowActionCreate($pcId)
    {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }
        $actionSearchModel = new \app\modules\workflow\models\ActionSearch();
        $actionSearchModel->setScenario('search');
        $actionDataProvider = $actionSearchModel->search(Yii::$app->request->queryParams);
        $actionDataProvider->pagination->pageSize = 10000;

        if (Yii::$app->request->isPost) {
            $processlist = Yii::$app->request->post('items');
            foreach ($processlist as $key => $value) {
                $pcPaction = \app\modules\document\models\ProcessCodeAction::find()->where("CodeIDRef=:pcId and ActionIDRef=:value")
                    ->andWhere(\app\modules\document\models\ProcessCodeAction::tableName() . ".ActionType = 'workflow' ")->
                    addParams([':pcId' => $pcId, ':value' => $value])
                    ->one();
                if (empty($pcPaction)) {
                    $processCodeActionModel = new \app\modules\document\models\ProcessCodeAction();
                    $processCodeActionModel->CodeIDRef = $pcId;
                    $processCodeActionModel->ActionIDRef = $value;
                    $processCodeActionModel->ActionType = 'workflow';
                    if ($processCodeActionModel->validate()) {

                        if ($processCodeActionModel->save()) {
                            $processCodeActionTable = \app\modules\document\models\ProcessCodeAction::tableName();
                            $activityManagement = new \app\components\ActivityManagement();
                            $actionType = "New";
                            $subType = "NewProcessCodeAction";
                            $subTypeDescription["TableName"] = $processCodeActionTable;
                            $subTypeDescription["RecordPrimaryKey"] = $processCodeActionModel->id;
                            $activityManagement->sendActitvityLog($actionType, $subType, $subTypeDescription);
                        }
                    }
                }
            }
            return 1;
        }

        return $this->renderAjax('_processActionList', [
            'actionModel' => $actionSearchModel,
            'actionDataProvider' => $actionDataProvider,
            'pcId' => $pcId,
        ]);
    }

    public function actionWorkflowActionDelete($id) {

        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPost) {
            throw new \yii\web\BadRequestHttpException('Invalid request type.');
        }

        $processCodeActionModel = \app\modules\document\models\ProcessCodeAction::find()->where(['id' => $id])
            ->one();
        if (!$processCodeActionModel){
            Yii::$app->session->setFlash('error','the request Process Action does not exist.');
            $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['document/process-code-action/workflow-action-list', 'pcId' => $processCodeActionModel->CodeIDRef]));
            Yii::$app->end();
        }

        if ($processCodeActionModel) {

            if ($processCodeActionModel->delete()) {

                Yii::$app->session->setFlash('success', 'Process Action has been deleted');
            } else {
                Yii::$app->session->setFlash('error', 'Have a problem on deleting Process Action!');
            }
        }
        $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['document/process-code-action/workflow-action-list', 'pcId' => $processCodeActionModel->CodeIDRef]));
        Yii::$app->end();
    }

}
