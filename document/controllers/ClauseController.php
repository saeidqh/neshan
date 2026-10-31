<?php

namespace app\modules\document\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\modules\document\models\Clause;
use app\modules\document\models\ClauseActivity;

class ClauseController extends Controller {

    public function behaviors() {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['list', 'create', 'update', 'create-activity', 'standard-items', 'download-activity', 'delete-activity', 'delete-activity-file'],
                'rules' => [
                    [
                        'actions' => ['list', 'create', 'update', 'create-activity', 'update-activity', 'delete-activity', 'delete-activity-file', 'standard-items'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action, $exteraData = []) {
                            return \app\access\AccessControl::isAccess(['or',
                                ['formAction', 'aid' => 449],
                                ['obs', 'check' => 'isCandoAdmin']
                            ]);
                        },
                    ],
                    [
                        'actions' => ['download-activity'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action, $exteraData = []) {
                            return \app\access\AccessControl::isAccess(['or',
                                ['formAction', 'aid' => 447],
                                ['formAction', 'aid' => 449],
                                ['formAction', 'aid' => 451],
                                ['obs', 'check' => 'isCandoAdmin']
                            ]);
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionList() {
        $clauseSearchModel = new \app\modules\document\models\ClauseSearch();
        $clauseSearchModel->setScenario('search');
        $clauseDataProvider = $clauseSearchModel->search(Yii::$app->request->queryParams);
        
//        var_dump(count($clauseDataProvider->models));
//        die();

        return $this->render('list', [
                    'clauseSearchModel' => $clauseSearchModel,
                    'clauseDataProvider' => $clauseDataProvider,
        ]);
    }

    public function actionCreate() {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }
        $clauseModel = new Clause();
        if ($clauseModel->load(Yii::$app->request->post())) {
            $clauseModel->isActive = 1;
            $clauseModel->id = $clauseModel->find()->max('id') + 1;

            if ($clauseModel->validate()) {
                if ($clauseModel->saveClause()) {
                    return \app\components\Utility::renderPjaxCallBack('clauseCallbackPjax', [
                                'result' => true,
                                'message' => Yii::t('document', 'Clause successfully Added.')
                    ]);
                } else {
                    return \app\components\Utility::renderPjaxCallBack('clauseCallbackPjax', [
                                'result' => false,
                                'message' => Yii::t('document', 'Error on saving Clause.')
                    ]);
                }
            }
        }

        return $this->renderAjax('_Form', [
                    'clauseModel' => $clauseModel
        ]);
    }

    public function actionUpdate($id) {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }

        $clauseModel = Clause::find()->where(['id' => $id])->one();
        if ($clauseModel->load(Yii::$app->request->post())) {
            if ($clauseModel->validate()) {
                if ($clauseModel->saveClause()) {
                    return \app\components\Utility::renderPjaxCallBack('clauseCallbackPjax', [
                                'result' => true,
                                'message' => Yii::t('automation', 'Update Clause successfully .')
                    ]);
                } else {
                    return \app\components\Utility::renderPjaxCallBack('clauseCallbackPjax', [
                                'result' => false,
                                'message' => Yii::t('automation', 'Error on Update Clause!.')
                    ]);
                }
            }
        }
        $clauseModel->standard = ArrayHelper::getColumn($clauseModel->standards, 'id');

        return $this->renderAjax('_Form', [
                    'clauseModel' => $clauseModel
        ]);
    }

    public function actionDetails($clauseIdRef = null) {
        $clauseIdRef = \Yii::$app->request->post('expandRowKey');
        $clauseActivities = ClauseActivity::find()
                        ->where(['clauseIdRef' => $clauseIdRef])
                        ->andWhere(['isDeleted' => 0])
                        ->asArray()->all();

        $activityArrayDataProvider = new \yii\data\ArrayDataProvider([
            "allModels" => $clauseActivities,
            "totalCount" => count($clauseActivities),
        ]);

        return $this->renderAjax('_details', [
                    'clauseIdRef' => $clauseIdRef,
                    'activityArrayDataProvider' => $activityArrayDataProvider,
        ]);
    }

    public function actionStandardItems($id) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $clauseModel = Clause::find()->where(['id' => $id])->one();
        $standards = ArrayHelper::getColumn($clauseModel->standards, 'id');
        return $standards;
    }

    public function actionCreateActivity($clauseIdRef) {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }
        $clauseActivityModel = new ClauseActivity();
        if ($clauseActivityModel->load(Yii::$app->request->post())) {
            $clauseActivityModel->clauseIdRef = $clauseIdRef;

            if ($clauseActivityModel->validate()) {
//                $clauseActivityModel->file = \yii\web\UploadedFile::getInstance($clauseActivityModel, "file");
                if ($clauseActivityModel->saveActivity()) {
                    return \app\components\Utility::renderPjaxCallBack('clauseActivityCallbackPjax', [
                                'result' => true,
                                'message' => Yii::t('document', 'Clause Activity successfully Added.')
                    ]);
                } else {
                    return \app\components\Utility::renderPjaxCallBack('clauseActivityCallbackPjax', [
                                'result' => false,
                                'message' => Yii::t('document', 'Error on saving Clause Activity.')
                    ]);
                }
            }
        }

        return $this->renderAjax('_ActivityForm', [
                    'clauseActivityModel' => $clauseActivityModel
        ]);
    }

