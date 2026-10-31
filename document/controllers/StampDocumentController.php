<?php

namespace app\modules\document\controllers;


use app\components\Security;
use app\modules\document\models\ScopeTypeDocument;
use app\modules\document\models\StampDocument;
use app\modules\document\models\StampDocumentSearch;
use app\modules\document\models\StampFlowDocument;
use app\modules\document\models\StampScopeDocument;
use app\modules\document\models\StampRefundsSearch;
use Yii;
use yii\web\Controller;
use app\components\jDateTime;
use yii\helpers\Html;
use app\modules\workflow\models\ProcessEntity;
use app\modules\OBS\models\Obsnew;
use yii\helpers\Url;

/**
 * @see StampDocumentController
 *
 *
 * @author <>
 */
class StampDocumentController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['list', 'create', 'update', 'view', 'logo-image', 'refund','view2', 'create-workflow', 'update-workflow','delete','get-process-code','create-refund','view-refund','update-refund'],
                'rules' => [
                    [
                        'actions' => ['list', 'view', 'logo-image'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action, $exteraData = []) {
                            return \app\access\AccessControl::isAccess([
                                'or',
                                ['formAction', 'aid' => 408],
                                ['formAction', 'aid' => 412],
                                ['formAction', 'aid' => 413],
                                ['formAction', 'aid' => 448],
                                ['obs', 'check' => 'isCandoAdmin'],
                            ]);
                        },
                    ],
                    [
                        'actions' => ['create','get-process-code'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action, $exteraData = []) {
                            return \app\access\AccessControl::isAccess([
                                'or',
                                ['formAction', 'aid' => 408],
//                                ['formAction', 'aid' => 410],
                                ['obs', 'check' => 'isCandoAdmin'],
                            ]);
                        },
                    ],
                    [
                        'actions' => ['update','update-refund'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action, $exteraData = []) {
                            return \app\access\AccessControl::isAccess([
                                'or',
                                ['formAction', 'aid' => 408],
                                ['obs', 'check' => 'isCandoAdmin'],
                            ]);
                        },
                    ],
                    [
                        'actions' => ['refund'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action, $exteraData = []) {
                            return \app\access\AccessControl::isAccess([
                                'or',
                                ['formAction', 'aid' => 408],
                                ['formAction', 'aid' => 412],
                                ['obs', 'check' => 'isCandoAdmin'],
                            ]);
                        },
                    ],
                    [
                        'actions' => ['verified'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action, $exteraData = []) {
                            return \app\access\AccessControl::isAccess([
                                'or',
                                ['formAction', 'aid' => 408],
                                ['obs', 'check' => 'isCandoAdmin']
                            ]);
                        },
                    ],
                    [
                            'actions' => ['view2','view-refund'],
                            'allow' => true,
                            'roles' => ['@'],
                            'matchCallback' => function ($rule, $action, $exteraData = []) {
                                $peid = Yii::$app->request->get('peid');
                                $processEntityModel = \app\modules\workflow\models\ProcessEntity::find()
                                    ->where(['ProcessEntityID' => $peid])
                                    ->one();

                                return \app\access\AccessControl::isAccess([
                                    'or',
                                    ['processEntity', 'peid' => $peid, 'type' => 'All'],
                                    ['obs', 'check' => 'isCandoAdmin'],
                                ]);
                            },
                        ],
                                    
                         [
                    'actions' => ['create-workflow', 'update-workflow'],
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => function ($rule, $action, $exteraData = []) {
                        $firstActionId = \app\modules\workflow\models\Process::getFirstAction(85);
                        return \app\access\AccessControl::isAccess([
                            'or',
                            ['processAction', 'aid' => $firstActionId],
                            ['obs', 'check' => 'isCandoAdmin'],
                        ]);
                    },
                ],                         [
                    'actions' => ['create-refund'],
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => function ($rule, $action, $exteraData = []) {
                        $firstActionId = \app\modules\workflow\models\Process::getFirstAction(87);
                        return \app\access\AccessControl::isAccess([
                            'or',
                            ['processAction', 'aid' => $firstActionId],
                            ['obs', 'check' => 'isCandoAdmin'],
                        ]);
                    },
                ],
                     [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action, $exteraData = []) {
                            return \app\access\AccessControl::isAccess([
                                'or',
                               
                                ['obs', 'check' => 'isCandoAdmin']
                            ]);
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionList($companyId = null)
    {
        $userCompanyId = yii::$app->session->get('CompanyID');
        $companyAllChildsArray = \app\components\CandoHelper::companyChilds($userCompanyId, 'array');
        if (!isset($companyId) || (isset($companyId) && !in_array($companyId, $companyAllChildsArray) && !Security::isCandoAdmin())) {
            return $this->redirect(\Yii::$app->urlManager->createAbsoluteUrl(['document/stamp-document/list', 'companyId' => Yii::$app->session->get('CompanyID')]));
        }
        $stampSearchModel = new StampDocumentSearch();
        $stampSearchModel->setScenario('search');
        $stampDataProvider = $stampSearchModel->search(Yii::$app->request->queryParams, $companyAllChildsArray);

        $companyTable = \app\modules\OBS\models\Company::tableName();
        $companyList = \yii\helpers\ArrayHelper::map(\app\components\ActiveModel::find('\app\modules\OBS\models\Company')
            ->joinWith('company')
            ->andWhere(['in', "$companyTable.companyIdRef", $companyAllChildsArray])
            ->asArray()->all(), 'company.PersonID', 'company.PersonTitle');

        return $this->render('stampList', [
            'companyList' => $companyList,
            'stampDataProvider' => $stampDataProvider,
            'stampSearchModel' => $stampSearchModel

        ]);
    }

public function actionCreate()
{
    if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
        throw new \yii\web\BadRequestHttpException();
    }
    if (Yii::$app->request->isPost) {
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
}

    $stampModel = new StampDocument();

    $flowModel = new StampFlowDocument();
    $flowModel->setScenario('create');

    $internalScopes = \yii\helpers\ArrayHelper::map(
        \app\modules\document\models\ScopeTypeDocument::find()
            ->where(['ScopeType' => 'internal'])
            ->all(),
        'ScopeId',
        'ScopeTitle'
    );

    $externalScopes = \yii\helpers\ArrayHelper::map(
        \app\modules\document\models\ScopeTypeDocument::find()
            ->where(['ScopeType' => 'external'])
            ->all(),
        'ScopeId',
        'ScopeTitle'
    );

    $processCodeArray = \yii\helpers\ArrayHelper::map(
        \app\modules\document\models\ImsDocumentProcessCode::find()
            ->where(['isActive' => 1])
            ->addSelect(['id', 'code' => "concat(code, ' - ', IFNULL(PersianTitle,''))"])
            ->all(),
        'id',
        'code'
    );

    if ($stampModel->load(Yii::$app->request->post())) {

        /*
         * load اطلاعات بهره‌بردار
         */
        $flowModel->load(Yii::$app->request->post());

        /*
         * اطلاعات اصلی نشان
         */
        $stampModel->StampId = (int) StampDocument::find()->max('StampId') + 1;

        /*
         * وضعیت ثبت مستقیم را تغییر نمی‌دهیم
         */
        $stampModel->Status = StampDocument::STATUS_PENDING;

        /*
         * فایل تصویر اولیه نشان
         */
        $stampModel->StampFile = \yii\web\UploadedFile::getInstance($stampModel, "StampFile");

        if ($stampModel->StampFile instanceof \yii\web\UploadedFile) {
            $stampModel->StampDocument = $stampModel->StampFile->name;
        }

        /*
         * آماده‌سازی رکورد بهره‌بردار
         */
        $flowModel->FlowId = (int) StampFlowDocument::find()->max('FlowId') + 1;
        $flowModel->StampIDRef = $stampModel->StampId;

        /*
         * فقط فیلدهای لازم بهره‌بردار را validate می‌کنیم
         * اینجا SerialNumber و FinalStampFile عمداً validate نمی‌شوند
         */
        $flowValid = $flowModel->validate([
            'NationalCode',
            'ObsIDRef',
            'ReceivedDate',
        ]);

        /*
         * پیدا کردن بهره‌بردار از روی کد ملی
         */
        if ($flowValid) {
            $humanResource = \app\modules\automation\models\HumanResource::find()
                ->where(['NationalCode' => $flowModel->NationalCode])
                ->one();

            if (!$humanResource) {
                $flowModel->addError('NationalCode', 'شخصی با این کد ملی یافت نشد!');
                $flowValid = false;
            } elseif ((int)$humanResource->Verified !== 1 || $humanResource->Status !== 'Employment') {
                $flowModel->addError('NationalCode', 'اشتغال شخص توسط گزینش منابع انسانی هلدینگ تایید نشده است.');
                $flowValid = false;
            } else {
                $flowModel->HumanIDRef = $humanResource->Id;

                /*
                 * فقط برای اینکه اگر فرم دوباره render شد، اطلاعات نمایشی خالی نباشد
                 */
                $flowModel->FirstName = $humanResource->FirstName;
                $flowModel->LastName = $humanResource->LastName;
                $flowModel->EmploymentType = $humanResource->EmploymentType;
            }
        }

        /*
         * validate اطلاعات اصلی نشان
         */
        $stampValid = $stampModel->validate();

if (!$stampValid || !$flowValid) {
    return [
        'result' => false,
        'message' => 'خطا در اعتبارسنجی اطلاعات!',
        'errors' => [
            'stamp' => $stampModel->getErrors(),
            'flow' => $flowModel->getErrors(),
        ],
    ];
}

        $transaction = \app\components\Utility::centralDb()->beginTransaction();

        try {

            /*
             * 1. ذخیره خود نشان
             */
            $stampSaved = $stampModel->saveStamp(
                $stampModel->internalScopeIds,
                $stampModel->externalScopeIds
            );

            if (!$stampSaved) {
                throw new \Exception(Yii::t('document', 'Error on adding Stamp !.'));
            }

            /*
             * 2. ذخیره بهره‌بردار
             */
            $flowModel->StampIDRef = $stampModel->StampId;

            if (!$flowModel->save(false)) {
                throw new \Exception('خطا در ذخیره اطلاعات بهره‌بردار.');
            }

            /*
             * 3. اتصال آخرین بهره‌بردار به نشان
             * وضعیت نشان تغییر نمی‌کند.
             */
            $stampModel->LastFlowIDRef = $flowModel->FlowId;

            if (!$stampModel->save(false)) {
                throw new \Exception('خطا در ثبت آخرین بهره‌بردار نشان.');
            }

            $transaction->commit();

return [
    'result' => true,
    'message' => 'نشان سازمانی با موفقیت ذخیره شد.'
];

        } catch (\Exception $e) {

            $transaction->rollBack();

return [
    'result' => false,
    'message' => $e->getMessage(),
];
        }
    }

    return $this->renderAjax('_FormStamp', [
        'stampModel' => $stampModel,
        'flowModel' => $flowModel,
        'internalScopes' => $internalScopes,
        'externalScopes' => $externalScopes,
        'processCodeArray' => $processCodeArray,
        'isDirectCreate' => true,
    ]);
}

    public function actionUpdate($sid)
    {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }
        $stampModel = StampDocument::find()->where(['StampId' => $sid])->one();

        $internalScopes = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ScopeTypeDocument::find()
            ->where(['ScopeType' => 'internal'])->all(), 'ScopeId', 'ScopeTitle');

        $externalScopes = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ScopeTypeDocument::find()
            ->where(['ScopeType' => 'external'])->all(), 'ScopeId', 'ScopeTitle');

        $processCodeArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ImsDocumentProcessCode::find()->where(['isActive' => 1])
            ->addSelect(['id', 'code' => "concat(code, ' - ', IFNULL(PersianTitle,''))"])
            ->all(), 'id', 'code');

        
        if(!empty($stampModel->HumanResourceIDRef)) {
            $humanResource = $stampModel->humanResource;
            $stampModel->FullName = $humanResource->fullName;
            $stampModel->NationalCode = $humanResource->NationalCode;
        }
        if ($stampModel->load(Yii::$app->request->post())) {
            $stampModel->StampFile = \yii\web\UploadedFile::getInstance($stampModel, "StampFile");
            if ($stampModel->StampFile instanceof \Yii\web\UploadedFile) {
                $stampModel->StampDocument = $stampModel->StampFile->name;
            }

            if ($stampModel->validate()) {
                if ($stampModel->saveStamp($stampModel->internalScopeIds, $stampModel->externalScopeIds)) {

                    return \app\components\Utility::renderPjaxCallBack('stampCallbackPjax', [
                        'result' => true,
                        'message' => Yii::t('document', 'Stamp successfully Added.')
                    ]);
                } else {

                    return \app\components\Utility::renderPjaxCallBack('stampCallbackPjax', [
                        'result' => false,
                        'message' => Yii::t('document', 'Error on adding Stamp !.')
                    ]);
                }

            }
        }
        if (!Yii::$app->request->isPost) {
            $stampModel->internalScopeIds = $stampModel->getInternalScope()->select(['ScopeId'])->column();
            $stampModel->externalScopeIds = $stampModel->getExternalScope()->select(['ScopeId'])->column();
        }

        return $this->renderAjax('_FormStamp', [
            'stampModel' => $stampModel,
            'internalScopes' => $internalScopes,
            'externalScopes' => $externalScopes,
            'processCodeArray' => $processCodeArray,
        ]);
    }

    public function actionView($sid)
    {
        $stampModel = StampDocument::find()->where(['StampId' => $sid])->one();
        $internalScope = $stampModel->internalScope;
        $externalScope = $stampModel->externalScope;
        $flowController = new StampFlowDocumentController('StampFlowDocumentController', Yii::$app);
        $flowData = $flowController->actionList($sid);
        return $this->render('view', [
            'stampModel' => $stampModel,
            'sid' => $sid,
            'internalScope' => $internalScope,
            'externalScope' => $externalScope,
            'stampFlowDataProvider' => $flowData['stampFlowDataProvider'],
            'stampFlowSearchModel' => $flowData['stampFlowSearchModel']
        ]);
    }

