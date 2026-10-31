<?php

namespace app\modules\document\controllers;

use Yii;
use yii\web\Controller;
use app\components\jDateTime;
use app\components\Security;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\modules\document\models\Clause;
use app\modules\document\models\Standard;

/**
 * @see ImsDocumentController
 *
 *
 * @author <mail:mortezakml@gmail.com & Tel:09366263397>
 */
class ImsDocumentController extends Controller {

    public $defaultAction = "";
    public $access = null;

    public function behaviors() {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['list', 'detail', 'native-download', 'reprot-download', 'create', 'update', 'change-private', 'export'],
                'rules' => [
                    // allow authenticated users
                    [
                        'actions' => ['detail', 'reprot-download'],
                        'allow' => true,
                        'roles' => ['@'],
                    /* 'matchCallback' => function() {
                      return \app\access\AccessControl::isAccess(
                      ['formAction', 'aid' => 139] // View IMS Document Form
                      );
                      } */
                    ],
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            $master = Yii::$app->request->get('master');
                            if (!isset($master) || $master == 1) {
                                return true;
                            }
                            return \app\access\AccessControl::isAccess(
                                    ['formAction', 'aid' => 139] // View IMS Document Form
                            );
                        }
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            return \app\access\AccessControl::isAccess([
                                'or',
                                ['obs', 'check' => 'isCandoAdmin'],
                                ['formAction', 'aid' => 143]
                            ]);
                        }
                    ],
                    [
                        'actions' => ['export'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            return \app\access\AccessControl::isAccess(
                                    ['formAction', 'aid' => 324]
                            );
                        }
                    ],
                    [
                        'actions' => ['create', 'update', 'change-private'],
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
                    [
                        'actions' => ['native-download'],
                        'allow' => true,
                        'roles' => ['@'],
                        /*'matchCallback' => function () {
                            return \app\access\AccessControl::isAccess([
                                'or',
                                ['obs', 'check' => 'isCandoAdmin'],
                                ['formAction', 'aid' => 319]  // Download IMS Document (Native File)
                            ]);
                        }*/
                    ],
                ],
            ],
        ];
    }

    public function actionList($master = true, $type = 'All') {
        if (Yii::$app->request->post('type')) {
            $type = Yii::$app->request->post('type');
        }
        $companyId = Yii::$app->session->get('CompanyID');

        $imsDocumentSearchModel = new \app\modules\document\models\ImsDocumentSearch();
        $imsDocumentSearchModel->setScenario('search');
        $imsDocumentDataProvider = $imsDocumentSearchModel->search(Yii::$app->request->queryParams, $master, $type);

        // Filter Detail
        $companyArray = \yii\helpers\ArrayHelper::map(\app\modules\OBS\models\Person::find()
                                ->where(['in', 'PersonID', $companyId])
                                ->asArray()->all(), 'PersonID', 'PersonTitle');
        $obsArray = \yii\helpers\ArrayHelper::map(\app\modules\OBS\models\Obsnew::find()
                                ->andWhere(["companyIdRef" => $companyId])->asArray()->all(), 'obsId', 'obsTitle');

        $keywordArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ImsDocumentKeywordList::find()->asArray()->all(), 'id', 'code');

//        $titleArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ImsDocumentTitleList::find()->asArray()->all(), 'id', 'title');
        $titleArray = \app\modules\document\models\ImsDocument::find()->select('title')->distinct()->asArray()->all();
        $processCodeArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ImsDocumentProcessCode::find()->addSelect(['id', "CONCAT(COALESCE(code,''), ' - ', COALESCE(PersianTitle,'')) as title"])->where(['isActive' => 1])->asArray()->all(), 'id', 'title');
