<?php

namespace app\modules\document\controllers;

use Yii;
use app\components\jDateTime;
use yii\web\Controller;
use yii\db\Expression;
use app\modules\document\models\StandardRequest;

class StandardRequestController extends Controller {

    public function behaviors() {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['list', 'create', 'update'],
                'rules' => [
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['@'],
                        /*'matchCallback' => function ($rule, $action, $exteraData = []) {
                            return \app\access\AccessControl::isAccess([
                                'or',
                                ['obs', 'check' => 'isCandoAdmin'],
                                ['formAction', 'aid' => 449],
                                ['formAction', 'aid' => 447],
                                ['formAction', 'aid' => 451]
                            ]);
                        },*/
                    ],
                    [
                        'actions' => ['create', 'update'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action, $exteraData = []) {
                            return \app\access\AccessControl::isAccess([
                                'or',
                                ['obs', 'check' => 'isCandoAdmin'],
                                ['formAction', 'aid' => 449],
                                ['formAction', 'aid' => 447]
                            ]);
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionList() {
        $companyQuery = \app\modules\OBS\models\Person::find()->select(['PersonID', 'companyName'])
                ->where(['in', 'PersonID', \app\modules\OBS\models\Company::find()->select('companyIdRef')->distinct()]);

        $companyList = \yii\helpers\ArrayHelper::map($companyQuery->asArray()->all(), 'PersonID', 'companyName');

        $standardSearchModel = new \app\modules\document\models\StandardRequestSearch();
        $standardDataProvider = $standardSearchModel->search(Yii::$app->request->queryParams);
        $standardDataProvider->sort->defaultOrder = ['id' => SORT_DESC];

        return $this->render('list', [
                    'companyList' => $companyList,
                    'standardSearchModel' => $standardSearchModel,
                    'standardDataProvider' => $standardDataProvider,
        ]);
    }

    public function actionCreate() {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }
        $standardRequestModel = new StandardRequest();
        if ($standardRequestModel->load(Yii::$app->request->post())) {
            $jdate = new jDateTime(true, true, 'Asia/Tehran');
            $standardRequestModel->createdBy = Yii::$app->session->get('UserID');
            $standardRequestModel->requestDate = $jdate->date("Y/m/d", false, false);
            if ($standardRequestModel->validate()) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($standardRequestModel->save()) {
                        $srId = $standardRequestModel->id;
                        $clauseTable = \app\modules\document\models\Clause::tableName();
                        $personTable = \app\modules\OBS\models\Person::tableName();
                        $clauseActivityTable = \app\modules\document\models\ClauseActivity::tableName();
                        $standardClauseTable = \app\modules\document\models\StandardClause::tableName();
                        $standardRequestActivityTable = \app\modules\document\models\StandardRequestActivity::tableName();

                        $activeQuery = \app\modules\document\models\ClauseActivity::find()
                                ->select(["$clauseActivityTable.id as clauseActivityIdRef", new Expression("$srId as standardRequestIdRef"), new Expression("'checking' as radehStatus"), new Expression("'checking' as holdingStatus")])
                                ->innerJoin($clauseTable, "$clauseTable.id = $clauseActivityTable.clauseIdRef")
                                ->innerJoin($standardClauseTable, "$standardClauseTable.ClauseIdRef = $clauseTable.id")
                                ->where(["$standardClauseTable.StandardIdRef" => explode(',', $standardRequestModel->standard), "$clauseTable.isActive" => 1])
                                ->groupBy("$clauseActivityTable.id")
                                ->asArray()
                                ->all();
                        $activities = array_map('array_values', $activeQuery);
                        $columns = ['clauseActivityIdRef', 'standardRequestIdRef', 'radehStatus', 'holdingStatus'];
                        if (!empty($activities)) {
                            $res = Yii::$app->db->createCommand()->batchInsert($standardRequestActivityTable, $columns, $activities)->execute();
                        }
                        $transaction->commit();
                        return \app\components\Utility::renderPjaxCallBack('standardRequestCallbackPjax', [
                                    'result' => true,
                                    'message' => Yii::t('document', 'Standard Request successfully Added.')
                        ]);
                    } else {
                        return \app\components\Utility::renderPjaxCallBack('standardRequestCallbackPjax', [
                                    'result' => false,
                                    'message' => Yii::t('document', 'Error on saving Standard Request.')
                        ]);
                    }
                } catch (Exception $exc) {
                    $transaction->rollBack();
                    echo $exc->getTraceAsString();
                    return \app\components\Utility::renderPjaxCallBack('standardRequestCallbackPjax', [
                                'result' => false,
                                'message' => Yii::t('document', 'Error on saving Standard Request.')
                    ]);
                }
            }
        }

        return $this->renderAjax('_Form', [
                    'standardRequestModel' => $standardRequestModel
        ]);
    }

