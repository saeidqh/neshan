<?php

namespace app\modules\document\controllers;

use app\modules\document\models\CertificateActivity;
use app\modules\document\models\CertificateDocument;
use app\modules\document\models\CertificateDocumentActivity;
use app\modules\document\models\CertificateDocumentBaseType;
use Yii;
use yii\web\Controller;
use app\components\jDateTime;
use yii\helpers\Html;

/**
 * @see CertificateDocumentController
 *
 *
 * @author <>
 */
class CertificateDocumentController extends Controller
{

    public $defaultAction = "";
    public $access = null;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['list', 'download', 'create', 'update', 'export','edit-base-type-document','document-activity-show'],
                'rules' => [
                    // allow authenticated users
                    [
                        'actions' => ['list','download','document-activity-show'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['create', 'update', 'delete', 'edit-base-type-document'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            //return false;
                            return \app\access\AccessControl::isAccess([
                                'or',
                                ['obs', 'check' => 'isCandoAdmin'],
                                ['formAction', 'aid' => 396]
                            ]);
                        }
                    ],
                    [
                        'actions' => ['export'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionList()
    {
        $documentModel = new \app\modules\document\models\CertificateDocument();
        $certificateSearchModel = new \app\modules\document\models\CertificateDocumentSearch();
        $certificateSearchModel->setScenario('search');
        $certificateDataProvider = $certificateSearchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [


            'certificateSearchModel' => $certificateSearchModel,
            'certificateDataProvider' => $certificateDataProvider,
            'documentModel' => $documentModel,

        ]);


    }


    public function actionCreate()
    {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }

        $certificateModel = new \app\modules\document\models\CertificateDocument();
        $companyTable = \app\modules\OBS\models\Company::tableName();
        $personTable = \app\modules\OBS\models\Person::tableName();

        $companyQuery = (new \yii\db\Query())
            ->from("$personTable")
            ->innerJoin($companyTable, "$companyTable.companyIdRef=$personTable.PersonID");
        $companyArray = \yii\helpers\ArrayHelper::map($companyQuery->all(), 'PersonID', 'PersonTitle');
        $certificateType = \yii\helpers\ArrayHelper::map(\app\modules\document\models\CertificateDocumentType::find()->where("certificateTypeParentId is null")->all(), 'certificateTypeId', 'certificateTitle');

        $certificateActivity = \yii\helpers\ArrayHelper::map(\app\modules\document\models\CertificateActivity::find()->all(), 'backgroundActivityTypeId', 'backgroundActivityTitle');


        if ($certificateModel->load(Yii::$app->request->post())) {


            $certificateModel->certificateId = $certificateModel->find()->max('certificateId') + 1;


            if ($certificateModel->certificateTypeIdRef == 4 or $certificateModel->certificateTypeIdRef == 5 or $certificateModel->certificateTypeIdRef == 6 or $certificateModel->certificateTypeIdRef == 7) {
                $certificateModel->certificateTypeIdRef = $certificateModel->certificateParentType;
            }

            $certificateModel->certificateFile = \yii\web\UploadedFile::getInstance($certificateModel, "certificateFile");
            if ($certificateModel->certificateFile instanceof \yii\web\UploadedFile) {
                $certificateModel->certificateDocument = $certificateModel->certificateFile->name;
            }

            if ($certificateModel->validate()) {


                if ($certificateModel->save()) {

                    if (!isset($certificateModel->activityIdList) or $certificateModel->activityIdList == '') {
                        $certificateModel->activityIdList = [];
                    }
                    foreach ($certificateModel->activityIdList as $activityId) {

                        $activityModel = new CertificateDocumentActivity();
                        $activityModel->activityIdRef = intval($activityId);
                        $activityModel->certificateIdRef = $certificateModel->certificateId;
                        $activityModel->save();
                    }


                    $certificateModel->upload();
                    $certificateModel->refresh();

                    return \app\components\Utility::renderPjaxCallBack('certificateCallbackPjax', [
                        'result' => true,
                        'message' => Yii::t('document', 'Certificate successfully Added.')
                    ]);

                } else {

                    return \app\components\Utility::renderPjaxCallBack('certificateCallbackPjax', [
                        'result' => false,
                        'message' => Yii::t('obs', 'Error on saving Certificate.<br >Error on adding Certificate node!.')
                    ]);
                }
            }
        }


        return $this->renderAjax('_Form', [
            'certificateModel' => $certificateModel,
            'companyArray' => $companyArray,
            'certificateType' => $certificateType,
            'certificateActivity' => $certificateActivity,
        ]);


    }

    public function actionUpdate($certificateId)
    {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }

        $certificateModel = CertificateDocument::find()->where(['certificateId' => $certificateId])->one();
        $companyTable = \app\modules\OBS\models\Company::tableName();
        $personTable = \app\modules\OBS\models\Person::tableName();

        $companyQuery = (new \yii\db\Query())
            ->from("$personTable")
            ->innerJoin($companyTable, "$companyTable.companyIdRef=$personTable.PersonID");
        $companyArray = \yii\helpers\ArrayHelper::map($companyQuery->all(), 'PersonID', 'PersonTitle');
        $certificateType = \yii\helpers\ArrayHelper::map(\app\modules\document\models\CertificateDocumentType::find()->where("certificateTypeParentId is null")->all(), 'certificateTypeId', 'certificateTitle');

        $certificateActivity = \yii\helpers\ArrayHelper::map(\app\modules\document\models\CertificateActivity::find()->all(), 'backgroundActivityTypeId', 'backgroundActivityTitle');

        $certificateModel->activityIdList = [];
        foreach ($certificateModel->activityDocument as $m){
            $fetchedRow[] =  $m->activityIdRef;
            array_push($certificateModel->activityIdList, $m->activityIdRef);
        }
//        var_dump($fetchedRow);

        if ($certificateModel->load(Yii::$app->request->post())) {

            $certificateModel->certificateFile = \yii\web\UploadedFile::getInstance($certificateModel, "certificateFile");
            $importCertificateFile = false;

            if ($certificateModel->certificateTypeIdRef == 4 or $certificateModel->certificateTypeIdRef == 5 or $certificateModel->certificateTypeIdRef == 6 or $certificateModel->certificateTypeIdRef == 7 ) {
                $certificateModel->certificateTypeIdRef = $certificateModel->certificateParentType;
            }

            $certificateModel->certificateFile = \yii\web\UploadedFile::getInstance($certificateModel, "certificateFile");
            if ($certificateModel->certificateFile instanceof \yii\web\UploadedFile) {
                $certificateModel->certificateDocument = $certificateModel->certificateFile->name;
                $importCertificateFile = true;
            }

            if ($certificateModel->validate()) {


                if ($certificateModel->save()) {

                    if (!isset($certificateModel->activityIdList) or $certificateModel->activityIdList == '') {
                        $certificateModel->activityIdList = [];
                    }
                    foreach ($certificateModel->activityIdList as $activityId) {

                        if (isset($fetchedRow)){
                            if (!in_array($activityId,$fetchedRow)){
                                $activityModel = new CertificateDocumentActivity();
                                $activityModel->activityIdRef = intval($activityId);
                                $activityModel->certificateIdRef = $certificateModel->certificateId;
                                $activityModel->save();
                            }
                        }else{
                            $activityModel = new CertificateDocumentActivity();
                            $activityModel->activityIdRef = intval($activityId);
                            $activityModel->certificateIdRef = $certificateModel->certificateId;
                            $activityModel->save();
                        }

                        $inputValue[] = $activityId;
                    }

                    if (isset($fetchedRow)){
                        foreach ( $fetchedRow as $row){
                            if (isset($inputValue)){
                                if (!in_array($row,$inputValue)){
                                    \app\modules\document\models\CertificateDocumentActivity::deleteActivityDocument($certificateId,$row);
                                }
                            }else{
                                \app\modules\document\models\CertificateDocumentActivity::deleteActivityDocument($certificateId,$row);
                            }
                        }

                    }
//                    if (!in_array($fetchedRow,$inputValue)){
//                        \app\modules\document\models\CertificateDocumentActivity::deleteAll(['certificateIdRef'=>$certificateId,'activityIdRef'=>$fetchedRow]);
//                    }

                    if ($importCertificateFile) {
                        $certificateModel->setConfigDoc([
                            'selectedFile' => 'certificateFile',
                            'selectedFileName' => $certificateModel->certificateFile,
                        ]);
                        $certificateModel->deleteFile();
                    }

                    $certificateModel->upload();
                    $certificateModel->refresh();

                    return \app\components\Utility::renderPjaxCallBack('certificateCallbackPjax', [
                        'result' => true,
                        'message' => Yii::t('document', 'Edit successfully .')
                    ]);

                } else {

                    return \app\components\Utility::renderPjaxCallBack('certificateCallbackPjax', [
                        'result' => false,
                        'message' => Yii::t('document', 'Error on Editing Certificate.<br >Error on Editing node!.')

                    ]);
                }
            }
        }


        return $this->renderAjax('_Form', [
            'certificateModel' => $certificateModel,
            'companyArray' => $companyArray,
            'certificateType' => $certificateType,
            'certificateActivity' => $certificateActivity,
        ]);

    }

    public function actionDelete($certificateId)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $activityTable = \app\modules\document\models\CertificateDocumentActivity::tableName();
        $baseTypeTable = \app\modules\document\models\CertificateDocumentBaseType::tableName();
        $certificateTable = \app\modules\document\models\CertificateDocument::tableName();
        $db = Yii::$app->dbTehran;

        $db->createCommand("delete From $baseTypeTable Where activityDocumentIdRef in 
                        (select id From $activityTable Where certificateIdRef in 
                             (select certificateId From $certificateTable Where certificateId=:certificateId))")
            ->bindParam(':certificateId', $certificateId)
            ->execute();

        $db->createCommand("delete from $activityTable where certificateIdRef=:certificateId")
            ->bindParam(':certificateId', $certificateId)
            ->execute();

        $certificateModel = CertificateDocument::find()->where(['certificateId' => $certificateId])->one();
        if (!isset($certificateModel->certificateId)) {
            throw new \yii\web\BadRequestHttpException;
        }

        $certificateModel->setConfigDoc([
            'selectedFile' => 'certificateFile',
            'selectedFileName' => $certificateModel->certificateFile,
        ]);
        $certificateModel->deleteFile();


        if ($certificateModel->delete()) {


            return [
                'result' => 1,
                'message' => Yii::t('obs', 'The Certificate successfully removed.')
            ];
        } else {
            return [
                'result' => 0,
                'message' => Yii::t('obs', 'Certificate in not Unknown. you cannot delete it')
            ];
        }

    }

    public function actionDownload($certificateId)
    {
        $certificateModel = CertificateDocument::find()->where(['certificateId' => $certificateId])->one();
        if (!isset($certificateModel->certificateDocument)) {
            throw new \yii\web\HttpException("Certificate document not found!!", 404);
        }

        $certificateModel->setConfigDoc([
            'selectedFile' => 'certificateFile',
            'selectedFileName' => $certificateModel->certificateDocument,
        ]);
        return $certificateModel->download();
    }

    public function actionDocumentActivityShow()
    {

        $certificateId = \Yii::$app->request->post('expandRowKey');
        $certificateId = Html::encode($certificateId);
//        $documentModal = \app\modules\document\models\CertificateDocument::find()->where(['certificateId'=>$certificateId])->one();

        $certificateDocumentActivityModel = new \app\modules\document\models\CertificateDocumentActivitySearch();
        $certificateDocumentActivityModel->setScenario('search');
        $certificateDocumentActivityDataProvider = $certificateDocumentActivityModel->searchActivitiesDocument($certificateId);


        return $this->renderAjax('_activityDocumentList', [
            'certificateDocumentActivityModel' => $certificateDocumentActivityModel,
            'certificateDocumentActivityDataProvider' => $certificateDocumentActivityDataProvider,
        ]);

    }

    public function actionEditBaseTypeDocument($adid)
    {


        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }

        $activityTableName = \app\modules\document\models\CertificateActivity::tableName();
        $activityDocumentTableName = \app\modules\document\models\CertificateDocumentActivity::tableName();
        $activityDocumentBaseTypeTableName = \app\modules\document\models\CertificateDocumentBaseType::tableName();
        $documentTableName = \app\modules\document\models\CertificateDocument::tableName();


        $certificateDocumentActivityModel = new \app\modules\document\models\CertificateActivityBaseTypeForm();
        $certificateDocumentActivityModel->loadCertificateDocumentActivity($adid);


        if (!isset($certificateDocumentActivityModel->base) and !isset($certificateDocumentActivityModel->authorizedWork) and !isset($certificateDocumentActivityModel->authorizedCapacity) and !isset($certificateDocumentActivityModel->approvedContract)) {

            if ($certificateDocumentActivityModel->load(Yii::$app->request->post())) {
                if($certificateDocumentActivityModel->validate()){
                    $max = CertificateDocumentBaseType::find()->max('id');
                    $certificateActivityBaseTypeModel = new \app\modules\document\models\CertificateDocumentBaseType();
                    $certificateActivityBaseTypeModel->id = ($max + 1);
                    $certificateActivityBaseTypeModel->activityDocumentIdRef = $adid;
                    $certificateActivityBaseTypeModel->baseTypeIdRef = CertificateDocumentBaseType::BASE_ID;
                    $certificateActivityBaseTypeModel->value = $certificateDocumentActivityModel->base;
                    $certificateActivityBaseTypeModel->save();


                    $certificateActivityBaseTypeModel = new \app\modules\document\models\CertificateDocumentBaseType();
                    $certificateActivityBaseTypeModel->id = ($max + 2);
                    $certificateActivityBaseTypeModel->activityDocumentIdRef = $adid;
                    $certificateActivityBaseTypeModel->baseTypeIdRef = CertificateDocumentBaseType::AUTHORIZED_WORK_ID;
                    $certificateActivityBaseTypeModel->value = $certificateDocumentActivityModel->authorizedWork;
                    $certificateActivityBaseTypeModel->save();

                    $certificateActivityBaseTypeModel = new \app\modules\document\models\CertificateDocumentBaseType();
                    $certificateActivityBaseTypeModel->id = ($max + 3);
                    $certificateActivityBaseTypeModel->activityDocumentIdRef = $adid;
                    $certificateActivityBaseTypeModel->baseTypeIdRef = CertificateDocumentBaseType::AUTHORIZED_CAPACITY_ID;
                    $certificateActivityBaseTypeModel->value = $certificateDocumentActivityModel->authorizedCapacity;
                    $certificateActivityBaseTypeModel->save();

                    $certificateActivityBaseTypeModel = new \app\modules\document\models\CertificateDocumentBaseType();
                    $certificateActivityBaseTypeModel->id = ($max + 4);
                    $certificateActivityBaseTypeModel->activityDocumentIdRef = $adid;
                    $certificateActivityBaseTypeModel->baseTypeIdRef = CertificateDocumentBaseType::APPROVED_CONTRACT_ID;
                    $certificateActivityBaseTypeModel->value = $certificateDocumentActivityModel->approvedContract;
                    $certificateActivityBaseTypeModel->save();
                }

                return \app\components\Utility::renderPjaxCallBack('DocumentBaseTypeCallbackPjax', [
                    'result' => true,
                    'message' => Yii::t('document', 'Certificate Base Info successfully Added.')
                ]);
            }
            return $this->renderAjax('_certificateDocumentBaseType', [
                'certificateModel' => $certificateDocumentActivityModel,
            ]);

        } else {
            if ($certificateDocumentActivityModel->load(Yii::$app->request->post())) {
                if ($certificateDocumentActivityModel->validate()) {
                    $base = CertificateDocumentBaseType::find()
                        ->where([
                            "baseTypeIdRef"=>CertificateDocumentBaseType::BASE_ID,
                            "activityDocumentIdRef"=>$adid
                        ])
                        ->one();
                    $base->value = $certificateDocumentActivityModel->base;
                    $base->save();

                    $authorizedWork = CertificateDocumentBaseType::find()
                        ->where([
                            "baseTypeIdRef"=>CertificateDocumentBaseType::AUTHORIZED_WORK_ID,
                            "activityDocumentIdRef"=>$adid
                        ])
                        ->one();
                    $authorizedWork->value = $certificateDocumentActivityModel->authorizedWork;
                    $authorizedWork->save();

                    $authorizedCapacity = CertificateDocumentBaseType::find()
                        ->where([
                            "baseTypeIdRef"=>CertificateDocumentBaseType::AUTHORIZED_CAPACITY_ID,
                            "activityDocumentIdRef"=>$adid
                        ])
                        ->one();
                    $authorizedCapacity->value = $certificateDocumentActivityModel->authorizedCapacity;
                    $authorizedCapacity->save();

                    $approvedContract = CertificateDocumentBaseType::find()
                        ->where([
                            "baseTypeIdRef"=>CertificateDocumentBaseType::APPROVED_CONTRACT_ID,
                            "activityDocumentIdRef"=>$adid
                        ])
                        ->one();
                    $approvedContract->value = $certificateDocumentActivityModel->approvedContract;
                    $approvedContract->save();
                    return \app\components\Utility::renderPjaxCallBack('DocumentBaseTypeCallbackPjax', [
                        'result' => true,
                        'message' => Yii::t('document', 'Certificate Base Info successfully Updated.')
                    ]);
                }
            }
            return $this->renderAjax('_certificateDocumentBaseType', [
                'certificateModel' => $certificateDocumentActivityModel,
            ]);
        }

    }