//        $codeListArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ImsDocumentCodeList::find()->asArray()->all(), 'id', 'title');
        $codeListArray = \app\modules\document\models\ImsDocument::find()->select('code')->distinct()->asArray()->all();

        $imsDocumentTable = \app\modules\document\models\ImsDocument::tableName();
        $imsDocumentModel = new \app\modules\document\models\ImsDocument();

        $relatedIMSArray = \yii\helpers\ArrayHelper::map(
                        \app\modules\document\models\ImsDocument::find()
                                ->addSelect([
                                    "imsTitle" => "CONCAT($imsDocumentTable.code,' (',$imsDocumentTable.title,')')",
                                    "id" => "$imsDocumentTable.id",
                                    "type" => "(case "
                                    . " when $imsDocumentTable.type=1 then '" . $imsDocumentModel->typeArray()[1] . "'"
                                    . " when $imsDocumentTable.type=2 then '" . $imsDocumentModel->typeArray()[2] . "'"
                                    . " when $imsDocumentTable.type=3 then '" . $imsDocumentModel->typeArray()[3] . "'"
                                    . " when $imsDocumentTable.type=4 then '" . $imsDocumentModel->typeArray()[4] . "'"
                                    . " when $imsDocumentTable.type=5 then '" . $imsDocumentModel->typeArray()[5] . "'"
                                    . "  else 'NULL' end)",
                                ])->andWhere(["status" => 1])->asArray()->all(), 'id', 'imsTitle', 'type');

        $typeArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ImsDocumentType::find()->addSelect(['ID', "CONCAT(COALESCE(Code,''), ' - ', COALESCE(PersianTitle,'')) as Title"])->where(['isActive' => 1])->orderBy(['sort' => SORT_ASC])->asArray()->all(), 'ID', 'Title');
        $companyAllChildsArray = \app\components\CandoHelper::companyChilds($companyId, 'array');
        return $this->render('view', [
                    'master' => $master,
                    'companyAllChildsArray' => $companyAllChildsArray,
                    'imsDocumentSearchModel' => $imsDocumentSearchModel,
                    'imsDocumentDataProvider' => $imsDocumentDataProvider,
                    'type' => $type,
                    'typeArray' => $typeArray,
                    'statusArray' => $imsDocumentSearchModel->statusArray(),
                    'companyArray' => $companyArray,
                    'obsArray' => $obsArray,
                    'relatedIMSArray' => $relatedIMSArray,
                    'titleArray' => $titleArray,
                    'processCodeArray' => $processCodeArray,
                    'codeListArray' => $codeListArray,
                    'keywordArray' => $keywordArray,
        ]);
    }

    public function actionDetail($id) {

        $id = Html::encode($id);

        $imsDocumentModel = \app\modules\document\models\ImsDocument::find()
                ->where(['id' => $id])
                ->one();

        if (!isset($imsDocumentModel)) {
            throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
        }

        return $this->renderAjax('detail', [
                    'imsDocumentModel' => $imsDocumentModel,
        ]);
    }

    public function actionCreate($id = 0) {
        $companyTable = \app\modules\OBS\models\Company::tableName();
        $imsDocumentTable = \app\modules\document\models\ImsDocument::tableName();
        $jdate = new jDateTime(true, true, 'Asia/Tehran');
        $company = Yii::$app->session->get('CompanyID');

        $imsDocumentModel = new \app\modules\document\models\ImsDocument();
        if ($id > 0) {
            $lastRevModel = \app\modules\document\models\ImsDocument::find()
                    ->where(['id' => $id, 'status' => 1])
                    ->andWhere("isDeleted is null or isDeleted<>1")
                    ->one();
            if (!$imsDocumentModel) {
                throw new \yii\web\BadRequestHttpException();
            }

            $imsDocumentModel->setScenario('revision');
            $visibleField = $imsDocumentModel->scenarios()['revision'];

            $companyAllChildsArray = \app\components\CandoHelper::companyChilds($company, 'array');
            if (!in_array($lastRevModel->companyIdRef, $companyAllChildsArray) && !Security::isCandoAdmin()) {
                throw new \yii\web\BadRequestHttpException();
            }
            $lastRevModel->status = 0;
            $imsDocumentModel->status = 1;
            $imsDocumentModel->title = $lastRevModel->title;
            $imsDocumentModel->type = $lastRevModel->type;
            $imsDocumentModel->processCode = $lastRevModel->processCode;
            $imsDocumentModel->companyIdRef = $lastRevModel->companyIdRef;
            $imsDocumentModel->preparationDate = $lastRevModel->preparationDate;
            $imsDocumentModel->revisionNumber = $lastRevModel->revisionNumber + 1;
            $imsDocumentModel->parentID = $lastRevModel->parentID;
            $imsDocumentModel->mainUnitObsIdRef = $lastRevModel->mainUnitObsIdRef;
            $imsDocumentModel->mainDocumentID = $lastRevModel->mainDocumentID;
            $imsDocumentModel->autoCode = $imsDocumentModel->setAutoCode($imsDocumentModel->companyIdRef, $imsDocumentModel->processCode, $imsDocumentModel->type, $id);
        } else {
            $imsDocumentModel->setScenario('create');
            $imsDocumentModel->status = 2;
            $imsDocumentModel->revisionNumber = 0;
            $imsDocumentModel->companyIdRef = $company;
            $visibleField = $imsDocumentModel->scenarios()['create'];
        }

        $imsDocumentModel->hasChange = 1;
        $imsDocumentModel->isDeleted = 0;
        $imsDocumentModel->id = \app\modules\document\models\ImsDocument::find()->max('id') + 1;

        if ($imsDocumentModel->load(Yii::$app->request->post())) {
            //دریافت فایل ها
            $imsDocumentModel->reportObject = \yii\web\UploadedFile::getInstance($imsDocumentModel, 'reportObject');
            $imsDocumentModel->nativeObject = \yii\web\UploadedFile::getInstance($imsDocumentModel, 'nativeObject');

            //reportObject 
            $imsDocumentModel->reportObject = \yii\web\UploadedFile::getInstance($imsDocumentModel, "reportObject");
            if ($imsDocumentModel->reportObject instanceof \yii\web\UploadedFile) {
                $imsDocumentModel->reportfile = $imsDocumentModel->reportObject->name;
            }
            //nativefile 
            $imsDocumentModel->nativeObject = \yii\web\UploadedFile::getInstance($imsDocumentModel, "nativeObject");
            if ($imsDocumentModel->nativeObject instanceof \yii\web\UploadedFile) {
                $imsDocumentModel->nativefile = $imsDocumentModel->nativeObject->name;
            }

            $imsDocumentModel->createdBy = Yii::$app->session->get('UserID');
            $imsDocumentModel->updatedBy = Yii::$app->session->get('UserID');
            $imsDocumentModel->createdAt = $jdate->date("Y/m/d", false, false, false);
            $imsDocumentModel->updatedAt = $jdate->date("Y/m/d", false, false, false);

//            $imsDocumentModel->setRevisionNumber('create');
            $imsDocumentModel->autoCode = $imsDocumentModel->setAutoCode($imsDocumentModel->companyIdRef, $imsDocumentModel->processCode, $imsDocumentModel->type, $id);
            if ($imsDocumentModel->validate()) {
                $db = Yii::$app->dbTehran;
                $maimDbTransaction = $db->beginTransaction();
                try {
                    // set Expire other Revision with out this Document
//                    $imsDocumentModel->id = \app\components\Utility::getModelNewId($imsDocumentModel);
                    $imsDocumentModel->id = \app\modules\document\models\ImsDocument::find()->max('id') + 1;
                    /* if (!empty($imsDocumentModel->parentID)) {
                      $db->createCommand("update $imsDocumentTable set status=0 WHERE parentID=" . $imsDocumentModel->parentID . ";")
                      ->execute();
                      } else {
                      $imsDocumentModel->parentID = $imsDocumentModel->id;
                      } */

                    if (empty($imsDocumentModel->parentID)) {
                        $imsDocumentModel->parentID = $imsDocumentModel->id;
                    }

                    if ($imsDocumentModel->save()) {
                        if (isset($lastRevModel)) {
                            if ($lastRevModel->save(false)) {
                                \app\modules\document\models\ImsDocument::updateAll([
                                    'mainDocumentID' => $imsDocumentModel->id,
                                        ],
                                        "mainDocumentID=$lastRevModel->id && (isDeleted is null or isDeleted<>1)"
                                );
                                \app\modules\document\models\CompanyActivityDocument::updateAll(
                                    ['imsDocumentIdRef' => $imsDocumentModel->id],
                                    ['imsDocumentIdRef' => $lastRevModel->id, 'isActive' => 1]
                                );
                            } else {
                                $maimDbTransaction->rollBack();
                                return \app\components\Utility::renderPjaxCallBack('imsDocumentCallbackPjax', [
                                            'result' => FALSE,
                                            'message' => 'Error in creating IMS Document!',
                                ]);
                            }
                        }

                        $imsDocumentModel->upload();
                        $imsDocumentModel->refresh();
                        if (!empty($imsDocumentModel->relatedKeyword)) {
                            foreach ($imsDocumentModel->relatedKeyword as $key => $value) {
                                $imsKeywordRelatedModel = \app\modules\document\models\ImsDocumentKeywordRelated::find()
                                        ->andWhere(["imsIdRef" => $imsDocumentModel->id])
                                        ->andWhere(["keywordIdRef" => $value])
                                        ->one();
                                if (!isset($imsKeywordRelatedModel)) {
                                    $imsKeywordRelatedModel = new \app\modules\document\models\ImsDocumentKeywordRelated();
                                    $imsKeywordRelatedModel->id = \app\components\Utility::getModelNewId($imsKeywordRelatedModel);
                                    $imsKeywordRelatedModel->imsIdRef = $imsDocumentModel->id;
                                    $imsKeywordRelatedModel->keywordIdRef = $value;
                                    $imsKeywordRelatedModel->save();
                                }
                            }
                        }

                        if (!empty($imsDocumentModel->relatedIMSDocument)) {
                            foreach ($imsDocumentModel->relatedIMSDocument as $key => $value) {
                                $imsRelatedModel = \app\modules\document\models\ImsDocumentRelated::find()->andWhere(["from" => $imsDocumentModel->id])->andWhere(["to" => $value])->one();
                                if (!isset($imsRelatedModel)) {
                                    $imsRelatedInsertModel = new \app\modules\document\models\ImsDocumentRelated();
                                    $imsRelatedInsertModel->id = \app\components\Utility::getModelNewId($imsRelatedInsertModel);
                                    $imsRelatedInsertModel->from = $imsDocumentModel->id;
                                    $imsRelatedInsertModel->to = $value;
                                    $imsRelatedInsertModel->save();
                                }
                            }
                        }

                        $imsDocumentModel->saveRelatedOBS();
                        $imsDocumentModel->saveClause();

                        $this->sendImsDocumentLog($imsDocumentModel->id, "New");

                        $maimDbTransaction->commit();
                        return \app\components\Utility::renderPjaxCallBack('imsDocumentCallbackPjax', [
                                    'result' => true,
                                    'id' => $imsDocumentModel->id,
                                    'message' => 'New IMS Document created successfully.',
                        ]);
                    } else {
                        $maimDbTransaction->rollBack();
                        return \app\components\Utility::renderPjaxCallBack('imsDocumentCallbackPjax', [
                                    'result' => FALSE,
                                    'message' => 'Error in creating IMS Document!',
                        ]);
                    }
                } catch (Exception $ex) {
                    $maimDbTransaction->rollBack();
                    return \app\components\Utility::renderPjaxCallBack('imsDocumentCallbackPjax', [
                                'result' => FALSE,
                                'message' => 'Error in creating IMS Document!',
                    ]);
                }
            }
        }
        $personTable = \app\modules\OBS\models\Person::tableName();

        $companyQuery = (new \yii\db\Query())
                ->from("$personTable")
                ->innerJoin($companyTable, "$companyTable.companyIdRef=$personTable.PersonID")
                ->where(["$companyTable.isActive" => 1]);

        $companyPARS = \app\modules\OBS\models\Company::find()->andWhere(["companyIdRef" => 878])->one();
        $companyNSR = \app\modules\OBS\models\Company::find()->andWhere(["companyIdRef" => 248])->one();

        if (!($companyPARS->companyIdRef == $company || $companyNSR->companyIdRef == $company)) {
            $companyQuery->andWhere(['in', 'PersonID', $company]);
        }

        $companyArray = \yii\helpers\ArrayHelper::map($companyQuery->all(), 'PersonID', 'PersonTitle');

        $imsTypeArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ImsDocumentType::find()->addSelect(['ID', "CONCAT(COALESCE(Code,''), ' - ', COALESCE(PersianTitle,'')) as Title"])->where(['isActive' => 1])->orderBy(['sort' => SORT_ASC])->asArray()->all(), 'ID', 'Title');

        $obsArray = \yii\helpers\ArrayHelper::map(\app\modules\OBS\models\Obsnew::find()
                                ->andWhere(["companyIdRef" => Yii::$app->session->get('CompanyID')])
                                ->asArray()
                                ->all(), 'obsId', 'obsTitle');

        //        $titleArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ImsDocumentTitleList::find()->asArray()->all(), 'id', 'title');
        $titleArray = \app\modules\document\models\ImsDocument::find()->select('title')->distinct()->asArray()->all();
        $processCodeArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ImsDocumentProcessCode::find()->addSelect(['id', "CONCAT(COALESCE(code,''), ' - ', COALESCE(PersianTitle,'')) as code"])->where(['isActive' => 1])->orderBy("sort")->asArray()->all(), 'id', 'code');
