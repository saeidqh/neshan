<?php

namespace app\modules\document\controllers;

use app\modules\document\models\StampDocument;
use app\modules\document\models\StampFlowDocument;
use app\modules\document\models\StampFlowDocumentSearch;
use Yii;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\UploadedFile;

class StampFlowDocumentController extends Controller
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
                                ['formAction', 'aid' => 408],
                                ['formAction', 'aid' => 409],
                                ['formAction', 'aid' => 410],
                                ['obs', 'check' => 'isCandoAdmin'],
                            ]);
                        },
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action, $exteraData = []) {
                            return \app\access\AccessControl::isAccess([
                                'or',
                                ['formAction', 'aid' => 408],
                                ['formAction', 'aid' => 410],
                                ['obs', 'check' => 'isCandoAdmin'],
                            ]);
                        },
                    ],
                    [
                        'actions' => ['update'],
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
                ],
            ],
        ];
    }

    public function actionList($sid)
    {
        $stampFlowSearchModel = new StampFlowDocumentSearch();
        $stampFlowSearchModel->setScenario('search');
        $stampFlowDataProvider = $stampFlowSearchModel->search(Yii::$app->request->queryParams, $sid);

        return [
            'stampFlowDataProvider' => $stampFlowDataProvider,
            'stampFlowSearchModel' => $stampFlowSearchModel,
        ];
    }

