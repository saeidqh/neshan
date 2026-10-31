<?php

namespace app\modules\document\controllers;

use Yii;
use app\components\jDateTime;
use yii\web\Controller;
use app\modules\document\models\StandardRequestActivity;

class StandardRequestActivityController extends Controller {

    public function behaviors() {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['list', 'update', 'hilight', 'delete-document'],
                'rules' => [
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['update', 'hilight', 'delete-document'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action, $exteraData = []) {
                            return \app\access\AccessControl::isAccess([
                                'or',
                                ['obs', 'check' => 'isCandoAdmin'],
                                ['formAction', 'aid' => 449],
                                ['formAction', 'aid' => 447],
                                ['formAction', 'aid' => 451],
                            ]);
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionList($standardRequestIdRef = null ) {
        $standardRequestIdRef = \Yii::$app->request->post('expandRowKey');
        $companyQuery = \app\modules\OBS\models\Person::find()->select(['PersonID', 'companyName'])
                ->where(['in', 'PersonID', \app\modules\OBS\models\Company::find()->select('companyIdRef')->distinct()]);

        $companyList = \yii\helpers\ArrayHelper::map($companyQuery->asArray()->all(), 'PersonID', 'companyName');

        $standardRequestActivitySearchModel = new \app\modules\document\models\StandardRequestActivitySearch();
        $standardRequestActivityDataProvider = $standardRequestActivitySearchModel->search($standardRequestIdRef, Yii::$app->request->queryParams);
        $standardRequestActivityDataProvider->sort->defaultOrder = ['year' => SORT_DESC, 'fullClauseNo' => SORT_ASC];

        /*$standardRequestActivitySearchModel = new \app\modules\document\models\ClauseActivitySearch();
        $standardRequestActivityDataProvider = $standardRequestActivitySearchModel->searchStandardRequest(Yii::$app->request->queryParams);
        $standardRequestActivityDataProvider->sort->defaultOrder = ['year' => SORT_DESC, 'fullClauseNo' => SORT_ASC];*/

        return $this->renderAjax('list', [
                    'companyList' => $companyList,
                    'standardRequestIdRef' => $standardRequestIdRef,
                    'standardRequestActivitySearchModel' => $standardRequestActivitySearchModel,
                    'standardRequestActivityDataProvider' => $standardRequestActivityDataProvider,
        ]);
    }

    public function actionUpdate($srId, $caId, $id = null) {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }
        $jdate = new jDateTime(true, true, 'Asia/Tehran');
        if ($id > 0) {
            $standardRequestActivityModel = StandardRequestActivity::find()->where(['id' => $id])->one();
        } else {
            $standardRequestActivityModel = new StandardRequestActivity();
            $standardRequestActivityModel->radehStatus = 'checking';
            $standardRequestActivityModel->holdingStatus = 'checking';
            $standardRequestActivityModel->standardRequestIdRef = $srId;
            $standardRequestActivityModel->clauseActivityIdRef = $caId;
        }
        $oldRadehStatus = $standardRequestActivityModel->radehStatus;
        $oldHoldingStatus = $standardRequestActivityModel->holdingStatus;

        if ($standardRequestActivityModel->load(Yii::$app->request->post())) {
            $standardRequestActivityModel->file = \yii\web\UploadedFile::getInstance($standardRequestActivityModel, "file");
            if ($standardRequestActivityModel->file instanceof \yii\web\UploadedFile) {
                $standardRequestActivityModel->fileName = $standardRequestActivityModel->file->name;
            }
            if ($standardRequestActivityModel->validate()) {
                if ($id == null || $oldRadehStatus == 'checking') {
                    $standardRequestActivityModel->radehUserIdRef = Yii::$app->session->get('UserID');
                    $standardRequestActivityModel->radehUpdateDate = $jdate->date("Y/m/d", false, false);
                    if ($standardRequestActivityModel->radehStatus == 'verify') {
                        $standardRequestActivityModel->holdingStatus = 'checking';
                    }
                } else {
                    if ($standardRequestActivityModel->holdingStatus == 'back') {
                        $standardRequestActivityModel->radehStatus = 'checking';
                    }
                    $standardRequestActivityModel->holdingUserIdRef = Yii::$app->session->get('UserID');
                    $standardRequestActivityModel->holdingUpdateDate = $jdate->date("Y/m/d", false, false);
                }
                if ($standardRequestActivityModel->radehStatus == 'verify' && $standardRequestActivityModel->radehStatus != $oldRadehStatus) {
                    $standardRequestActivityModel->color = "#b3f5b6ff";
                } else if ($standardRequestActivityModel->holdingStatus == 'verify' && $standardRequestActivityModel->holdingStatus != $oldHoldingStatus) {
                    $standardRequestActivityModel->color = "#2aee33ff";
                } else if ($standardRequestActivityModel->radehStatus == 'checking' && $standardRequestActivityModel->radehStatus != $oldRadehStatus) {
                    $standardRequestActivityModel->color = null;
                } else if($standardRequestActivityModel->radehStatus == 'verify' && $standardRequestActivityModel->holdingStatus == 'checking' && $standardRequestActivityModel->holdingStatus != $oldHoldingStatus) {
                    $standardRequestActivityModel->color = "#b3f5b6ff";
                }

                if ($standardRequestActivityModel->saveRequestActivity()) {
                    return \app\components\Utility::renderPjaxCallBack('standardRequestActivityCallbackPjax', [
                                'result' => true,
                                'message' => Yii::t('automation', 'Update Standard Request successfully .')
                    ]);
                } else {
                    return \app\components\Utility::renderPjaxCallBack('standardRequestActivityCallbackPjax', [
                                'result' => false,
                                'message' => Yii::t('automation', 'Error on Update Standard Request!.')
                    ]);
                }
            }
        } else {
            $standardRequestActivityModel->loadDocument();
        }

        $imsDocumentTable = \app\modules\document\models\ImsDocument::tableName();
        $imsQuery = \app\modules\document\models\ImsDocument::find()
                ->addSelect([
                    "id" => "$imsDocumentTable.id",
                    "imsTitle" => "CONCAT($imsDocumentTable.autoCode,' (',$imsDocumentTable.title,')')",
                ])
                ->where(["$imsDocumentTable.status" => 1])
                ->andWhere(["$imsDocumentTable.companyIdRef" => $standardRequestActivityModel->standardRequest->companyIdRef])
                ->andWhere("$imsDocumentTable.isDeleted is null or $imsDocumentTable.isDeleted <>1");

        $mainIMSArray = \yii\helpers\ArrayHelper::map($imsQuery->asArray()->all(), 'id', 'imsTitle');

        $clauseActivityModel = \app\modules\document\models\ClauseActivity::find()->where(['id' => $caId])->one();            
        return $this->renderAjax('_Form', [
                    'mainIMSArray' => $mainIMSArray,
                    'clauseActivityModel' => $clauseActivityModel,
                    'standardRequestActivityModel' => $standardRequestActivityModel
        ]);
    }

    public function actionDeleteDocument($id) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $standardRequestActivityModel = StandardRequestActivity::find()->where(['id' => $id])->one();

        $standardRequestActivityModel->fileName = null;
        if ($standardRequestActivityModel->save(false)) {
            $standardRequestActivityModel->hardDeleteFile();
            return [
                'result' => 1,
                'message' => 'Document deleted successfully',
            ];
        } else {
            return [
                'result' => 0,
                'message' => 'Error in deleting Document',
            ];
        }
    }

    public function actionHilight($srId, $caId, $color, $id = null) {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if ($id > 0) {
            $standardRequestActivityModel = StandardRequestActivity::find()->where(['id' => $id])->one();
        } else {
            $standardRequestActivityModel = new StandardRequestActivity();
            $standardRequestActivityModel->radehStatus = 'checking';
            $standardRequestActivityModel->holdingStatus = 'checking';
            $standardRequestActivityModel->standardRequestIdRef = $srId;
            $standardRequestActivityModel->clauseActivityIdRef = $caId;
        }
        if($color == 'remove') {
            if($standardRequestActivityModel->holdingStatus == 'verify') {
                $color = "2aee33ff";
            } else if($standardRequestActivityModel->radehStatus == 'verify') {
                $color = "b3f5b6ff";
            } else  {
                $color = null;
            }
        }
        $standardRequestActivityModel->color = $color;
        // $standardRequestActivityModel->hilight = $standardRequestActivityModel->hilight ? 0 : 1;
        if ($standardRequestActivityModel->save(false)) {
            return [
                'result' => true,
                'message' => 'Standard Request Activity changed hilight successfully!',
            ];
        } else {
            return [
                'result' => false,
                'message' => 'Error in hilight StandardRequestActivity!',
            ];
        }
    }

    public function actionDownload($id) {
        if (empty($id)) {
            throw new \yii\web\BadRequestHttpException();
        }

        $companyActivityDocumentModel = \app\modules\document\models\CompanyActivityDocument::find()->where(['id' => $id])->one();
        if (!isset($companyActivityDocumentModel->id)) {
            throw new \yii\web\HttpException("Document not found", 404);
        }

        $companyActivityDocumentModel->setConfigDoc([
            'selectedFileName' => $companyActivityDocumentModel->fileName,
        ]);

        return $companyActivityDocumentModel->download();
    }
}