//        $codeListArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ImsDocumentCodeList::find()->asArray()->all(), 'id', 'title');
        $codeListArray = \app\modules\document\models\ImsDocument::find()->select('code')->distinct()->asArray()->all();
        $keywordListArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ImsDocumentKeywordList::find()->asArray()->all(), 'id', 'title');

        $imsDocumentTitleTable = \app\modules\document\models\ImsDocumentTitleList::tableName();
        $imsDocumentCodeTable = \app\modules\document\models\ImsDocumentCodeList::tableName();

        $relatedIMSArray = \yii\helpers\ArrayHelper::map(
                        \app\modules\document\models\ImsDocument::find()
                                ->innerJoin($imsDocumentTitleTable, "$imsDocumentTitleTable.id=$imsDocumentTable.title")
                                ->innerJoin($imsDocumentCodeTable, "$imsDocumentCodeTable.id=$imsDocumentTable.code")
                                ->addSelect([
                                    "imsTitle" => "CONCAT($imsDocumentCodeTable.title,' (',$imsDocumentTitleTable.title,')')",
                                    "id" => "$imsDocumentTable.id",
                                    "type" => "(case when $imsDocumentTable.type=1 then '" . $imsDocumentModel->typeArray()[1] . "'"
                                    . " when $imsDocumentTable.type=2 then '" . $imsDocumentModel->typeArray()[2] . "'"
                                    . " when $imsDocumentTable.type=3 then '" . $imsDocumentModel->typeArray()[3] . "'"
                                    . " when $imsDocumentTable.type=4 then '" . $imsDocumentModel->typeArray()[4] . "'"
                                    . " when $imsDocumentTable.type=5 then '" . $imsDocumentModel->typeArray()[5] . "'"
                                    . "  else 'NULL' end)",
                                ])->andWhere(["status" => 1])->asArray()->all(), 'id', 'imsTitle', 'type');
        /* $fathersIMSArray = \app\modules\document\models\ImsDocument::find()
          ->addSelect([
          "imsTitle" => "CONCAT($imsDocumentTable.code,' (',$imsDocumentTable.title,')')",
          "id" => "$imsDocumentTable.id",
          ])->andWhere("$imsDocumentTable.id = $imsDocumentTable.parentID")
          ->andWhere(["$imsDocumentTable.companyIdRef" => $company])
          ->andWhere("$imsDocumentTable.isDeleted is null or $imsDocumentTable.isDeleted <>1");
          if (Yii::$app->session->get('CompanyID') == 878 or Yii::$app->session->get('CompanyID') == 248) {
          $fathersIMSArray = \yii\helpers\ArrayHelper::map($fathersIMSArray->asArray()->all(), 'id', 'imsTitle');
          } else {
          $companyID = Yii::$app->session->get('CompanyID');
          $fathersIMSArray = \yii\helpers\ArrayHelper::map($fathersIMSArray->andWhere("$imsDocumentTable.companyIdRef = $companyID")->asArray()->all(), 'id', 'imsTitle');
          } */

        $mainIMSQuery = \app\modules\document\models\ImsDocument::find()
                ->addSelect([
                    "imsTitle" => "CONCAT($imsDocumentTable.autoCode, ' (',$imsDocumentTable.title,')')",
                    "id" => "$imsDocumentTable.id",
                ])
                ->where(["$imsDocumentTable.status" => [1, 2]])
                ->andWhere(["$imsDocumentTable.mainDocumentID" => NULL])
                ->andWhere("$imsDocumentTable.isDeleted is null or $imsDocumentTable.isDeleted <> 1");

        if (!Security::isCandoAdmin()) {
            $mainIMSQuery->andWhere(["$imsDocumentTable.companyIdRef" => $company]);
        }
        $mainIMSArray = \yii\helpers\ArrayHelper::map($mainIMSQuery->asArray()->all(), 'id', 'imsTitle');

        $clauseTable = Clause::tableName();
        $standardTable = Standard::tableName();
        $clauseArray = \yii\helpers\ArrayHelper::map(Clause::find()
                                ->joinWith(['standards'])
                                ->addSelect(["id" => "$clauseTable.id", "title" => "CONCAT(mainClause,'-',subClause,' ', $clauseTable.title, ' ', GROUP_CONCAT($standardTable.title SEPARATOR ' | '))"])
                                ->where(["$clauseTable.isActive" => 1])
                                ->where(["$standardTable.isActive" => 1])
                                ->groupBy("$clauseTable.id")
                                ->orderBy("$clauseTable.sort")
                                ->all(), 'id', 'title');

        return $this->renderAjax('IMSFormCreate', [
                    'id' => $id,
                    'mainIMSArray' => $mainIMSArray,
                    'clauseArray' => $clauseArray,
//                    'fathersIMSArray' => $fathersIMSArray,
                    'imsDocumentModel' => $imsDocumentModel,
                    'companyArray' => $companyArray,
                    'obsArray' => $obsArray,
                    'imsTypeArray' => $imsTypeArray,
                    'relatedIMSArray' => $relatedIMSArray,
                    'visibleField' => $visibleField,
                    'titleArray' => $titleArray,
                    'processCodeArray' => $processCodeArray,
                    'codeListArray' => $codeListArray,
                    'keywordListArray' => $keywordListArray,
        ]);
    }

    public function actionUpdate($id, $callInsert = FALSE) {

        $id = Html::encode($id);
        $callInsert = Html::encode($callInsert);

        $companyTable = \app\modules\OBS\models\Company::tableName();

        $imsDocumentModel = \app\modules\document\models\ImsDocument::find()
                ->where(['id' => $id])
                ->one();

        $imsDocumentModel->setScenario('update');
        $company = Yii::$app->session->get('CompanyID');
        $obs = Yii::$app->session->get('ObsPosition');
        if (!isset($imsDocumentModel)) {
            return \app\components\Utility::renderPjaxCallBack('imsDocumentCallbackPjax', [
                        'result' => false,
                        'message' => 'Ims Document Not Found.',
            ]);
        }
        // بررسی اجازه ی تغییر برای شرکت ها 
        //  و فقط شرکت نگار صنعت رهی  برای همه را بتواند تغییر یدهد

        $isCandoAdmin = \app\access\AccessControl::isAccess(['obs', 'check' => 'isCandoAdmin', 'errorException' => false]);

        if (!$isCandoAdmin && $company != 248) {
            if ($imsDocumentModel->companyIdRef != $company) {
                return \app\components\Utility::renderPjaxCallBack('imsDocumentCallbackPjax', [
                            'result' => FALSE,
                            'message' => "You Can't Edit This ImsDocument!!",
                ]);
            }
        }
        // در ابتدا قرار بود فیلد ها ار هم تفکیک شوند و بعدا همه را برای تمامی تایپ ها یکسان در نظر گرفتند 
        // بنابر این کد پایین فقط برای یک سناریو در نظر گرفته میشود و کد را سرجایش می گذاریم شاید بعدا دوستان نظرشان تغییر کند
        $visibleField = $imsDocumentModel->scenarios()['update'];

        $imsDocumentModel->hasChange = 1;
        $imsDocumentModel->isDeleted = 0;

        if ($imsDocumentModel->load(Yii::$app->request->post()) && !$callInsert) {
            //دریافت فایل ها
            $imsDocumentModel->reportObject = \yii\web\UploadedFile::getInstance($imsDocumentModel, 'reportObject');
            $imsDocumentModel->nativeObject = \yii\web\UploadedFile::getInstance($imsDocumentModel, 'nativeObject');
            //reportObject 
            $importReportObject = false;
            $importNativeObject = false;

            $imsDocumentModel->reportObject = \yii\web\UploadedFile::getInstance($imsDocumentModel, "reportObject");
            if ($imsDocumentModel->reportObject instanceof \yii\web\UploadedFile) {
                $imsDocumentModel->reportfile = $imsDocumentModel->reportObject->name;
                $importReportObject = true;
            }
            //nativefile 
            $imsDocumentModel->nativeObject = \yii\web\UploadedFile::getInstance($imsDocumentModel, "nativeObject");
            if ($imsDocumentModel->nativeObject instanceof \yii\web\UploadedFile) {
                $imsDocumentModel->nativefile = $imsDocumentModel->nativeObject->name;
                $importNativeObject = TRUE;
            }
            $jdate = new jDateTime(true, true, 'Asia/Tehran');
            $imsDocumentModel->updatedBy = Yii::$app->session->get('UserID');
            $imsDocumentModel->updatedAt = $jdate->date("Y/m/d", false, false, false);
//            $imsDocumentModel->setRevisionNumber('update');
//            $imsDocumentModel->autoCode = $imsDocumentModel->setAutoCode($imsDocumentModel->companyIdRef,$imsDocumentModel->processCode,$imsDocumentModel->type,$imsDocumentModel->parentID);
            if ($imsDocumentModel->validate()) {
                $db = Yii::$app->dbTehran;
                $maimDbTransaction = $db->beginTransaction();
                try {
                    if ($imsDocumentModel->save()) {
                        /* if ($importReportObject) {
                          $imsDocumentModel->setConfigDoc([
                          'selectedFile' => 'reportObject',
                          'selectedFileName' => $imsDocumentModel->reportfile,
                          ]);
                          $imsDocumentModel->deleteFile();
                          }
                          if ($importNativeObject) {
                          $imsDocumentModel->setConfigDoc([
                          'selectedFile' => 'nativeObject',
                          'selectedFileName' => $imsDocumentModel->nativefile,
                          ]);
                          $imsDocumentModel->deleteFile();
                          } */

                        $imsDocumentModel->upload();
                        $imsDocumentModel->refresh();
                        if (!empty($imsDocumentModel->relatedIMSDocument)) {
                            foreach ($imsDocumentModel->relatedIMSDocument as $key => $value) {
                                $imsRelatedModel = \app\modules\document\models\ImsDocumentRelated::find()->andWhere(["from" => $imsDocumentModel->id])->andWhere(["to" => $value])->one();
                                if (!isset($imsRelatedModel)) {
                                    $imsRelatedInsertModel = new \app\modules\document\models\ImsDocumentRelated();
                                    $imsRelatedInsertModel->id = \app\components\Utility::getModelNewId($imsRelatedInsertModel);
                                    $imsRelatedInsertModel->from = $imsDocumentModel->id;
                                    $imsRelatedInsertModel->to = $value;
                                    $imsRelatedInsertModel->save();
                                }
                            }
                        }

                        $imsDocumentModel->saveRelatedOBS();
                        $imsDocumentModel->saveClause();

                        $this->sendImsDocumentLog($imsDocumentModel->id, "Edit");

                        $maimDbTransaction->commit();
                        return \app\components\Utility::renderPjaxCallBack('imsDocumentCallbackPjax', [
                                    'result' => true,
                                    'message' => 'IMS Document updated successfully.',
                        ]);
                    } else {
                        $maimDbTransaction->rollBack();
                        return \app\components\Utility::renderPjaxCallBack('imsDocumentCallbackPjax', [
                                    'result' => FALSE,
                                    'message' => 'Error in updating IMS Document!',
                        ]);
                    }
                } catch (Exception $ex) {
                    $maimDbTransaction->rollBack();
                    return \app\components\Utility::renderPjaxCallBack('imsDocumentCallbackPjax', [
                                'result' => FALSE,
                                'message' => 'Error in updating IMS Document!',
                    ]);
                }
            }
        }


        /// کاربران شرکت پارس و نگارصنعت بتوانند برای تمامی شرکت ها رو ببینند     
        $companyTable = \app\modules\OBS\models\Company::tableName();
        $personTable = \app\modules\OBS\models\Person::tableName();

        $companyQuery = (new \yii\db\Query())
                ->from("$personTable")
                ->innerJoin($companyTable, "$companyTable.companyIdRef=$personTable.PersonID")
                ->where(["$companyTable.isActive" => 1]);

        $companyPARS = \app\modules\OBS\models\Company::find()->andWhere(["companyIdRef" => 878])->one();
        $companyNSR = \app\modules\OBS\models\Company::find()->andWhere(["companyIdRef" => 248])->one();
        if ($companyPARS->companyIdRef == $company || $companyNSR->companyIdRef == $company) {
            $obsArray = \yii\helpers\ArrayHelper::map(\app\modules\OBS\models\Obsnew::find()->asArray()->all(), 'obsId', 'obsTitle');
        } else {
            $companyQuery->andWhere(["in", 'companyIdRef', Yii::$app->session->get('CompanyID')]);
            $obsArray = \yii\helpers\ArrayHelper::map(\app\modules\OBS\models\Obsnew::find()
                                    ->andWhere(["companyIdRef" => Yii::$app->session->get('CompanyID')])
                                    ->asArray()
                                    ->all(), 'obsId', 'obsTitle');
        }

        $companyArray = \yii\helpers\ArrayHelper::map($companyQuery->all(), 'PersonID', 'PersonTitle');

        $titleArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ImsDocumentTitleList::find()->asArray()->all(), 'id', 'title');
        $processCodeArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ImsDocumentProcessCode::find()->addSelect(['id', "CONCAT(COALESCE(code,''), ' - ', COALESCE(PersianTitle,'')) as code"])->where(['isActive' => 1])->orderBy("sort")->asArray()->all(), 'id', 'code');
        $codeListArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ImsDocumentCodeList::find()->asArray()->all(), 'id', 'title');

        $imsTypeArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ImsDocumentType::find()->addSelect(['ID', "CONCAT(COALESCE(Code,''), ' - ', COALESCE(PersianTitle,'')) as Title"])->where(['isActive' => 1])->orderBy(['sort' => SORT_ASC])->asArray()->all(), 'ID', 'Title');

        $imsDocumentTable = \app\modules\document\models\ImsDocument::tableName();
        $imsDocumentTitleTable = \app\modules\document\models\ImsDocumentTitleList::tableName();
        $imsDocumentCodeTable = \app\modules\document\models\ImsDocumentCodeList::tableName();

        $relatedIMSArray = \yii\helpers\ArrayHelper::map(
                        \app\modules\document\models\ImsDocument::find()
                                ->innerJoin($imsDocumentTitleTable, "$imsDocumentTitleTable.id=$imsDocumentTable.title")
                                ->innerJoin($imsDocumentCodeTable, "$imsDocumentCodeTable.id=$imsDocumentTable.code")
                                ->addSelect([
                                    "imsTitle" => "CONCAT($imsDocumentCodeTable.title,' (',$imsDocumentTitleTable.title,')')",
                                    "id" => "$imsDocumentTable.id",
                                    "type" => "(case when $imsDocumentTable.type=1 then '" . $imsDocumentModel->typeArray()[1] . "'"
                                    . " when $imsDocumentTable.type=2 then '" . $imsDocumentModel->typeArray()[2] . "'"
                                    . " when $imsDocumentTable.type=3 then '" . $imsDocumentModel->typeArray()[3] . "'"
                                    . " when $imsDocumentTable.type=4 then '" . $imsDocumentModel->typeArray()[4] . "'"
                                    . " when $imsDocumentTable.type=5 then '" . $imsDocumentModel->typeArray()[5] . "'"
                                    . "  else 'NULL' end)",
                                ])->andWhere(["status" => 1])->asArray()->all(), 'id', 'imsTitle', 'type');

        /* $relatedIMSDocument = \app\modules\document\models\ImsDocumentRelated::find()
          ->andWhere(["from" => $imsDocumentModel->id])
          ->addSelect('to')
          ->asArray()
          ->all();
          $arrayRelated = [];
          foreach ($relatedIMSDocument as $key => $value) {
          array_push($arrayRelated, $value['to']);
          }
          $imsDocumentModel->relatedIMSDocument = $arrayRelated; */

        $relatedProcessCode = \app\modules\document\models\ImsDocumentProcessCodeRelated::find()
                ->andWhere(["imsIdRef" => $imsDocumentModel->id])
                ->addSelect('processCodeIdRef')
                ->asArray()
                ->all();
        $arrayProcessCodeRelated = [];
        foreach ($relatedProcessCode as $key => $value) {
            array_push($arrayProcessCodeRelated, $value['processCodeIdRef']);
        }

        $imsDocumentModel->relatedOBS = $arrayProcessCodeRelated;
        $clauses = !empty($imsDocumentModel->mainDocumentID) ? $imsDocumentModel->mainDocument->clauses : $imsDocumentModel->clauses;
        $imsDocumentModel->clause = ArrayHelper::getColumn($clauses, 'id');

        // Can Update Status
        $uid = \Yii::$app->session->get('UserID');
        $obsId = \Yii::$app->session->get('ObsPosition');
        $acionId = 324;  // Update IMS Document Status

        $userObsFormTable = \app\modules\account\models\UserOBSFormAction::tableName();

        $userObsFormAction = \app\modules\account\models\UserOBSFormAction::find()
                ->where(['UserIDRef' => $uid, 'OBSCategory3IDRef' => $obsId, 'FormActionIDRef' => $acionId, 'AccessValue' => 1])
                ->andWhere("($userObsFormTable.isDeleted is null or $userObsFormTable.isDeleted<>1)")
                ->one();

        $canUpdateStatus = false;
        if ($userObsFormAction) {
            $canUpdateStatus = TRUE;
        }

        /* $fathersIMSArray = \app\modules\document\models\ImsDocument::find()
          ->addSelect([
          "imsTitle" => "CONCAT($imsDocumentTable.code,' (',$imsDocumentTable.title,')')",
          "id" => "$imsDocumentTable.id",
          ])->andWhere("$imsDocumentTable.id = $imsDocumentTable.parentID")
          ->andWhere("$imsDocumentTable.isDeleted is null or $imsDocumentTable.isDeleted <>1");
          if (Yii::$app->session->get('CompanyID') == 878 or Yii::$app->session->get('CompanyID') == 248) {
          $fathersIMSArray = \yii\helpers\ArrayHelper::map($fathersIMSArray->asArray()->all(), 'id', 'imsTitle');
          } else {
          $companyID = Yii::$app->session->get('CompanyID');
          $fathersIMSArray = \yii\helpers\ArrayHelper::map($fathersIMSArray->andWhere("$imsDocumentTable.companyIdRef = $companyID")->asArray()->all(), 'id', 'imsTitle');
          } */

        $mainIMSQuery = \app\modules\document\models\ImsDocument::find()
                ->addSelect([
                    "imsTitle" => "CONCAT($imsDocumentTable.autoCode,' (',$imsDocumentTable.title,')')",
                    "id" => "$imsDocumentTable.id",
                ])
                ->where(["$imsDocumentTable.status" => [1, 2]])
                ->andWhere(["$imsDocumentTable.mainDocumentID" => NULL])
                ->andWhere("$imsDocumentTable.isDeleted is null or $imsDocumentTable.isDeleted <>1");

        if (!Security::isCandoAdmin()) {
            $mainIMSQuery->andWhere(["$imsDocumentTable.companyIdRef" => $company]);
        }
        $mainIMSArray = \yii\helpers\ArrayHelper::map($mainIMSQuery->asArray()->all(), 'id', 'imsTitle');

        $clauseTable = Clause::tableName();
        $standardTable = Standard::tableName();
        $clauseArray = \yii\helpers\ArrayHelper::map(Clause::find()
                                ->joinWith(['standards'])
                                ->addSelect(["id" => "$clauseTable.id", "title" => "CONCAT(mainClause,'-',subClause,' ', $clauseTable.title, ' ', GROUP_CONCAT($standardTable.title SEPARATOR ' | '))"])
                                ->where(["$clauseTable.isActive" => 1])
                                ->where(["$standardTable.isActive" => 1])
                                ->groupBy("$clauseTable.id")
                                ->orderBy("$clauseTable.sort")
                                ->all(), 'id', 'title');

        return $this->renderAjax('IMSFormUpdate', [
                    'isCandoAdmin' => $isCandoAdmin,
//                    'fathersIMSArray' => $fathersIMSArray,
                    'mainIMSArray' => $mainIMSArray,
                    'clauseArray' => $clauseArray,
                    'imsDocumentModel' => $imsDocumentModel,
                    'companyArray' => $companyArray,
                    'imsTypeArray' => $imsTypeArray,
                    'obsArray' => $obsArray,
                    'visibleField' => $visibleField,
                    'relatedIMSArray' => $relatedIMSArray,
                    'titleArray' => $titleArray,
                    'processCodeArray' => $processCodeArray,
                    'codeListArray' => $codeListArray,
                    'canUpdateStatus' => $canUpdateStatus,
        ]);
    }

    public function actionChangePrivate($id) {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $imsDocumentModel = \app\modules\document\models\ImsDocument::find()->where(['id' => $id])->one();
        if (!$imsDocumentModel) {
            throw new \yii\web\BadRequestHttpException();
        }
        $imsDocumentModel->setScenario('change-private');
        $imsDocumentModel->private = $imsDocumentModel->private ? 0 : 1;
        if ($imsDocumentModel->save()) {
            return [
                'result' => true,
                'message' => 'IMS Document Private changed successfully',
            ];
        } else {
            return [
                'result' => false,
                'message' => 'Error in changed IMS Document Private!',
            ];
        }
    }

    public function actionDelete($id) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $id = Html::encode($id);

        $imsDocumentModel = \app\modules\document\models\ImsDocument::find()
                ->where(['id' => $id])
                ->andWhere("isDeleted is null or isDeleted!=1")
                ->one();

        if (!$imsDocumentModel) {
            throw new \yii\web\HttpException(403, 'Ims Document Not Found');
        }
        // بررسی اجازه ی حذف برای شرکت ها 
        //  و فقط شرکت نگار صنعت رهی  برای همه را بتواند تغییر یدهد
        $company = Yii::$app->session->get('CompanyID');
        $isCandoAdmin = \app\access\AccessControl::isAccess(['obs', 'check' => 'isCandoAdmin', 'errorException' => false]);
        $obsId = Yii::$app->session->get('ObsPosition');
        if (!$isCandoAdmin && $obsId != 702) {
            if ($imsDocumentModel->companyIdRef != $company) {
                return [
                    'result' => 0,
                    'message' => "You Can't Delete This ImsDocument\n because owner other company!!"
                ];
            }
        }

