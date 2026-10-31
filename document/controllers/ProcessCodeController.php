<?php

namespace app\modules\document\controllers;


use app\components\Security;
use app\modules\document\models\ImsDocumentProcessCode;
use Yii;
use yii\web\Controller;
use app\components\jDateTime;
use yii\helpers\Html;

/**
 * @see ProcessCodeController
 *
 *
 * @author <>
 */
class ProcessCodeController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['list', 'create', 'update'],
                'rules' => [
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action, $exteraData = []) {
                            return \app\access\AccessControl::isAccess([
                                'or',
                                ['formAction', 'aid' => 415],
                                ['formAction', 'aid' => 416],
                                ['obs', 'check' => 'isCandoAdmin'],
                            ]);
                        },
                    ],
                    [
                        'actions' => ['create', 'update'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action, $exteraData = []) {
                            return \app\access\AccessControl::isAccess([
                                'or',
                                ['formAction', 'aid' => 415],
                                ['obs', 'check' => 'isCandoAdmin'],
                            ]);
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionList()
    {
        $processCodeModel = new \app\modules\document\models\ImsDocumentProcessCodeSearch();
        $processCodeModel->setScenario('search');
        $processCodeDataProvider = $processCodeModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'processCodeModel' => $processCodeModel,
            'processCodeDataProvider' => $processCodeDataProvider,
        ]);
    }

    public function actionCreate()
    {

        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }
        $processCodeModel = new ImsDocumentProcessCode();

        if ($processCodeModel->load(Yii::$app->request->post())) {
            $processCodeModel->id = $processCodeModel->find()->max('id') + 1;

            if ($processCodeModel->validate()) {
                if ($processCodeModel->save()) {
                    return \app\components\Utility::renderPjaxCallBack('processCallbackPjax', [
                        'result' => true,
                        'message' => Yii::t('document', 'Process Code successfully Added.')
                    ]);
                } else {
                    return \app\components\Utility::renderPjaxCallBack('processCallbackPjax', [
                        'result' => false,
                        'message' => Yii::t('document', 'Error on saving Process Code.')
                    ]);
                }
            }
        }

        return $this->renderAjax('_Form', [
            'processCodeModel' => $processCodeModel
        ]);

    }

    public function actionUpdate($id)
    {
        $id = Html::encode($id);
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }

        $processCodeModel = ImsDocumentProcessCode::find()->where(['id' => $id])->one();

        if ($processCodeModel->load(Yii::$app->request->post())) {
            if ($processCodeModel->validate()) {
                if ($processCodeModel->save()) {
                    return \app\components\Utility::renderPjaxCallBack('processCallbackPjax', [
                        'result' => true,
                        'message' => Yii::t('document', 'Update Process Code successfully.')
                    ]);
                } else {

                    return \app\components\Utility::renderPjaxCallBack('processCallbackPjax', [
                        'result' => false,
                        'message' => Yii::t('document', 'Error on Update Process Code !.')

                    ]);
                }
            }
        }
        return $this->renderAjax('_Form', [
            'processCodeModel' => $processCodeModel
        ]);

    }


}