public function actionRefund($id)
{
    $stampModel = StampDocument::findOne($id);

    if (!$stampModel) {
        throw new \yii\web\NotFoundHttpException('اطلاعات یافت نشد.');
    }

    $stampModel->scenario = 'destroy';

    if (Yii::$app->request->isPost) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if ($stampModel->load(Yii::$app->request->post())) {

            $stampModel->DestroyAttachmentFile = \yii\web\UploadedFile::getInstance(
                $stampModel,
                'DestroyAttachmentFile'
            );

            if ($stampModel->validate()) {

                if ($stampModel->DestroyAttachmentFile) {

                    $uploadResult = $stampModel->upload();

                    if (!$uploadResult) {
                        return [
                            'result' => false,
                            'message' => 'خطا در آپلود فیزیکی فایل مستندات امحا'
                        ];
                    }

                    $stampModel->DestroyAttachment = $stampModel->generateDestroyAttachmentFileName(
                        $stampModel->DestroyAttachmentFile
                    );
                }

                $stampModel->Status = 'destroyed';

                if ($stampModel->save(false)) {
                    return [
                        'result' => true,
                        'message' => 'امحا با موفقیت ثبت شد.'
                    ];
                }

                return [
                    'result' => false,
                    'message' => 'خطا در ذخیره اطلاعات امحا.'
                ];
            }

            $firstError = reset($stampModel->firstErrors);

            return [
                'result' => false,
                'message' => $firstError ?: 'خطا در ثبت اطلاعات امحا.'
            ];
        }
    }

    return $this->renderAjax('_FormRefund', [
        'stampModel' => $stampModel,
    ]);
}
public function actionDownloadDestroyAttachment($sid)
{
    $stampModel = StampDocument::findOne($sid);

    if ($stampModel === null || empty($stampModel->DestroyAttachment)) {
        throw new \yii\web\NotFoundHttpException('فایل مستندات امحا پیدا نشد.');
    }

    // خیلی مهم: چون فایل امحا باید selectedFile بشود DestroyAttachmentFile
    $stampModel->scenario = 'destroy';

    try {
        $filePath = $stampModel->getFullPath();
    } catch (\Exception $e) {
        throw new \yii\web\NotFoundHttpException('فایل مستندات امحا در سرور وجود ندارد.');
    }

    return Yii::$app->response->sendFile($filePath, $stampModel->DestroyAttachment, [
        'inline' => false,
    ]);
}

    public function actionLogoImage($id)
    {
        $StampID = Html::encode($id);

        $logoModel = \app\modules\document\models\StampDocument::find()
            ->where(['StampId' => $StampID])
            ->one();

        return $logoModel->loadImage();
    }

    public function actionVerified($Id) {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $stampDocumentModel = StampDocument::find()->where(['StampId' => $Id])->one();
        $stampDocumentModel->setScenario('Status');
        if (!$stampDocumentModel) {
            throw new \yii\web\BadRequestHttpException();
        }
        if ($stampDocumentModel->setVerifyStatus()) {
            return [
                'result' => true,
                'message' => 'Stamp document changed Verified successfully!',
            ];
        } else {
            return [
                'result' => false,
                'message' => 'Error in Verified Stamp document!',
            ];
        }
    }
        public function actionView2($peid)
{
    $peid = (int)$peid;
    $todoExists = TRUE;

    // 1. بارگذاری ProcessEntity
    $processEntityModel = \app\modules\workflow\models\ProcessEntity::find()
        ->where(['ProcessEntityID' => $peid])
        ->one();

    if (!$processEntityModel) {
        throw new \yii\web\NotFoundHttpException("ProcessEntity not found");
    }

    // 2. بارگذاری StampDocument
    $stampModel = StampDocument::find()
        ->where(['StampId' => $processEntityModel->StampIDRef])
        ->one();

    if (!$stampModel) {
        throw new \yii\web\NotFoundHttpException("StampDocument not found");
    }

    // 3. بارگذاری Scope‌ها
    $internalScope = $stampModel->internalScope;
    $externalScope = $stampModel->externalScope;

    return $this->render('view2', [
        'stampModel'          => $stampModel,
        'processEntityModel'  => $processEntityModel,
        'todoExists'          => $todoExists,
        'internalScope'       => $internalScope,
        'externalScope'       => $externalScope,
    ]);
}
// =========================================================
// اکشن‌های Workflow (جدید)
// =========================================================
public function actionCreateWorkflow()
{
    if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
        throw new \yii\web\BadRequestHttpException();
    }

    $stampModel = new StampDocument();

    $internalScopes = \yii\helpers\ArrayHelper::map(
        ScopeTypeDocument::find()->where(['ScopeType' => 'internal'])->all(),
        'ScopeId',
        'ScopeTitle'
    );

    $externalScopes = \yii\helpers\ArrayHelper::map(
        ScopeTypeDocument::find()->where(['ScopeType' => 'external'])->all(),
        'ScopeId',
        'ScopeTitle'
    );

    $processCodeArray = \yii\helpers\ArrayHelper::map(
        \app\modules\document\models\ImsDocumentProcessCode::find()
            ->where(['isActive' => 1])
            ->addSelect(['id', 'code' => "concat(code, ' - ', IFNULL(PersianTitle,''))"])
            ->all(),
        'id',
        'code'
    );

    if ($stampModel->load(Yii::$app->request->post())) {

        $stampModel->ProcessCodeIDRef = '85';

        if ($stampModel->StampTypeIDRef == 4) {
            if (!empty($stampModel->NationalCode)) {
                $humanResource = \app\modules\automation\models\HumanResource::find()
                    ->where(['NationalCode' => $stampModel->NationalCode])
                    ->one();
                if ($humanResource) {
                    $stampModel->HumanResourceIDRef = $humanResource->Id;
                    $stampModel->FullName = $humanResource->fullName;
                } else {
                    $stampModel->addError('NationalCode', 'شخصی با این کد ملی یافت نشد!');
                }
            }
        }

        $stampModel->StampId = (int)($stampModel->find()->max('StampId') + 1);
        $stampModel->Status = StampDocument::STATUS_IN_PROCESS;

        $stampModel->StampFile = \yii\web\UploadedFile::getInstance($stampModel, "StampFile");
        if ($stampModel->StampFile instanceof \yii\web\UploadedFile) {
            $stampModel->StampDocument = $stampModel->StampFile->name;
        }

        if (!$stampModel->validate()) {
            return Utility::renderPjaxCallBack('stampCallbackPjax', [
                'result' => false,
                'message' => 'خطا در اعتبارسنجی اطلاعات!',
                'errors' => $stampModel->getErrors()
            ]);
        }

        $result = $stampModel->saveStampWorkflow();

        if ($result !== false && is_numeric($result)) {
            $processEntity = ProcessEntity::find()
                ->where(['StampIDRef' => $stampModel->StampId])
                ->orderBy(['ProcessEntityID' => SORT_DESC])
                ->one();

            if ($processEntity) {
                return $this->redirect(['view2', 'peid' => $processEntity->ProcessEntityID]);
            }
        }

        return Utility::renderPjaxCallBack('stampCallbackPjax', [
            'result' => false,
            'message' => 'خطا در ایجاد گردش کار!'
        ]);
    }

    return $this->renderAjax('_FormStamp', [
        'stampModel' => $stampModel,
        'internalScopes' => $internalScopes,
        'externalScopes' => $externalScopes,
        'processCodeArray' => $processCodeArray,
    ]);
}
public function actionUpdateWorkflow($sid)
{
    if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
        throw new \yii\web\BadRequestHttpException();
    }

    $stampModel = StampDocument::find()->where(['StampId' => $sid])->one();

    $processEntity = ProcessEntity::find()
        ->where(['StampIDRef' => $sid])
        ->orderBy(['ProcessEntityID' => SORT_DESC])
        ->one();

    $peid = $processEntity ? $processEntity->ProcessEntityID : null;

    $internalScopes = \yii\helpers\ArrayHelper::map(
        \app\modules\document\models\ScopeTypeDocument::find()
            ->where(['ScopeType' => 'internal'])->all(),
        'ScopeId',
        'ScopeTitle'
    );

    $externalScopes = \yii\helpers\ArrayHelper::map(
        \app\modules\document\models\ScopeTypeDocument::find()
            ->where(['ScopeType' => 'external'])->all(),
        'ScopeId',
        'ScopeTitle'
    );

    $processCodeArray = \yii\helpers\ArrayHelper::map(
        \app\modules\document\models\ImsDocumentProcessCode::find()
            ->where(['isActive' => 1])
            ->addSelect(['id', 'code' => "concat(code, ' - ', IFNULL(PersianTitle,''))"])
            ->all(),
        'id',
        'code'
    );

    if (!empty($stampModel->HumanResourceIDRef)) {
        $humanResource = $stampModel->humanResource;
        if ($humanResource) {
            $stampModel->FullName = $humanResource->fullName;
            $stampModel->NationalCode = $humanResource->NationalCode;
        }
    }

    if ($stampModel->load(Yii::$app->request->post())) {
        $stampModel->StampFile = \yii\web\UploadedFile::getInstance($stampModel, "StampFile");

        if ($stampModel->StampFile instanceof \yii\web\UploadedFile) {
            $stampModel->StampDocument = $stampModel->StampFile->name;
        }

        if ($stampModel->validate()) {
            if ($stampModel->saveStamp($stampModel->internalScopeIds, $stampModel->externalScopeIds)) {
                if ($peid) {
                    return $this->redirect(['view2', 'peid' => $peid]);
                }

                return \app\components\Utility::renderPjaxCallBack('stampCallbackPjax', [
                    'result' => true,
                    'message' => Yii::t('document', 'Stamp successfully Added.')
                ]);
            } else {
                return \app\components\Utility::renderPjaxCallBack('stampCallbackPjax', [
                    'result' => false,
                    'message' => Yii::t('document', 'Error on adding Stamp !.')
                ]);
            }
        }
    }

    if (!Yii::$app->request->isPost) {
        $stampModel->internalScopeIds = $stampModel->getInternalScope()->select(['ScopeId'])->column();
        $stampModel->externalScopeIds = $stampModel->getExternalScope()->select(['ScopeId'])->column();
    }

    return $this->renderAjax('_FormStamp', [
        'stampModel' => $stampModel,
        'internalScopes' => $internalScopes,
        'externalScopes' => $externalScopes,
        'processCodeArray' => $processCodeArray,
    ]);
}

