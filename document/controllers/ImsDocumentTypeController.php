<?php

namespace app\modules\document\controllers;

use app\modules\document\models\ImsDocumentType;
use Yii;
use yii\web\Controller;
use yii\helpers\Html;

/**
 * @see ProcessCodeController
 *
 *
 * @author <>
 */
class ImsDocumentTypeController extends Controller
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
                                ['formAction', 'aid' => 420],
                                ['formAction', 'aid' => 421],
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
                                ['formAction', 'aid' => 421],
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
        $imsDocumentTypeModel = new \app\modules\document\models\ImsDocumentTypeSearch();
        $imsDocumentTypeModel->setScenario('search');
        $imsDocumentTypeDataProvider = $imsDocumentTypeModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'imsDocumentTypeModel' => $imsDocumentTypeModel,
            'imsDocumentTypeDataProvider' => $imsDocumentTypeDataProvider,
        ]);
    }

    public function actionCreate()
    {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }
        $imsDocumentTypeModel = new ImsDocumentType();

        if ($imsDocumentTypeModel->load(Yii::$app->request->post())) {
            $imsDocumentTypeModel->ID = $imsDocumentTypeModel->find()->max('id') + 1;

            if ($imsDocumentTypeModel->validate()) {
                if ($imsDocumentTypeModel->save()) {
                    return \app\components\Utility::renderPjaxCallBack('documentTypeCallbackPjax', [
                        'result' => true,
                        'message' => Yii::t('document', 'Document Type successfully Added.')
                    ]);
                } else {
                    return \app\components\Utility::renderPjaxCallBack('documentTypeCallbackPjax', [
                        'result' => false,
                        'message' => Yii::t('document', 'Error on saving Document Type.')
                    ]);
                }
            }
        }

        return $this->renderAjax('_Form', [
            'imsDocumentTypeModel' => $imsDocumentTypeModel
        ]);

    }

    public function actionUpdate($id)
    {
        $id = Html::encode($id);
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }

        $imsDocumentTypeModel = ImsDocumentType::find()->where(['ID' => $id])->one();

        if ($imsDocumentTypeModel->load(Yii::$app->request->post())) {
            if ($imsDocumentTypeModel->validate()) {
                if ($imsDocumentTypeModel->save()) {
                    return \app\components\Utility::renderPjaxCallBack('documentTypeCallbackPjax', [
                        'result' => true,
                        'message' => Yii::t('document', 'Update Process Code successfully.')
                    ]);
                } else {

                    return \app\components\Utility::renderPjaxCallBack('documentTypeCallbackPjax', [
                        'result' => false,
                        'message' => Yii::t('document', 'Error on Update Process Code !.')

                    ]);
                }
            }
        }
        return $this->renderAjax('_Form', [
            'imsDocumentTypeModel' => $imsDocumentTypeModel
        ]);

    }
}
