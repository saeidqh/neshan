<?php

namespace app\modules\document\controllers;

use app\components\Utility;
use app\modules\document\models\Clause;
use app\modules\document\models\StampDocument;
use Yii;
use yii\web\Controller;

/**
 * @see ExportController
 *
 *
 * @author <>
 */
class ExportController extends Controller {

    public function behaviors() {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['stamp', 'clause', 'clause-activity'],
                'rules' => [
                    [
                        'actions' => ['stamp'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action, $exteraData = []) {
                            return \app\access\AccessControl::isAccess([
                                'or',
                                ['formAction', 'aid' => 408],
                                ['formAction', 'aid' => 413],
                                ['obs', 'check' => 'isCandoAdmin'],
                            ]);
                        },
                    ],
                    [
                        'actions' => ['clause', 'clause-activity'],
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

    public function actionStamp() {
        $userCompanyId = yii::$app->session->get('CompanyID');
        $companyAllChildsArray = \app\components\CandoHelper::companyChilds($userCompanyId, 'array');
        $companyTable = \app\modules\OBS\models\Person::tableName();
        $obsNewTable = \app\modules\OBS\models\Obsnew::tableName();
        $stampTable = \app\modules\document\models\StampDocument::tableName();
        $userTable = \app\modules\account\models\User::tableName();
        $stampFlowTable = \app\modules\document\models\StampFlowDocument::tableName();
        $humanTable = \app\modules\automation\models\HumanResource::tableName();
        $stampTypeTable = \app\modules\document\models\StampTypeDocument::tableName();
        $stampArray = StampDocument::find()
                        ->leftJoin("$humanTable as human", "human.Id = $stampTable.HumanResourceIDRef")
                        ->leftJoin($stampFlowTable, "$stampFlowTable.FlowId = $stampTable.LastFlowIDRef")
                        ->leftJoin($humanTable, "$humanTable.Id = $stampFlowTable.HumanIDRef")
                        ->leftJoin($obsNewTable, "$obsNewTable.obsId = $stampTable.ObsIDRef")
                        ->leftJoin($companyTable, "$companyTable.PersonID = $obsNewTable.companyIdRef")
                        ->leftJoin($userTable, "$userTable.UserID = $stampTable.LastUserIDRef")
                        ->leftJoin("$userTable as destroy", "destroy.UserID = $stampTable.DestroyUserIDRef")
                        ->leftJoin($stampTypeTable, "$stampTypeTable.StampTypeId = $stampTable.StampTypeIDRef")
                        ->andWhere(['in', "$obsNewTable.companyIdRef", $companyAllChildsArray])
                        ->addSelect([
                            "StampId" => "$stampTable.StampId",
                            "شرکت" => "$companyTable.companyName",
                            "OrganizationalMaker" => "$stampTable.OrganizationalMaker",
                            "عنوان نشان سازمانی" => "$stampTable.StampTitle",
                            "نوع نشان سازمانی" => "$stampTypeTable.TypeName",
                            "تاریخ درخواست" => "$stampTable.RequestDate",
                            "بهره بردار" => "concat($humanTable.FirstName, ' ', $humanTable.LastName)",
                            "کد ملی بهره بردار" => "$humanTable.NationalCode",
                            "صاحب امضا/نام" => "concat(human.FirstName, ' ', human.LastName)",
                            "کد ملی صاحب امضا/نام" => "human.NationalCode",
                            "وضعیت" => "$stampTable.Status",
                            "تاریخ امحاء" => "$stampTable.DestroyDate",
                            "امحاء کننده" => "concat(destroy.FirstName, ' ', destroy.LastName)",
                            "توضیحات امحاء نشان سازمانی" => "$stampTable.DescriptionDestroy",
                            "آخرین ویرایش کننده" => "concat($userTable.FirstName, ' ', $userTable.LastName)",
                            "تاریخ آخرین ویرایش" => "$stampTable.LastUpdate"
                        ])
                        ->orderBy(['StampId' => SORT_DESC])
                        ->asArray()->all();

        foreach ($stampArray as &$stamp) {
            $model = StampDocument::findOne($stamp['StampId']);
            $parentTitle = $model->getAssistanceTitle();
//            $makerLabel = 'نامشخص';
            switch ((int)
            $stamp['OrganizationalMaker']) {
                case 1:
                    $stamp['OrganizationalMaker'] = 'رده بالا دست';
                    break;
                case 2:
                    $stamp['OrganizationalMaker'] = 'رده';
                    break;
                default:
                    $stamp['OrganizationalMaker'] = '-';
            }
            switch ($stamp['وضعیت']) {
                case 'pending':
                    $stamp['وضعیت'] = 'در حال بررسی';
                    break;
                case 'approved':
                    $stamp['وضعیت'] = 'تایید شده';
                    break;
                case 'destroyed':
                    $stamp['وضعیت'] = 'امحاء شده';
                    break;
                default:
                    $stamp['وضعیت'] = '-';
            }


            $positionTitle = $model->obs->fullTitle;
            $stamp = array_merge(
                    ['شناسه نشان سازمانی' => $stamp['StampId']],
                    ['شرکت' => $stamp['شرکت']],
                    ['جایگاه والد' => $parentTitle],
                    ['واحد مالک نشان سازمانی' => $positionTitle],
//                    ['OrganizationalMaker' => $makerLabel],
                    array_slice($stamp, 2)
            );
        }


        $format = ['0' => 'string',
            '1' => 'string',
            '2' => 'string',
            '3' => 'string',
            '4' => 'string',
            '5' => 'string',
            '6' => 'string',
            '7' => 'string',
            '8' => 'string',
            '9' => 'string',
            '10' => 'string',
            '11' => 'string',
            '12' => 'string',
            '13' => 'string',
            '14' => 'string',
            '15' => 'string',
            '16' => 'string',
            '17' => 'string',
        ];

        Utility::exportDataAsLibxl($stampArray, "stamp List", 'Stamp List', $format);
    }
    
    public function actionClause() {
        $clauseSearchModel = new \app\modules\document\models\ClauseSearch();
        $clauseSearchModel->setScenario('search');
        $clauseDataProvider = $clauseSearchModel->search(Yii::$app->request->queryParams);

        $clauses = $clauseDataProvider->query->all();
        $data = [];
        $row = 0;
        foreach ($clauses as $clause) {
            $data[$row] = [
                'شناسه' => $clause->id,
                'استاندارد/مدل' => $clause->standard,
                'نام بند' => $clause->title,
                'نوع' => Clause::getType($clause->type),
                'شماره بند' => $clause->fullClauseNo,
                'توضیح مختصر' => $clause->description,
                'اطلاعات مدون' => Clause::getCodifiedInformation($clause->codifiedInformation),
                'تعداد فعالیت' => $clause->activityCount,
                'تعداد فایل نمونه' => $clause->activityFileCount,
                'وزن' => $clause->weight,
                'ترتیب' => $clause->sort,
                'وضعیت' => $clause->isActive == 1 ? 'فعال' : 'غیر فعال',
            ];
            $row++;
        }
        $format = [
            '0' => 'integer',
            '1' => 'string',
            '2' => 'string',
            '3' => 'string',
            '4' => 'string',
            '5' => 'string',
            '6' => 'string',
            '7' => 'string',
            '8' => 'string',
            '9' => 'string',
            '10' => 'string',
            '11' => 'string',
        ];

        \app\components\Utility::exportDataAsLibxl($data, "Clause-List", "Clause-List", $format);
    }

    public function actionClauseActivity() {
        $clauseSearchModel = new \app\modules\document\models\ClauseSearch();
        $clauseSearchModel->setScenario('search');
        $clauseDataProvider = $clauseSearchModel->search(Yii::$app->request->queryParams);

        $clauseActivityTable = \app\modules\document\models\ClauseActivity::tableName();
        $clauseActivities = \app\modules\document\models\ClauseActivity::find()
                        ->addSelect(["$clauseActivityTable.*", "subQuery.id AS clauseId", "subQuery.standard", "subQuery.title AS clauseTitle", "subQuery.type AS clauseType", "subQuery.fullClauseNo", "subQuery.isActive"])
                        ->innerJoin(['subQuery' => "(" . $clauseDataProvider->query->createCommand()->rawSql . ")"], "subQuery.id=$clauseActivityTable.clauseIdRef")
                        ->andWhere(["$clauseActivityTable.isDeleted" => 0])
                        ->asArray()->all();

        $data = [];
        $row = 0;
        foreach ($clauseActivities as $activity) {
        $data[$row] = [
                'شناسه بند' => $activity['clauseId'],
                'استاندارد/مدل' => $activity['standard'],
                'نام بند' => $activity['clauseTitle'],
                'نوع' => Clause::getType($activity['clauseType']),
                'شماره بند' => $activity['fullClauseNo'],
                'وضعیت بند' => $activity['isActive'] == 1 ? 'فعال' : 'غیر فعال',
                'شناسه فعالیت' => $activity['id'],
                'عنوان فعالیت' => $activity['title'],
                \Yii::t('document', 'Extention') => $activity['extention'],
                \Yii::t('document', 'Max Size') => $activity['maxSize'],
                \Yii::t('document', 'Complete Method') => $activity['completeMethod'],
            ];
            $row++;
            }
        $format = [
            '0' => 'integer',
            '1' => 'string',
            '2' => 'string',
            '3' => 'string',
            '4' => 'string',
            '5' => 'string',
            '6' => 'string',
            '7' => 'string',
            '8' => 'string',
            '9' => 'string',
            '10' => 'string',
        ];

        \app\components\Utility::exportDataAsLibxl($data, "Clause-Activity-List", "Clause-Activity-List", $format);
    }
}