public function actionDelete($id)
{
    // تنظیم خروجی JSON برای درخواست‌های Ajax
    if (Yii::$app->request->isAjax) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }  
    $model = $this->findModel($id);
    if ($model->softDelete()) {
        $message = 'سند مهر با موفقیت حذف شد.';
        if (Yii::$app->request->isAjax) {
            return ['result' => true, 'message' => $message];
        }
        Yii::$app->session->setFlash('success', $message);
    } else {
        $message = 'خطا در حذف سند مهر. لطفاً مجدداً تلاش کنید.';
        if (Yii::$app->request->isAjax) {
            return ['result' => false, 'message' => $message];
        }
        Yii::$app->session->setFlash('danger', $message);
    }
    
    return $this->redirect(['list']);
}

public function actionGetProcessCode($obsId)
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    $stampModel = new StampDocument();
    $stampModel->ObsIDRef = $obsId;
    
    $processCode = $stampModel->getProcessCodeIdRef();
    
    return ['processCode' => $processCode];
}
protected function findModel($id)
{
    if (($model = StampDocument::findOne($id)) !== null) {
        return $model;
    }
    
    throw new \yii\web\NotFoundHttpException('سند مهر مورد نظر یافت نشد.');
}
public function actionFinalLogoImage($id)
{
    $model = \app\modules\document\models\StampDocument::findOne((int)$id);

    if ($model === null || empty($model->FinalStampDocument)) {
        throw new \yii\web\NotFoundHttpException('Final stamp image not found.');
    }

    $subDir = sprintf('%05d', ceil($model->StampId / 1000));

    $filePath = Yii::getAlias('@candoDocs')
        . DIRECTORY_SEPARATOR . 'stamp'
        . DIRECTORY_SEPARATOR . $subDir
        . DIRECTORY_SEPARATOR . $model->FinalStampDocument;

    if (!is_file($filePath)) {
        throw new \yii\web\NotFoundHttpException('Final stamp image file not found.');
    }

    return Yii::$app->response->sendFile($filePath, null, [
        'inline' => true,
    ]);
}