//        بررسی نداشتن وابسته برای جلوگیری کردن از حذف پدر
        $imsDocumentRelatedModel = \app\modules\document\models\ImsDocument::find()
                ->where(["mainDocumentID" => $imsDocumentModel->id])
                ->andWhere("status<>0")
                ->andWhere("COALESCE(isDeleted,0)<>1")
                ->exists();
        if ($imsDocumentRelatedModel) {
            return [
                'result' => 0,
                'message' => "Error in deleting IMS Document \n This Document has Related document!!"
            ];
        }

        $imsDocumentModel->isDeleted = 1;
        if ($imsDocumentModel->save(false)) {
            $this->sendImsDocumentLog($imsDocumentModel->id, "Delete");
            return [
                'result' => 1,
                'message' => 'Ims Document deleted successfully'
            ];
        } else {
            return [
                'result' => 0,
                'message' => 'Error in deleting IMS Document'
            ];
        }
    }

    public function actionReprotDownload($id) {
        $id = Html::encode($id);

        $imsDocumentModel = \app\modules\document\models\ImsDocument::find()
                ->where(['id' => $id])
                ->one();
        if (!isset($imsDocumentModel->reportfile)) {
            throw new \yii\web\HttpException("document not found!!", 404);
        }

        $controller = new \app\modules\home\controllers\ErrorController('mytest', Yii::$app->controller->module);
        $controller->viewPath = '@app/modules/home/views/error/';

        if ($imsDocumentModel->status == 0) {
            $uid = \Yii::$app->session->get('UserID');
            $obsId = \Yii::$app->session->get('ObsPosition');
            $acionId = 322;  // دسترسی دانلود داکیومنت های نامعتبر

            $userObsFormTable = \app\modules\account\models\UserOBSFormAction::tableName();
            $userObsFormAction = \app\modules\account\models\UserOBSFormAction::find()
                    ->where(['UserIDRef' => $uid, 'OBSCategory3IDRef' => $obsId, 'FormActionIDRef' => $acionId, 'AccessValue' => 1])
                    ->andWhere("($userObsFormTable.isDeleted is null or $userObsFormTable.isDeleted<>1)")
                    ->one();
            if (!$userObsFormAction) {
                echo $controller->runAction('access-denid', ['name' => 'Access Denid!!', 'title' => 'action', 'message' => 'Access ID (323)']);
                Yii::$app->end();
            }
        }

        $imsDocumentModel->setConfigDoc([
            'selectedFile' => 'reportObject',
            'selectedFileName' => $imsDocumentModel->reportfile,
        ]);
        $this->sendDownloadLog($imsDocumentModel->id, $imsDocumentModel->reportfile);
        $imsDocumentModel->download();
    }

    public function actionNativeDownload($id) {
        $id = Html::encode($id);

        $imsDocumentModel = \app\modules\document\models\ImsDocument::find()
                ->where(['id' => $id])
                ->one();
        if (!isset($imsDocumentModel->nativefile)) {
            throw new \yii\web\HttpException("document not found!!", 404);
        }

        $controller = new \app\modules\home\controllers\ErrorController('mytest', Yii::$app->controller->module);
        $controller->viewPath = '@app/modules/home/views/error/';
        //افزودن  شرط برای این که یکسری مدارک خاص
        $isCandoAdmin = \app\components\Security::isCandoAdmin();
        $hasAid319 = \app\access\AccessControl::isAccess(['formAction', 'aid' => 319,'errorException'=>false]);
        $exceptionForm= in_array($imsDocumentModel->type,[4,5]);//4=form,5=table
        if (!$isCandoAdmin && !$hasAid319 && !$exceptionForm) {
            echo $controller->runAction('access-denied',['name'=>'Access Denied!!','title'=>'action','message'=>'باید به فرم اکشن 319 دسترسی داشته باشید']);
            \Yii::$app->end();
        }
        
        if ($imsDocumentModel->status == 0) {
            $uid = \Yii::$app->session->get('UserID');
            $obsId = \Yii::$app->session->get('ObsPosition');
            $acionId = 322;  // دسترسی دانلود داکیومنت های نامعتبر

            $userObsFormTable = \app\modules\account\models\UserOBSFormAction::tableName();
            $userObsFormAction = \app\modules\account\models\UserOBSFormAction::find()
                    ->where(['UserIDRef' => $uid, 'OBSCategory3IDRef' => $obsId, 'FormActionIDRef' => $acionId, 'AccessValue' => 1])
                    ->andWhere("($userObsFormTable.isDeleted is null or $userObsFormTable.isDeleted<>1)")
                    ->one();
            if (!$userObsFormAction) {
                echo $controller->runAction('access-denid', ['name' => 'Access Denid!!', 'title' => 'action', 'message' => 'Access ID (323)']);
                Yii::$app->end();
            }
        }

        $imsDocumentModel->setConfigDoc([
            'selectedFile' => 'nativeObject',
            'selectedFileName' => $imsDocumentModel->nativefile,
        ]);
        $this->sendDownloadLog($imsDocumentModel->id, $imsDocumentModel->nativefile);

        $imsDocumentModel->download();
    }

    public function actionChildExpand($master = true) {
        $id = \Yii::$app->request->post('expandRowKey');
        $id = Html::encode($id);
        $companyId = Yii::$app->session->get('CompanyID');

        $imsDocumentModel = \app\modules\document\models\ImsDocument::find()
                ->where(['id' => $id])
                ->one();
        if (!isset($imsDocumentModel)) {
            throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
        }
        $imsDocumentSearchModel = new \app\modules\document\models\ImsDocumentSearch();
        $imsDocumentSearchModel->setScenario('search');
//        $imsDocumentDataProvider = $imsDocumentSearchModel->searchImsDocumentExpire($imsDocumentModel);
        $imsDocumentDataProvider = $imsDocumentSearchModel->searchImsDocumentChild($imsDocumentModel->id, $master);

        $typeArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ImsDocumentType::find()->addSelect(['ID', "CONCAT(COALESCE(Code,''), ' - ', COALESCE(PersianTitle,'')) as Title"])->where(['isActive' => 1])->orderBy(['sort' => SORT_ASC])->asArray()->all(), 'ID', 'Title');
        $companyAllChildsArray = \app\components\CandoHelper::companyChilds($companyId, 'array');

        return $this->renderAjax('childExpand', [
                    'master' => $master,
                    'typeArray' => $typeArray,
                    'companyAllChildsArray' => $companyAllChildsArray,
                    'statusArray' => $imsDocumentSearchModel->statusArray(),
                    'imsDocumentSearchModel' => $imsDocumentSearchModel,
                    'imsDocumentDataProvider' => $imsDocumentDataProvider,
        ]);
    }

    /**
     *
     * @param id is ImsId
     */
    public function actionKeywordList($id) {
//        $imsDocumentModel = \app\modules\document\models\ImsDocument::findOne($id);
        $imsDocumentModel = \app\modules\document\models\ImsDocument::find()->where(['id' => $id])->one();

        $keywordListArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\ImsDocumentKeywordList::find()->asArray()->all(), 'id', 'title');

        $imsDocumentKeywordListSearchModel = new \app\modules\document\models\ImsDocumentKeywordListSearch();
        $imsDocumentKeywordListSearchModel->setScenario('search');
        $imsDocumentKeywordListDataProvider = $imsDocumentKeywordListSearchModel->search(Yii::$app->request->queryParams, $id);

        $imsDocumentKeywordRelatedSearchModel = new \app\modules\document\models\ImsDocumentKeywordListSearch();
        $imsDocumentKeywordRelatedDataProvider = $imsDocumentKeywordRelatedSearchModel->searchImsRelated(Yii::$app->request->queryParams, $id);

        return $this->renderAjax('keywordList', [
                    'imsDocumentModel' => $imsDocumentModel,
                    'imsDocumentKeywordListSearchModel' => $imsDocumentKeywordListSearchModel,
                    'imsDocumentKeywordListDataProvider' => $imsDocumentKeywordListDataProvider,
                    'imsDocumentKeywordRelatedSearchModel' => $imsDocumentKeywordRelatedSearchModel,
                    'imsDocumentKeywordRelatedDataProvider' => $imsDocumentKeywordRelatedDataProvider,
        ]);
    }

    public function actionCreateKeyword($imsId) {

        $imsId = Html::encode($imsId);

        $imsDocumentKeywordRelatedTable = \app\modules\document\models\ImsDocumentKeywordRelated::tableName();
        $imsDataArray = Yii::$app->request->post('addIMS');
        if (!empty($imsDataArray)) {
            $data = [];
            $res = false;
            foreach ($imsDataArray as $imsid) {
                $exists = \app\modules\document\models\ImsDocumentKeywordRelated::find()
                        ->andWhere(['imsIdRef' => $imsId, 'keywordIdRef' => $imsid])
                        ->exists();

                if (!$exists) {
                    $imsDocumentKeywordRelatedModel = new \app\modules\document\models\ImsDocumentKeywordRelated();
                    $imsDocumentKeywordRelatedModel->id = \app\components\Utility::getModelNewId($imsDocumentKeywordRelatedModel);
                    $imsDocumentKeywordRelatedModel->imsIdRef = $imsId;
                    $imsDocumentKeywordRelatedModel->keywordIdRef = $imsid;
                    if (!$res = $imsDocumentKeywordRelatedModel->save()) {
                        throw new \yii\web\HttpException(501, 'Data not saved!!!');
                    }
                    $this->sendImsDocumentKeywordRelatedLog($imsDocumentKeywordRelatedModel->id, "New");

                    $imsDocumentKeywordRelatedModel->refresh();
//                    $model = $imsDocumentKeywordRelatedModel->id;
                    $data[] = $imsDocumentKeywordRelatedModel->attributes;
                }
            }

            $json = json_encode(['result' => $res, 'data' => $data]);
            return $json;
        }


        $imsDocumentModel = \app\modules\document\models\ImsDocument::find()
                ->where(['id' => $imsId])
                ->one();

        $imsDocumentKeywordListSearchModel = new \app\modules\document\models\ImsDocumentKeywordListSearch();
        $imsDocumentKeywordListDataProvider = $imsDocumentKeywordListSearchModel->search(Yii::$app->request->queryParams, $imsId);

        $imsDocumentKeywordRelatedSearchModel = new \app\modules\document\models\ImsDocumentKeywordListSearch();
        $imsDocumentKeywordRelatedDataProvider = $imsDocumentKeywordListSearchModel->searchImsRelated(Yii::$app->request->queryParams, $imsId);

        return $this->renderAjax('keywordList', [
                    'imsDocumentModel' => $imsDocumentModel,
                    'imsDocumentKeywordListSearchModel' => $imsDocumentKeywordListSearchModel,
                    'imsDocumentKeywordListDataProvider' => $imsDocumentKeywordListDataProvider,
                    'imsDocumentKeywordRelatedSearchModel' => $imsDocumentKeywordRelatedSearchModel,
                    'imsDocumentKeywordRelatedDataProvider' => $imsDocumentKeywordRelatedDataProvider,
        ]);
    }

    public function actionDeleteKeyword($imsId) {
        $imsDataArray = Yii::$app->request->post('deleteIMS');

        if (!empty($imsDataArray)) {
            $IDs = '';
            foreach ($imsDataArray as $id) {
                $IDs .= $id . ',';
            }
            $IDs = substr($IDs, 0, strlen($IDs) - 1);
            \app\modules\document\models\ImsDocumentKeywordRelated::deleteAll("id in($IDs)");
        }


        $imsDocumentModel = \app\modules\document\models\ImsDocument::find()
                ->where(['id' => $imsId])
                ->one();

        $imsDocumentKeywordListSearchModel = new \app\modules\document\models\ImsDocumentKeywordListSearch();
        $imsDocumentKeywordListDataProvider = $imsDocumentKeywordListSearchModel->search(Yii::$app->request->queryParams, $imsId);

        $imsDocumentKeywordRelatedSearchModel = new \app\modules\document\models\ImsDocumentKeywordListSearch();
        $imsDocumentKeywordRelatedDataProvider = $imsDocumentKeywordListSearchModel->searchImsRelated(Yii::$app->request->queryParams, $imsId);

        return $this->renderAjax('keywordList', [
                    'imsDocumentModel' => $imsDocumentModel,
                    'imsDocumentKeywordListSearchModel' => $imsDocumentKeywordListSearchModel,
                    'imsDocumentKeywordListDataProvider' => $imsDocumentKeywordListDataProvider,
                    'imsDocumentKeywordRelatedSearchModel' => $imsDocumentKeywordRelatedSearchModel,
                    'imsDocumentKeywordRelatedDataProvider' => $imsDocumentKeywordRelatedDataProvider,
        ]);
    }

    /**
     *
     * @param $kwid is Keyword Id
     */
    public function actionKeywordUpdate($kwid) {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }

        $kwid = Html::encode($kwid);

        $imsDocumentKeywordListModel = \app\modules\document\models\ImsDocumentKeywordList::find()
                ->where(['id' => $kwid])
                ->one();

        if ($imsDocumentKeywordListModel->load(Yii::$app->request->post()) && $imsDocumentKeywordListModel->validate()) {

            $res = $imsDocumentKeywordListModel->save();
            if ($res) {
                $this->sendImsDocumentKeywordListLog($imsDocumentKeywordListModel->id, "Edit");
                return \app\components\Utility::renderPjaxCallBack('messageImsDocumentCallbackPjax', [
                            'result' => true,
                            'message' => 'Update Document has been successfully!',
                ]);
            } else {
                return \app\components\Utility::renderPjaxCallBack('messageImsDocumentCallbackPjax', [
                            'result' => false,
                            'message' => 'Error in saving data!!',
                ]);
            }
        }

        return $this->renderAjax('keywordUpdate', [
                    'imsDocumentKeywordListModel' => $imsDocumentKeywordListModel,
        ]);
    }

    public function actionKeywordCreate() {
        if (!Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            throw new \yii\web\BadRequestHttpException();
        }
        $imsDocumentKeywordListModel = new \app\modules\document\models\ImsDocumentKeywordList();

        if ($imsDocumentKeywordListModel->load(Yii::$app->request->post()) && $imsDocumentKeywordListModel->validate()) {
            $imsDocumentKeywordListModel->id = \app\components\Utility::getModelNewId($imsDocumentKeywordListModel);
            $res = $imsDocumentKeywordListModel->save();
            if ($res) {
                $this->sendImsDocumentKeywordListLog($imsDocumentKeywordListModel->id, "New");
                return \app\components\Utility::renderPjaxCallBack('messageImsDocumentCallbackPjax', [
                            'result' => true,
                            'message' => 'Create Document has been successfully!',
                ]);
            } else {
                return \app\components\Utility::renderPjaxCallBack('messageImsDocumentCallbackPjax', [
                            'result' => false,
                            'message' => 'Error in saving data!!',
                ]);
            }
        }

        return $this->renderAjax('keywordUpdate', [
                    'imsDocumentKeywordListModel' => $imsDocumentKeywordListModel,
        ]);
    }

    public function actionExport($master = true, $type = 'All') {
        $jDate = new jDateTime(true, true, 'Asia/Tehran');
        $dateNow = $jDate->date("Y.m.d", false, false);

        if (Yii::$app->request->post('imsType')) {
            $type = Yii::$app->request->post('imsType');
        }

        $imsDocumentSearchModel = new \app\modules\document\models\ImsDocumentSearch();
        $imsDocumentSearchModel->setScenario('search');
        $imsDocumentDataProvider = $imsDocumentSearchModel->search(Yii::$app->request->queryParams, $master, $type);

        $imsDocumentDataProvider->pagination->pageSize = 1000000;

        $imsDocumentQuery = $imsDocumentDataProvider->query->asArray()->all();

        $statusArray = $imsDocumentSearchModel->statusArray();

        /*
          $useXlsxFormat = true;
          $xlBook = new \ExcelBook(Yii::$app->params['libxlKey']['key'], Yii::$app->params['libxlKey']['pass'], $useXlsxFormat);
          $xlBook->setLocale('UTF-8');

          $xlBook->loadFile(\app\components\Templates::getTemplate('excel', 'Ims-Document-Template.xlsx'));
          $s = $xlBook->getSheet(0);

          $i = 3;
          foreach ($imsDocumentQuery as $modelData) {
          $mainDocument = isset($modelData['mainDocumentID']) ? 'وابسته' : 'اصلی';
          $s->write(($i), 1, intval($modelData['id']));
          $s->write(($i), 2, strval($modelData['referenceCompany']));
          $s->write(($i), 3, strval($modelData['companyTitle']));
          $s->write(($i), 4, strval($modelData['imsprocessCode']));
          $s->write(($i), 5, strval($mainDocument));
          $s->write(($i), 6, strval($modelData['imstitle']));
          $s->write(($i), 7, strval($modelData['imsType']));
          $s->write(($i), 8, strval($modelData['autoCode']));
          $s->write(($i), 9, strval(isset($modelData['preparationDate']) ? \app\components\Utility::convertDate($modelData['preparationDate'], true) : ''));
          $s->write(($i), 10, strval(isset($modelData['revisionDate']) ? \app\components\Utility::convertDate($modelData['revisionDate'], true) : ''));
          $s->write(($i), 11, strval(isset($modelData['effectiveDate']) ? \app\components\Utility::convertDate($modelData['effectiveDate'], true) : ''));
          $s->write(($i), 12, strval($modelData['revisionNumber']));
          $s->write(($i), 13, strval($statusArray[$modelData['status']]));
          $i++;
          }
          $s->removeColumn("C");

          header('Content-type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
          header('Content-Disposition: attachment;filename="Ims-Document-List-' . $dateNow . ' .xlsx"');
          $xlBook->save('php://output'); */


        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        $objReader = $objReader->load(\app\components\Templates::getTemplate('excel', 'Ims-Document-Template.xlsx'));

        $objReader->setActiveSheetIndex(0);
        $row = 4;
        foreach ($imsDocumentQuery as $key => $value) {
            $mainDocument = ($value['mainDocumentID'] == 1) ? 'وابسته' : 'اصلی';
            $objReader->getActiveSheet()
                    ->setCellValueByColumnAndRow(1, $row, $value['id'])
                    ->setCellValueByColumnAndRow(2, $row, $value['referenceCompany'])
                    ->setCellValueByColumnAndRow(3, $row, $value['companyTitle'])
                    ->setCellValueByColumnAndRow(4, $row, $value['imsprocessCode'])
                    ->setCellValueByColumnAndRow(5, $row, $mainDocument)
                    ->setCellValueByColumnAndRow(6, $row, $value['imstitle'])
                    ->setCellValueByColumnAndRow(7, $row, $value['imsType'])
                    ->setCellValueByColumnAndRow(8, $row, $value['autoCode'])
                    ->setCellValueByColumnAndRow(9, $row, $value['imscode'])
                    ->setCellValueByColumnAndRow(10, $row, isset($value['preparationDate']) ? \app\components\Utility::convertDate($value['preparationDate'], true) : '')
                    ->setCellValueByColumnAndRow(11, $row, isset($value['revisionDate']) ? \app\components\Utility::convertDate($value['revisionDate'], true) : '')
                    ->setCellValueByColumnAndRow(12, $row, isset($value['effectiveDate']) ? \app\components\Utility::convertDate($value['effectiveDate'], true) : '')
                    ->setCellValueByColumnAndRow(13, $row, $value['revisionNumber'])
                    ->setCellValueByColumnAndRow(14, $row, $statusArray[$value['status']]);
            $row++;
        }

        if (!$master) {
            $objReader->getActiveSheet()->removeColumn("C");
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Ims-Document-List-' . $dateNow . '.xlsx"');
        $objWriter = \PHPExcel_IOFactory::createWriter($objReader, 'Excel2007');
        $objWriter->save('php://output');

        $this->sendExportLog("ExportImsDocument", "Ims-Document-Template.xlsx");
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

    public function sendDownloadLog($id, $fileName) {
        $imsDocumentTable = \app\modules\document\models\ImsDocument::tableName();
        $activityManagement = new \app\components\ActivityManagement();
        $actionType = "Download";
        $subType = "DownloadImsDocument";
        $subTypeDescription["TableName"] = $imsDocumentTable;
        $subTypeDescription["RecordPrimaryKey"] = $id;
        $subTypeDescription["FileName"] = $fileName;
        $activityManagement->sendActitvityLog($actionType, $subType, $subTypeDescription);
    }

    public function sendExportLog($exportName, $fileName) {
        $activityManagement = new \app\components\ActivityManagement();
        $actionType = "Export";
        $subType = "ExportFile";
        $subTypeDescription["ExportName"] = $exportName;
        $subTypeDescription["FileName"] = $fileName;
        $subTypeDescription["FilePatch"] = \app\components\Templates::getTemplate('excel', $fileName);
        $activityManagement->sendActitvityLog($actionType, $subType, $subTypeDescription);
    }

    public function actionGenerateAutoCode($cid, $pcid, $tid, $pid = 0) {
        $autoCode = \app\modules\document\models\ImsDocument::setAutoCode($cid, $pcid, $tid, $pid);
        return $autoCode;
    }

    /* public function actionGenerateAutoCode($cid,$pcid,$tid,$pid) {
      $autoCodes = \app\modules\document\models\ImsDocument::find()->where('parentID = id')->all();

      foreach ($autoCodes as $autoCode){
      $code = \app\modules\document\models\ImsDocument::setAutoCode2($autoCode->companyIdRef,$autoCode->processCode,$autoCode->type,$autoCode->parentID,$autoCode->revisionNumber);
      $autoCode->setScenario('autoCode');
      $autoCode->autoCode = $code;
      $autoCode->save();
      }
      $autoCodes = \app\modules\document\models\ImsDocument::find()->where('parentID != id')->all();
      foreach ($autoCodes as $autoCode){
      $code = \app\modules\document\models\ImsDocument::setAutoCode2($autoCode->companyIdRef,$autoCode->processCode,$autoCode->type,$autoCode->parentID,$autoCode->revisionNumber);
      $autoCode->setScenario('autoCode');
      $autoCode->autoCode = $code;
      $autoCode->save();
      }

      } */
}