public function actionCreate($sid)
{
    $stampId = (int) Html::encode($sid);

    if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
        throw new \yii\web\BadRequestHttpException();
    }

    $fromView = Yii::$app->request->get('from') === 'view';
    $showFinalFields = !$fromView;

    $flowModel = new StampFlowDocument();
    $flowModel->StampIDRef = $stampId;
    $flowModel->setScenario($fromView ? 'create_from_view' : 'create');

    $stampModelForForm = StampDocument::findOne(['StampId' => $stampId]);

    if ($showFinalFields && $stampModelForForm !== null && !empty($stampModelForForm->SerialNumber)) {
        $flowModel->SerialNumber = $stampModelForForm->SerialNumber;
    }

    if (!$flowModel->load(Yii::$app->request->post())) {
        return $this->renderAjax('create', [
            'flowModel' => $flowModel,
            'showFinalFields' => $showFinalFields,
        ]);
    }

    $db = StampDocument::getDb();
    $transaction = $db->beginTransaction();

    try {
        $stampModel = StampDocument::findOne(['StampId' => $stampId]);

        if ($stampModel === null) {
            $transaction->rollBack();

            return \app\components\Utility::renderPjaxCallBack('flowCallbackPjax', [
                'result' => false,
                'message' => Yii::t('document', 'Stamp document not found.')
            ]);
        }

        $humanModel = \app\modules\automation\models\HumanResource::find()
            ->where(['NationalCode' => $flowModel->NationalCode])
            ->one();

        if ($humanModel === null) {
            $transaction->rollBack();

            return \app\components\Utility::renderPjaxCallBack('flowCallbackPjax', [
                'result' => false,
                'message' => Yii::t('document', 'Human resource not found.')
            ]);
        }

        $flowModel->HumanIDRef = $humanModel->Id;
        $flowModel->StampIDRef = $stampModel->StampId;

        /*
         * اگر فرم از صفحه view باز شده باشد
         * فیلدهای شناسه یکتا و تصویر نهایی نباید وارد فرآیند شوند
         */
        $finalFile = null;

        if ($showFinalFields) {
            if ($flowModel->SerialNumber !== null && $flowModel->SerialNumber !== '') {
                $stampModel->SerialNumber = (int) $flowModel->SerialNumber;
            }

            $finalFile = UploadedFile::getInstance($flowModel, 'FinalStampFile');

            if ($finalFile !== null) {
                $flowModel->FinalStampFile = $finalFile;
                $stampModel->FinalStampFile = $finalFile;
            }
        }

        /*
         * دریافت فایل مستندات بهره‌بردار
         */
        $attachmentFile = UploadedFile::getInstance($flowModel, 'AttachmentFile');

        if ($attachmentFile !== null) {
            $flowModel->AttachmentFile = $attachmentFile;
        }

        /*
         * ساخت FlowId قبل از validate و save
         * چون نام فایل مستندات بر اساس FlowId ساخته می‌شود
         */
        $maxFlowId = (int) StampFlowDocument::find()->max('FlowId');
        $flowModel->FlowId = $maxFlowId + 1;

        /*
         * ست کردن نام فایل مستندات در دیتابیس
         * این نام باید با الگوی DocumentTraitPro یکی باشد
         */
        if ($attachmentFile !== null) {
            $flowModel->AttachmentDocument = $flowModel->FlowId . '-attachment.' . strtolower($attachmentFile->extension);
        }

        /*
         * اعتبارسنجی مدل بهره‌بردار
         */
        if (!$flowModel->validate()) {
            $transaction->rollBack();

            return $this->renderAjax('create', [
                'flowModel' => $flowModel,
                'showFinalFields' => $showFinalFields,
            ]);
        }

        /*
         * اعتبارسنجی مدل نشان
         */
        if (!$stampModel->validate()) {
            $transaction->rollBack();

            return $this->renderAjax('create', [
                'flowModel' => $flowModel,
                'showFinalFields' => $showFinalFields,
            ]);
        }

        /*
         * بستن بهره‌بردار قبلی
         */
        $previousFlow = StampFlowDocument::find()
            ->where(['StampIDRef' => $stampModel->StampId])
            ->orderBy(['ReceivedDate' => SORT_DESC, 'FlowId' => SORT_DESC])
            ->one();

        if ($previousFlow !== null) {
            $previousFlow->RefundDate = $flowModel->ReceivedDate;

            if (!$previousFlow->save(false)) {
                $transaction->rollBack();

                return \app\components\Utility::renderPjaxCallBack('flowCallbackPjax', [
                    'result' => false,
                    'message' => Yii::t('document', 'Error on updating previous Stamp Flow.')
                ]);
            }
        }

        /*
         * ذخیره رکورد بهره‌بردار
         */
        if (!$flowModel->save(false)) {
            $transaction->rollBack();

            return \app\components\Utility::renderPjaxCallBack('flowCallbackPjax', [
                'result' => false,
                'message' => Yii::t('document', 'Error on saving Stamp Flow.')
            ]);
        }

        /*
         * به‌روزرسانی آخرین بهره‌بردار در مدل نشان
         */
        $stampModel->LastFlowIDRef = $flowModel->FlowId;

        if (!$stampModel->save(false)) {
            $transaction->rollBack();

            return \app\components\Utility::renderPjaxCallBack('flowCallbackPjax', [
                'result' => false,
                'message' => Yii::t('document', 'Error on updating Stamp Document.')
            ]);
        }

        /*
         * ذخیره تصویر نهایی نشان
         */
        if ($showFinalFields && $finalFile !== null) {
            if (!$this->saveFinalStampFile($stampModel, $finalFile)) {
                $transaction->rollBack();

                return \app\components\Utility::renderPjaxCallBack('flowCallbackPjax', [
                    'result' => false,
                    'message' => 'خطا در ذخیره تصویر نهایی نشان'
                ]);
            }
        }

        /*
         * ذخیره فایل مستندات بهره‌بردار
         */
        if ($attachmentFile !== null) {
            if (!$flowModel->upload()) {
                $transaction->rollBack();

                return \app\components\Utility::renderPjaxCallBack('flowCallbackPjax', [
                    'result' => false,
                    'message' => 'خطا در ذخیره مستندات بهره‌بردار'
                ]);
            }
        }

        $transaction->commit();

        return \app\components\Utility::renderPjaxCallBack('flowCallbackPjax', [
            'result' => true,
            'message' => Yii::t('document', 'New Stamp Flow Added.')
        ]);

    } catch (\Throwable $e) {
        $transaction->rollBack();
        Yii::error($e->getMessage(), __METHOD__);

        return \app\components\Utility::renderPjaxCallBack('flowCallbackPjax', [
            'result' => false,
            'message' => 'خطای سیستمی در ثبت بهره‌بردار نشان'
        ]);
    }
}

    public function actionUpdate($sid)
{
    $flowId = (int) Html::encode($sid);

    if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
        throw new \yii\web\BadRequestHttpException();
    }

    $flowModel = StampFlowDocument::findOne(['FlowId' => $flowId]);

    if ($flowModel === null) {
        throw new \yii\web\NotFoundHttpException(Yii::t('document', 'Stamp Flow not found.'));
    }

    $flowModel->setScenario('update');
    $flowModel->loadHumanResource();

    /*
     * نگه داشتن نام فایل قبلی
     * اگر فایل جدید آپلود نشد، نباید مقدار قبلی AttachmentDocument از بین برود
     */
    $oldAttachmentDocument = $flowModel->AttachmentDocument;

    if (!$flowModel->load(Yii::$app->request->post())) {
        return $this->renderAjax('update', [
            'flowModel' => $flowModel,
        ]);
    }

    $db = StampDocument::getDb();
    $transaction = $db->beginTransaction();

    try {
        $stampModel = StampDocument::findOne(['StampId' => $flowModel->StampIDRef]);

        if ($stampModel === null) {
            $transaction->rollBack();

            return \app\components\Utility::renderPjaxCallBack('flowCallbackPjax', [
                'result' => false,
                'message' => Yii::t('document', 'Stamp document not found.')
            ]);
        }

        $humanModel = \app\modules\automation\models\HumanResource::find()
            ->where(['NationalCode' => $flowModel->NationalCode])
            ->one();

        if ($humanModel === null) {
            $transaction->rollBack();

            return \app\components\Utility::renderPjaxCallBack('flowCallbackPjax', [
                'result' => false,
                'message' => Yii::t('document', 'Human resource not found.')
            ]);
        }

        $flowModel->HumanIDRef = $humanModel->Id;

        if ($flowModel->SerialNumber !== null && $flowModel->SerialNumber !== '') {
            $stampModel->SerialNumber = (int) $flowModel->SerialNumber;
        }

        /*
         * دریافت تصویر نهایی نشان
         */
        $finalFile = UploadedFile::getInstance($flowModel, 'FinalStampFile');

        if ($finalFile !== null) {
            $flowModel->FinalStampFile = $finalFile;
            $stampModel->FinalStampFile = $finalFile;
        }

        /*
         * دریافت فایل مستندات بهره‌بردار
         */
        $attachmentFile = UploadedFile::getInstance($flowModel, 'AttachmentFile');

        if ($attachmentFile !== null) {
            $flowModel->AttachmentFile = $attachmentFile;

            /*
             * نام فایل مستندات طبق الگوی DocumentTraitPro
             */
            $flowModel->AttachmentDocument = $flowModel->FlowId . '-attachment.' . strtolower($attachmentFile->extension);
        } else {
            /*
             * اگر فایل جدید آپلود نشده، مقدار قبلی حفظ شود
             */
            $flowModel->AttachmentDocument = $oldAttachmentDocument;
        }

        /*
         * اعتبارسنجی مدل بهره‌بردار
         * حجم 500KB و فرمت فایل مستندات اینجا چک می‌شود
         */
        if (!$flowModel->validate()) {
            $transaction->rollBack();

            return $this->renderAjax('update', [
                'flowModel' => $flowModel,
            ]);
        }

        /*
         * اعتبارسنجی مدل نشان
         */
        if (!$stampModel->validate()) {
            $transaction->rollBack();

            return $this->renderAjax('update', [
                'flowModel' => $flowModel,
            ]);
        }

        /*
         * ذخیره رکورد بهره‌بردار
         */
        if (!$flowModel->save(false)) {
            $transaction->rollBack();

            return \app\components\Utility::renderPjaxCallBack('flowCallbackPjax', [
                'result' => false,
                'message' => Yii::t('document', 'Error on Updating Stamp Flow.')
            ]);
        }

        /*
         * ذخیره مدل نشان
         */
        if (!$stampModel->save(false)) {
            $transaction->rollBack();

            return \app\components\Utility::renderPjaxCallBack('flowCallbackPjax', [
                'result' => false,
                'message' => Yii::t('document', 'Error on updating Stamp Document.')
            ]);
        }

        /*
         * ذخیره تصویر نهایی نشان
         */
        if ($finalFile !== null) {
            if (!$this->saveFinalStampFile($stampModel, $finalFile)) {
                $transaction->rollBack();

                return \app\components\Utility::renderPjaxCallBack('flowCallbackPjax', [
                    'result' => false,
                    'message' => 'خطا در ذخیره تصویر نهایی نشان'
                ]);
            }
        }

        /*
         * ذخیره فایل مستندات بهره‌بردار با DocumentTraitPro
         */
        if ($attachmentFile !== null) {

            /*
             * اگر قبلاً فایل داشته، با روش خود Trait حذف مجازی شود
             * بعد فایل جدید ذخیره شود
             */
            if (!empty($oldAttachmentDocument)) {
                $flowModel->deleteFile();
            }

            if (!$flowModel->upload()) {
                $transaction->rollBack();

                return \app\components\Utility::renderPjaxCallBack('flowCallbackPjax', [
                    'result' => false,
                    'message' => 'خطا در ذخیره مستندات بهره‌بردار'
                ]);
            }
        }

        $transaction->commit();

        return \app\components\Utility::renderPjaxCallBack('flowCallbackPjax', [
            'result' => true,
            'message' => Yii::t('document', 'Stamp Flow Updated.')
        ]);

    } catch (\Throwable $e) {
        $transaction->rollBack();
        Yii::error($e->getMessage(), __METHOD__);

        return \app\components\Utility::renderPjaxCallBack('flowCallbackPjax', [
            'result' => false,
            'message' => 'خطای سیستمی در ویرایش بهره‌بردار نشان'
        ]);
    }
}

    private function saveFinalStampFile(StampDocument $stampModel, UploadedFile $file): bool
    {
        if ($file === null) {
            return true;
        }

        if (empty($stampModel->StampId)) {
            return false;
        }

        $subDir = sprintf('%05d', ceil($stampModel->StampId / 1000));

        $basePath = Yii::getAlias('@candoDocs')
            . DIRECTORY_SEPARATOR . 'stamp'
            . DIRECTORY_SEPARATOR . $subDir;

        if (!is_dir($basePath)) {
            if (!mkdir($basePath, 0775, true) && !is_dir($basePath)) {
                return false;
            }
        }

        $extension = strtolower($file->extension);
        $fileName = $stampModel->StampId . '_final.' . $extension;
        $fullPath = $basePath . DIRECTORY_SEPARATOR . $fileName;

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }

        if (!$file->saveAs($fullPath, false)) {
            return false;
        }

        $stampModel->FinalStampDocument = $fileName;

        return $stampModel->save(false, ['FinalStampDocument']);
    }
public function actionDownloadAttachment($sid)
{
    $flowId = (int) Html::encode($sid);

    $flowModel = StampFlowDocument::findOne(['FlowId' => $flowId]);

    if ($flowModel === null) {
        throw new \yii\web\NotFoundHttpException(Yii::t('document', 'Stamp Flow not found.'));
    }

    if (empty($flowModel->AttachmentDocument)) {
        throw new \yii\web\NotFoundHttpException('مستنداتی برای این بهره‌بردار ثبت نشده است.');
    }

    $fileName = $flowModel->AttachmentDocument;
    $baseName = pathinfo($fileName, PATHINFO_FILENAME);

    $flowModel->setConfigDoc([
        'selectedFile' => 'AttachmentFile',
        'setFileName' => $baseName,
        'selectedFileName' => $fileName,
    ]);

    return $flowModel->download();
}
}