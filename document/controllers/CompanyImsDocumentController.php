<?php

namespace app\modules\document\controllers;

use Yii;
use yii\web\Controller;
use app\components\jDateTime;
use app\modules\document\models\ImsDocument;
use app\modules\document\models\ImsDocumentCompany;
use app\modules\document\models\ImsDocumentType;

/**
 * @see CompanyImsDocumentController
 *
 */
class CompanyImsDocumentController extends Controller {

    public function behaviors() {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['create', 'delete'],
                'rules' => [
                    [
                        'actions' => ['create', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            return \app\access\AccessControl::isAccess([
                                'or',
                                ['obs', 'check' => 'isCandoAdmin'],
                                ['formAction', 'aid' => 142]  // Edit IMS Document or Insert New IMS Document
                            ]);
                        }
                    ],
                ],
            ],
        ];
    }

    public function actionCreate() {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }

        $params = Yii::$app->request->queryParams;
        $selectedDocs = isset($params['docs']) ? $params['docs'] : null;
        $companyId = Yii::$app->session->get('CompanyID');

        $imsDocumentTable = ImsDocument::tableName();
        $imsDocumentCompanyTable = ImsDocumentCompany::tableName();
        $existDocs = (new \yii\db\Query())
                ->select('DocumentIDRef')
                ->from($imsDocumentCompanyTable)
                ->where(['CompanyIDRef' => $companyId])
                ->andWhere("$imsDocumentCompanyTable.isDeleted is null or $imsDocumentCompanyTable.isDeleted<>1");

        if (count($selectedDocs) > 0) {
            $db = Yii::$app->dbTehran;
            $mainDbTransaction = $db->beginTransaction();
//            $existDocs->andWhere(['DocumentIDRef' => $selectedDocs]);
            try {
                $documents = ImsDocument::find()
                        ->select('id')
                        ->where(['id' => $selectedDocs])
                        ->andWhere(['not in', 'id', $existDocs])
                        ->all();
                $res = $this->addDocuments($companyId, $documents, $selectedDocs);
                if (!$res) {
                    $mainDbTransaction->rollBack();
                    return \app\components\Utility::renderPjaxCallBack('companyImsDocumentCallbackPjax', [
                                'result' => false,
                                'message' => 'Error in adding IMS Documentt!',
                    ]);
                }
                $mainDbTransaction->commit();
                return \app\components\Utility::renderPjaxCallBack('companyImsDocumentCallbackPjax', [
                            'result' => true,
                            'message' => 'IMS Document added successfully.',
                ]);
            } catch (Exception $ex) {
                $mainDbTransaction->rollBack();
                return \app\components\Utility::renderPjaxCallBack('companyImsDocumentCallbackPjax', [
                            'result' => false,
                            'message' => 'Error in adding IMS Document!',
                ]);
            }
        }

        $imsDocumentSearchModel = new \app\modules\document\models\ImsDocumentSearch();
        $imsDocumentSearchModel->setScenario('search');
        $imsDocumentDataProvider = $imsDocumentSearchModel->search(Yii::$app->request->queryParams, false, 'All');
        $imsDocumentDataProvider->query->andWhere(["$imsDocumentTable.status" => 1])->andWhere(['not in', "$imsDocumentTable.id", $existDocs])->andWhere("$imsDocumentTable.private=0 or $imsDocumentTable.companyIdRef=$companyId");

        $typeArray = \yii\helpers\ArrayHelper::map(ImsDocumentType::find()->asArray()->all(), 'ID', 'Title');
        return $this->renderAjax('create', [
                    'typeArray' => $typeArray,
                    'statusArray' => $imsDocumentSearchModel->statusArray(),
                    'imsDocumentSearchModel' => $imsDocumentSearchModel,
                    'imsDocumentDataProvider' => $imsDocumentDataProvider,
        ]);
    }

    public function actionDelete() {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }

        $params = Yii::$app->request->queryParams;
        $selectedDocs = isset($params['docs']) ? $params['docs'] : null;
        $companyId = Yii::$app->session->get('CompanyID');
        $jdate = new jDateTime(true, true, 'Asia/Tehran');

        if (count($selectedDocs) > 0) {
            $res = ImsDocumentCompany::updateAll(
                            [
                                'isDeleted' => 1,
                                'LastUserIDRef' => Yii::$app->session->get('UserID'),
                                'LastUpdate' => $jdate->date("Y/m/d", false, false),
                            ],
                            ['DocumentIDRef' => $selectedDocs, 'CompanyIDRef' => $companyId, "COALESCE(isDeleted,0)" => 0]);
            if ($res) {
                return \app\components\Utility::renderPjaxCallBack('companyImsDocumentCallbackPjax', [
                            'result' => true,
                            'message' => 'Company Ims Document deleted successfully',
                ]);
            } else {
                return \app\components\Utility::renderPjaxCallBack('companyImsDocumentCallbackPjax', [
                            'result' => false,
                            'message' => 'Error in deleting Company IMS Document!',
                ]);
            }
        }
        return \app\components\Utility::renderPjaxCallBack('companyImsDocumentCallbackPjax', [
                    'result' => false,
                    'message' => 'Error in deleting Company IMS Document!',
        ]);
    }

    public function actionExport($master = true, $type = 'All') {
        $jDate = new jDateTime(true, true, 'Asia/Tehran');
        $dateNow = $jDate->date("Y.m.d", false, false);

        $useXlsxFormat = true;
        $xlBook = new \ExcelBook(Yii::$app->params['libxlKey']['key'], Yii::$app->params['libxlKey']['pass'], $useXlsxFormat);
        $xlBook->setLocale('UTF-8');

        $xlBook->loadFile(\app\components\Templates::getTemplate('excel', 'Ims-Document-Template.xlsx'));
        $s = $xlBook->getSheet(0);

        if (Yii::$app->request->post('type')) {
            $type = Yii::$app->request->post('type');
        }

        $imsDocumentSearchModel = new \app\modules\document\models\ImsDocumentSearch();
        $imsDocumentSearchModel->setScenario('search');
        $imsDocumentDataProvider = $imsDocumentSearchModel->search(Yii::$app->request->queryParams, $master, $type);

        $imsDocumentDataProvider->pagination->pageSize = 1000000;

        $imsDocumentQuery = $imsDocumentDataProvider->query->asArray()->all();

        $typeArray = $imsDocumentSearchModel->typeArray();
        $statusArray = $imsDocumentSearchModel->statusArray();

        $i = 3;
        foreach ($imsDocumentQuery as $modelData) {
            $s->write(($i), 1, intval($modelData['id']));
            $s->write(($i), 2, strval($modelData['imstitle']));
            $s->write(($i), 3, strval($modelData['imscode']));
            $s->write(($i), 4, strval(isset($modelData['preparationDate']) ? \app\components\Utility::convertDate($modelData['preparationDate'], true) : ''));
            $s->write(($i), 5, strval(isset($modelData['revisionDate']) ? \app\components\Utility::convertDate($modelData['revisionDate'], true) : ''));
            $s->write(($i), 6, strval(isset($modelData['effectiveDate']) ? \app\components\Utility::convertDate($modelData['effectiveDate'], true) : ''));
            $s->write(($i), 7, strval($modelData['revisionNumber']));
            $s->write(($i), 8, strval($modelData['preparatorUnitTitle']));
            $s->write(($i), 9, strval($modelData['imsprocessCode']));
            $s->write(($i), 10, strval($modelData['imsType']));
            $s->write(($i), 11, strval($statusArray[$modelData['status']]));
            $s->write(($i), 12, strval($modelData['companyTitle']));

            $i++;
        }

        /* foreach ($imsDocumentDataProvider->models as $modelData) {
          var_dump($modelData);
          die();
          $s->write(($i), 1, intval($modelData->id));
          $s->write(($i), 2, strval($modelData->imstitle));
          $s->write(($i), 3, $modelData->imscode);
          $s->write(($i), 4, strval($modelData->preparationDate));
          $s->write(($i), 5, strval($modelData->revisionDate));
          $s->write(($i), 6, strval($modelData->effectiveDate));
          $s->write(($i), 7, strval($modelData->revisionNumber));
          $s->write(($i), 8, strval($modelData->preparatorUnit));
          $s->write(($i), 9, strval($modelData->imsprocessCode));
          $s->write(($i), 10, strval($typeArray[$modelData->type]));
          $s->write(($i), 11, strval($statusArray[$modelData->status]));
          $s->write(($i), 12, strval($modelData->companyTitle));

          $i++;
          } */

        header('Content-type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Ims-Document-List-' . $dateNow . ' .xlsx"');
        $xlBook->save('php://output');

        $this->sendExportLog("ExportImsDocument", "Ims-Document-Template.xlsx");
    }

    private function addDocuments($companyId, $documents, $selectedDocs) {
        foreach ($documents as $document) {
            $companyDocument = new ImsDocumentCompany();
            $companyDocument->setScenario('create');
            $companyDocument->CompanyIDRef = $companyId;
            $companyDocument->DocumentIDRef = $document->id;
            if (!$companyDocument->save()) {
                return false;
            }
            if (empty($document->mainDocumentID)) {
                $subDocuments = $document->subDocuments;
                foreach ($subDocuments as $document) {
                    if (!in_array($document->id, $selectedDocs)) {
                        $companyDocument = new ImsDocumentCompany();
                        $companyDocument->setScenario('create');
                        $companyDocument->CompanyIDRef = $companyId;
                        $companyDocument->DocumentIDRef = $document->id;
                        if (!$companyDocument->save()) {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    public function sendImsDocumentLog($id, $type) {
        $imsDocumentTable = \app\modules\document\models\ImsDocument::tableName();
        $activityManagement = new \app\components\ActivityManagement();
        $actionType = $type;
        $subType = $type . "ImsDocument";
        $subTypeDescription["TableName"] = $imsDocumentTable;
        $subTypeDescription["RecordPrimaryKey"] = $id;
        $activityManagement->sendActitvityLog($actionType, $subType, $subTypeDescription);
    }

    public function sendImsDocumentKeywordRelatedLog($id, $type) {
        $imsDocumentKeywordRelatedTable = \app\modules\document\models\ImsDocumentKeywordRelated::tableName();
        $activityManagement = new \app\components\ActivityManagement();
        $actionType = $type;
        $subType = $type . "ImsDocumentKeywordRelated";
        $subTypeDescription["TableName"] = $imsDocumentKeywordRelatedTable;
        $subTypeDescription["RecordPrimaryKey"] = $id;
        $activityManagement->sendActitvityLog($actionType, $subType, $subTypeDescription);
    }

    public function sendImsDocumentKeywordListLog($id, $type) {
        $imsDocumentKeywordListTable = \app\modules\document\models\ImsDocumentKeywordList::tableName();
        $activityManagement = new \app\components\ActivityManagement();
        $actionType = $type;
        $subType = $type . "ImsDocumentKeywordList";
        $subTypeDescription["TableName"] = $imsDocumentKeywordListTable;
        $subTypeDescription["RecordPrimaryKey"] = $id;
        $activityManagement->sendActitvityLog($actionType, $subType, $subTypeDescription);
    }

}