// اکشن های مربوط به امحاء

public function actionCreateRefund($sid = null)
{
    if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
        throw new \yii\web\BadRequestHttpException();
    }

    // ---------- فرم ثبت امحا ----------
    if ($sid !== null) {
        $stampModel = StampDocument::find()
            ->where([
                'StampId'   => (int) $sid,
                'isDeleted' => 0,
                'Status'    => StampDocument::STATUS_APPROVED,
            ])
            ->one();

        if (!$stampModel || !$stampModel->canStartRefund()) {
            return '<div class="alert alert-danger">نشان یافت نشد یا قابل امحا نیست.</div>';
        }

        $stampModel->setScenario('destroy');

        if ($stampModel->load(Yii::$app->request->post())) {
            $stampModel->DestroyAttachmentFile = \yii\web\UploadedFile::getInstance(
                $stampModel,
                'DestroyAttachmentFile'
            );

            $peid = $stampModel->saveStampRefund();

            if ($peid === false) {
                return \app\components\Utility::renderPjaxCallBack('refundCallbackPjax', [
                    'result'  => false,
                    'message' => 'خطا در ثبت امحا',
                    'errors'  => $stampModel->getErrors(),
                ]);
            }

            return $this->redirect(['view-refund', 'peid' => $peid]);
        }

        return $this->renderAjax('_createRefund', [
            'stampModel' => $stampModel,
        ]);
    }

    // ---------- لیست انتخاب ----------
    $searchModel  = new StampRefundsSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->renderAjax('_selectRefund', [
        'searchModel'  => $searchModel,
        'dataProvider' => $dataProvider,
    ]);
}
/**
 * نمایش صفحه جزئیات امحا نشان سازمانی
 */