    public function actionUpdateActivity($id) {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }

        $clauseActivityModel = ClauseActivity::find()->where(['id' => $id])->one();
        $oldSimilarActivity = $clauseActivityModel->similarActivity;
        if ($clauseActivityModel->load(Yii::$app->request->post())) {
            if ($clauseActivityModel->validate()) {
                if ($clauseActivityModel->saveActivity($oldSimilarActivity)) {
                    return \app\components\Utility::renderPjaxCallBack('clauseActivityCallbackPjax', [
                                'result' => true,
                                'message' => Yii::t('automation', 'Update Clause Activity successfully .')
                    ]);
                } else {
                    return \app\components\Utility::renderPjaxCallBack('clauseActivityCallbackPjax', [
                                'result' => false,
                                'message' => Yii::t('automation', 'Error on Update Clause Activity!.')
                    ]);
                }
            }
        } else {
            $clauseActivityModel->extentionArray = !empty($clauseActivityModel->extention) ? explode(',', $clauseActivityModel->extention) : null;
            $clauseActivityModel->similarActivityArray = !empty($clauseActivityModel->similarActivity) ? explode(',', $clauseActivityModel->similarActivity) : null;
        }

        return $this->renderAjax('_ActivityForm', [
                    'clauseActivityModel' => $clauseActivityModel
        ]);
    }

    public function actionDeleteActivity($id) {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $clauseActivityModel = \app\modules\document\models\ClauseActivity::find()
                ->where(['id' => Html::encode($id), 'isDeleted' => 0])
                ->one();
        if (!isset($clauseActivityModel)) {
            throw new \yii\web\HttpException("document not found!!", 404);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $clauseActivityModel->isDeleted = 1;
            if (empty($clauseActivityModel->similarActivity) && $clauseActivityModel->save(false)) {
                $transaction->commit();
                return [
                    'result' => true,
                    'message' => 'Activity has Deleted Succeessfully.',
                ];
            }

            $similarActivityArray = explode(',', $clauseActivityModel->similarActivity);
            foreach ($similarActivityArray as $similarActivity) {
                $clauseActivity = ClauseActivity::find()->where(['id' => $similarActivity])->one();
                $clauseActivity->similarActivity = implode(',', array_diff(explode(',', $clauseActivity->similarActivity), [$id]));
                $clauseActivity->save(false);
            }

            $clauseActivityModel->similarActivity = null;
            if ($clauseActivityModel->save(false)) {
                return [
                    'result' => true,
                    'message' => 'Activity has Deleted Succeessfully.',
                ];
            }
            return [
                'result' => false,
                'message' => 'Error on Deleted Activity.',
            ];
        } catch (Exception $exc) {
            return [
                'result' => false,
                'message' => 'Error on Deleted Activity.',
            ];
        }
    }

    public function actionDownloadActivity($id) {
        $id = Html::encode($id);
        $clauseActivityModel = \app\modules\document\models\ClauseActivity::find()
                ->where(['id' => $id])
                ->one();
        if (!isset($clauseActivityModel->fileName)) {
            throw new \yii\web\HttpException("document not found!!", 404);
        }

        $clauseActivityModel->setConfigDoc([
            'selectedFileName' => $clauseActivityModel->fileName,
        ]);
        $clauseActivityModel->download();
    }

    public function actionDeleteActivityFile($id) {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $clauseActivityModel = \app\modules\document\models\ClauseActivity::find()
                ->where(['id' => Html::encode($id)])
                ->one();
        if (!isset($clauseActivityModel->fileName)) {
            throw new \yii\web\HttpException("document not found!!", 404);
        }
        $clauseActivityModel->fileName = null;
        if ($clauseActivityModel->hardDeleteFile() && $clauseActivityModel->save()) {
            return [
                'result' => true,
                'message' => 'File has Deleted Succeessfully.',
            ];
        }
        return [
            'result' => false,
            'message' => 'Error on Deleted File.',
        ];
    }
}
