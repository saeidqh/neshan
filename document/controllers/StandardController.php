<?php

namespace app\modules\document\controllers;

use Yii;
use yii\web\Controller;
use app\modules\document\models\Standard;

class StandardController extends Controller {

    public function behaviors() {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['list', 'create', 'update'],
                'rules' => [
                    [
                        'actions' => ['list', 'create', 'update'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action, $exteraData = []) {
                            return \app\access\AccessControl::isAccess(['or',
                                ['formAction', 'aid' => 449],
                                ['obs', 'check' => 'isCandoAdmin']
                            ]);
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionList() {
        $standardSearchModel = new \app\modules\document\models\StandardSearch();
        $standardSearchModel->setScenario('search');
        $standardDataProvider = $standardSearchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
                    'standardSearchModel' => $standardSearchModel,
                    'standardDataProvider' => $standardDataProvider,
        ]);
    }

    public function actionCreate() {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }
        $standardModel = new Standard();
        if ($standardModel->load(Yii::$app->request->post())) {
            $standardModel->id = $standardModel->find()->max('id') + 1;

            if ($standardModel->validate()) {
                if ($standardModel->save()) {
                    return \app\components\Utility::renderPjaxCallBack('standardCallbackPjax', [
                                'result' => true,
                                'message' => Yii::t('document', 'Standard successfully Added.')
                    ]);
                } else {
                    return \app\components\Utility::renderPjaxCallBack('standardCallbackPjax', [
                                'result' => false,
                                'message' => Yii::t('document', 'Error on saving Standard.')
                    ]);
                }
            }
        }

        return $this->renderAjax('_Form', [
                    'standardModel' => $standardModel
        ]);
    }

    public function actionUpdate($id) {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }

        $standardModel = Standard::find()->where(['id' => $id])->one();

        if ($standardModel->load(Yii::$app->request->post())) {
            if ($standardModel->validate()) {
                if ($standardModel->save()) {
                    return \app\components\Utility::renderPjaxCallBack('standardCallbackPjax', [
                                'result' => true,
                                'message' => Yii::t('automation', 'Update Standard successfully .')
                    ]);
                } else {

                    return \app\components\Utility::renderPjaxCallBack('standardCallbackPjax', [
                                'result' => false,
                                'message' => Yii::t('automation', 'Error on Update Standard!.')
                    ]);
                }
            }
        }
        return $this->renderAjax('_Form', [
                    'standardModel' => $standardModel
        ]);
    }
}