public function actionViewRefund($peid)
{
    $peid = (int) $peid;
    $processEntityModel = \app\modules\workflow\models\ProcessEntity::find()
        ->where(['ProcessEntityID' => $peid])
        ->one();
    if (!$processEntityModel) {
        throw new \yii\web\NotFoundHttpException('فرایند مورد نظر یافت نشد.');
    }

    $stampModel = StampDocument::find()
        ->where([
            'StampId'   => $processEntityModel->ObjectIDRef,
            'isDeleted' => 0,
        ])
        ->one();
    if (!$stampModel) {
        throw new \yii\web\NotFoundHttpException('نشان سازمانی یافت نشد.');
    }

    // بررسی اینکه آیا این فرایند در کارتابل «در حال انجام» کاربر قرار دارد
    $todoExists = \app\modules\workflow\models\ProcessEntity::find()
        ->refundStamp('ToDo')
        ->andWhere(['ProcessEntityID' => $peid])
        ->exists();

    return $this->render('viewRefund', [
        'stampModel'          => $stampModel,
        'processEntityModel'  => $processEntityModel,
        'todoExists'          => $todoExists,
    ]);
}
/**
 * ویرایش اطلاعات امحا (مودال)
 */
public function actionUpdateRefund($sid)
{
    if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
        throw new \yii\web\BadRequestHttpException();
    }

    $stampModel = StampDocument::find()
        ->where(['StampId' => (int)$sid, 'isDeleted' => 0])
        ->one();

    if (!$stampModel) {
        return '<div class="alert alert-danger">نشان یافت نشد.</div>';
    }

    // فقط در صورتی که وضعیت 'destroyed' باشد یا قبلاً اطلاعات امحا دارد
    $stampModel->setScenario('destroy');

    if ($stampModel->load(Yii::$app->request->post())) {
        $stampModel->DestroyAttachmentFile = \yii\web\UploadedFile::getInstance($stampModel, 'DestroyAttachmentFile');

        if ($stampModel->DestroyAttachmentFile instanceof \yii\web\UploadedFile) {
            // با استفاده از DocumentTraitPro آپلود می‌شود
            // اما برای اطمینان، نام فایل را تنظیم می‌کنیم
            $stampModel->DestroyAttachment = $stampModel->generateDestroyAttachmentFileName($stampModel->DestroyAttachmentFile);
        }

        if ($stampModel->save()) {
            // اگر فایل آپلود شده، آن را ذخیره کن
            if ($stampModel->DestroyAttachmentFile instanceof \yii\web\UploadedFile) {
                $stampModel->upload(); // متد upload از DocumentTraitPro
            }
        $pe = $stampModel->getRefundProcessEntity();
     if ($pe) {
         $peid = $pe->ProcessEntityID;
     } else {
         // اگر به هر دلیلی فرایند پیدا نشد، به صفحه لیست برگردان
         Yii::$app->session->setFlash('danger', 'فرایند امحا یافت نشد.');
         return $this->redirect(['/workflow/workflow/refund-stamp']);
     }

     return \app\components\Utility::renderPjaxCallBack('refundUpdateCallback', [
         'result'   => true,
         'message'  => 'اطلاعات امحا با موفقیت به‌روز شد.',
         'redirect' => Url::to(['view-refund', 'peid' => $peid]),
     ]);
          
        } else {
            return \app\components\Utility::renderPjaxCallBack('refundUpdateCallback', [
                'result'  => false,
                'message' => 'خطا در ذخیره اطلاعات',
                'errors'  => $stampModel->getErrors(),
            ]);
        }
    }

    return $this->renderAjax('_updateRefund', [
        'stampModel' => $stampModel,
    ]);
}
}