    public function actionUpdate($id) {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }
        $standardRequestModel = StandardRequest::find()->where(['id' => $id])->one();
        $oldIsLock = $standardRequestModel->isLock;
        $oldHoldingDate = $standardRequestModel->holdingDate;
        if(!\app\components\Security::isCandoAdmin() && !empty($standardRequestModel->managerialDate)) {
            $standardRequestModel->setScenario('holdingUpdate');
        }

        if ($standardRequestModel->load(Yii::$app->request->post())) {
            if ($standardRequestModel->validate()) {
                if ($standardRequestModel->holdingVerify) {
                    if (empty($oldHoldingDate)) {
                        $jdate = new jDateTime(true, true, 'Asia/Tehran');
                        $standardRequestModel->holdingDate = $jdate->date("Y/m/d", false, false);
                        $standardRequestModel->holdingUserIdRef = Yii::$app->session->get('UserID');
                    }
                } else {
                    $standardRequestModel->holdingDate = null;
                    $standardRequestModel->holdingUserIdRef = null;
                }
                if ($standardRequestModel->save()) {
                    if ($oldIsLock == 0 && $standardRequestModel->isLock == 1) {
                        $standardRequestActivitySubquery = \app\modules\document\models\StandardRequestActivity::find()->select('clauseActivityIdRef')->where(['standardRequestIdRef' => $standardRequestModel->id]);
                        \app\modules\document\models\CompanyActivityDocument::updateAll(
                            ['isActive' => 0],
                            ['companyIdRef' => $standardRequestModel->companyIdRef, 'activityIdRef' => $standardRequestActivitySubquery, 'isActive' => 1]
                        );
                    }
                    return \app\components\Utility::renderPjaxCallBack('standardRequestCallbackPjax', [
                                'result' => true,
                                'message' => Yii::t('automation', 'Update Standard Request successfully .')
                    ]);
                } else {
                    return \app\components\Utility::renderPjaxCallBack('standardRequestCallbackPjax', [
                                'result' => false,
                                'message' => Yii::t('automation', 'Error on Update Standard Request!.')
                    ]);
                }
            }
        } else {
            $standardRequestModel->holdingVerify = !empty($standardRequestModel->holdingDate);
            $standardRequestModel->standardArray = explode(',', $standardRequestModel->standard);
            $standardRequestModel->projectArray = explode(',', $standardRequestModel->projectIdRef);
        }
        return $this->renderAjax('_Form', [
                    'standardRequestModel' => $standardRequestModel
        ]);
    }

    public function actionManagerialVerified($id) {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $standardRequestModel = StandardRequest::find()->where(['id' => $id])->one();
        if (!$standardRequestModel || !empty($standardRequestModel->holdingDate)) {
            throw new \yii\web\BadRequestHttpException();
        }

        if (empty($standardRequestModel->managerialDate)) {
            $jdate = new \app\components\jDateTime(true, true, 'Asia/Tehran');
            $standardRequestModel->managerialUser = Yii::$app->session->get('UserID');
            $standardRequestModel->managerialDate = $jdate->date("Y/m/d", false, false);
        } else {
            $standardRequestModel->managerialUser = null;
            $standardRequestModel->managerialDate = null;
        }
        if ($standardRequestModel->save(false)) {
            return [
                'result' => true,
                'message' => 'Standard Request changed Verified successfully!',
            ];
        } else {
            return [
                'result' => false,
                'message' => 'Error in Verified Standard Request!',
            ];
        }
    }
}