    public function actionDeleteBaseTypeDocument($adid){

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $adid = Html::encode($adid);
        $adid = intval($adid);

        if (\app\modules\document\models\CertificateDocumentBaseType::deleteAll(['activityDocumentIdRef'=>$adid])) {


            return [
                'result' => 1,
                'message' => Yii::t('document', 'The Certificate Base successfully removed.')
            ];
        } else {
            return [
                'result' => 0,
                'message' => Yii::t('document', 'Certificate Base in not Unknown. you cannot delete it')
            ];
        }
    }


    public function sendCertificateDocumentLog($id, $type)
    {
        $imsDocumentTable = \app\modules\document\models\ImsDocument::tableName();
        $activityManagement = new \app\components\ActivityManagement();
        $actionType = $type;
        $subType = $type . "ImsDocument";
        $subTypeDescription["TableName"] = $imsDocumentTable;
        $subTypeDescription["RecordPrimaryKey"] = $id;
        $activityManagement->sendActitvityLog($actionType, $subType, $subTypeDescription);
    }

    public function sendDownloadLog($id, $fileName)
    {
        $imsDocumentTable = \app\modules\document\models\ImsDocument::tableName();
        $activityManagement = new \app\components\ActivityManagement();
        $actionType = "Download";
        $subType = "DownloadCertificateDocument";
        $subTypeDescription["TableName"] = $imsDocumentTable;
        $subTypeDescription["RecordPrimaryKey"] = $id;
        $subTypeDescription["FileName"] = $fileName;
        $activityManagement->sendActitvityLog($actionType, $subType, $subTypeDescription);
    }

    public function sendExportLog($exportName, $fileName)
    {
        $activityManagement = new \app\components\ActivityManagement();
        $actionType = "Export";
        $subType = "ExportFile";
        $subTypeDescription["ExportName"] = $exportName;
        $subTypeDescription["FileName"] = $fileName;
        $subTypeDescription["FilePatch"] = \app\components\Templates::getTemplate('excel', $fileName);
        $activityManagement->sendActitvityLog($actionType, $subType, $subTypeDescription);
    }

    public function actionTypeChildList($lid)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $types = \app\modules\document\models\CertificateDocumentType::find()->where(['certificateTypeParentId' => $lid])->asArray()->all();
        $data = "<option> انتخاب نوع گواهی نامه </option>";
        foreach ($types as $type) {
            $data .= "<option value=" . $type['certificateTypeId'] . ">" . $type['certificateTitle'] . "</option>";
        }

        return $data;
    }


}
