<?php

namespace app\modules\document\models;

use Yii;
use yii\db\Query;
use yii\db\Expression;
use app\modules\workflow\models\Action;
use app\modules\workflow\models\ActionEntity;
use app\modules\account\models\User;
use app\modules\account\models\UserObs;
use app\modules\OBS\models\Obsnew;
use app\modules\OBS\models\ObsProcessAction;


class StampRefundWorkflow
{
    const ACTION_REJECT = 1202;

    public function getActionHistoryData($peid)
    {
        $peid = (int)$peid;

        $userTable = User::tableName();
        $obsTable = Obsnew::tableName();
        $actionTable = Action::tableName();
        $actionEntityTable = ActionEntity::tableName();

        $actionEntityData = (new Query())
            ->from(['ae' => $actionEntityTable])
            ->leftJoin(['u' => $userTable], 'u.UserID = ae.UserIDRef')
            ->leftJoin(['o' => $obsTable], 'o.obsId = ae.OBSCategory3IDRef')
            ->leftJoin(['actionCurrent' => $actionTable], 'actionCurrent.ActionID = ae.ActionIDRef')
            ->leftJoin(['actionFrom' => $actionTable], 'actionFrom.ActionID = ae.FromActionIDRef')
            ->where(['ae.ProcessEntityIDRef' => $peid])
            ->andWhere('COALESCE(ae.isDeleted, 0) <> 1')
            ->select([
                'ae.ID',
                'ae.FromActionIDRef',
                'ae.ActionIDRef',
                'ae.ActionDate',
                'ae.ActionDateHijri',
                'ae.UserIDRef',
                'ae.OBSCategory3IDRef',
                'ae.ActionMessage',
                'ae.ActionAutoMessage',

                new Expression("CONCAT(COALESCE(u.FirstName, ''), ' ', COALESCE(u.LastName, '')) AS UserName"),

                'o.obsTitle AS OBSCategory3Title',

                new Expression("
                    CASE
                        WHEN ae.ActionIDRef = " . self::ACTION_REJECT . " THEN COALESCE(actionCurrent.ActionTitle1, 'رد درخواست')
                        WHEN ae.FromActionIDRef IS NULL THEN actionCurrent.ActionTitle1
                        ELSE actionFrom.ActionTitle1
                    END AS ActionTitle1
                "),

                new Expression("
                    CASE
                        WHEN ae.ActionIDRef = " . self::ACTION_REJECT . " THEN COALESCE(actionCurrent.ActionTitle2, 'پایان فرآیند')
                        WHEN ae.FromActionIDRef IS NULL THEN actionCurrent.ActionTitle2
                        ELSE actionFrom.ActionTitle2
                    END AS ActionTitle2
                "),

                new Expression("
                    CASE
                        WHEN ae.ActionIDRef = " . self::ACTION_REJECT . " THEN 1
                        ELSE 0
                    END AS IsRejected
                "),
            ])
            ->orderBy(['ae.ID' => SORT_ASC])
            ->all();
$actionEntityData = $this->removeDuplicateRegisterRequestRows($actionEntityData);
        /*
         * مهم:
         * اینجا دیگر حذف تکراری انجام نمی‌دهیم.
         * چون تاریخچه باید همه رخدادها را نشان دهد؛
         * رد درخواست هم یک رخداد مستقل است.
         */

        $jDate = new \app\components\jDateTime(true, true, 'Asia/Tehran');

        foreach ($actionEntityData as $index => &$row) {
            if ($index < count($actionEntityData) - 1) {
                $next = $actionEntityData[$index + 1];

                $duration = $this->calculateDuration(
                    $row['ActionDateHijri'] ?? null,
                    $next['ActionDateHijri'] ?? null,
                    $jDate
                );

                $row['Duration'] = $duration ?: '-';
            } else {
                $row['Duration'] = '-';
            }

            if ((int)($row['IsRejected'] ?? 0) === 1) {
                $row['ActionTitle1'] = $row['ActionTitle1'] ?: 'رد درخواست';
                $row['ActionTitle2'] = $row['ActionTitle2'] ?: 'پایان فرآیند';
            }

            $row['UserName'] = trim($row['UserName'] ?? '') ?: '-';
            $row['ActionTitle1'] = $row['ActionTitle1'] ?: '-';
            $row['ActionTitle2'] = $row['ActionTitle2'] ?: '-';
            $row['ActionMessage'] = $row['ActionMessage'] ?: '-';
            $row['ActionAutoMessage'] = $row['ActionAutoMessage'] ?: '-';
            $row['ActionDateHijri'] = $row['ActionDateHijri'] ?: '-';
            $row['OBSCategory3Title'] = $row['OBSCategory3Title'] ?: '-';
        }

        unset($row);

        return $actionEntityData;
    }
    private function removeDuplicateRegisterRequestRows(array $rows)
    {
        $result = [];
        $registerRequestShown = false;

        foreach ($rows as $row) {
            $actionId = (int)($row['ActionIDRef'] ?? 0);
            $fromActionId = (int)($row['FromActionIDRef'] ?? 0);

            $isReject = ($actionId === self::ACTION_REJECT);

            /*
             * ثبت درخواست معمولاً دو بار دیده می‌شود:
             * 1) ActionIDRef = 1195 و FromActionIDRef خالی
             * 2) FromActionIDRef = 1195 و ActionIDRef = مرحله بعد
             *
             * ما فقط یکی از این‌ها را نگه می‌داریم.
             * ولی اگر ActionIDRef = 1202 باشد، نباید حذف شود.
             */
            $isRegisterRequestDisplay = !$isReject && (
                ($fromActionId === 0 && $actionId === 1195) ||
                ($fromActionId === 1195)
            );

            if ($isRegisterRequestDisplay) {
                if ($registerRequestShown) {
                    continue;
                }

                $registerRequestShown = true;
            }

            $result[] = $row;
        }

        return $result;
    }
    private function calculateDuration($from, $to, $jDate)
    {
        if (!$from || !$to) {
            return null;
        }

        $fromParts = explode('/', substr($from, 0, 10));
        $toParts = explode('/', substr($to, 0, 10));

        if (count($fromParts) !== 3 || count($toParts) !== 3) {
            return null;
        }

        $timeBetween = $jDate->timeBetweenDate(
            (int)$fromParts[0],
            (int)$fromParts[1],
            (int)$fromParts[2],
            (int)$toParts[0],
            (int)$toParts[1],
            (int)$toParts[2]
        );

        $duration = '';

        if (!empty($timeBetween[0])) {
            $duration .= $timeBetween[0] . ' ماه ';
        }

        if (!empty($timeBetween[1])) {
            $duration .= $timeBetween[1] . ' روز ';
        }

        return trim($duration) ?: '0 روز';
    }

    public function getRefundWorkflowSteps($stampId, $currentActionId, $peid = null)
    {
        $stampId = (int)$stampId;
        $currentActionId = (int)$currentActionId;
        $peid = !empty($peid) ? (int)$peid : null;

        if (empty($stampId)) {
            return ['error' => 'شناسه نشان یافت نشد'];
        }

        $stampModel = StampDocument::find()
            ->where([
                'StampId' => $stampId,
                'isDeleted' => 0,
            ])
            ->one();

        if (!$stampModel) {
            return ['error' => 'سند نشان یافت نشد'];
        }

        $stampObs = Obsnew::find()
            ->where(['obsId' => $stampModel->ObsIDRef])
            ->one();

        if (!$stampObs) {
            return ['error' => 'OBS سند یافت نشد'];
        }

        $companyId = (int)$stampObs->companyIdRef;
        $licenseIssueType = (int)($stampModel->LicenseIssueType ?? 0);

        $smartPath = $this->calculateRefundSmartPath(
            $currentActionId,
            $licenseIssueType,
            $companyId,
            $peid
        );

        if (empty($smartPath) || !is_array($smartPath)) {
            return [
                'error' => 'مسیر گردش کار قابل محاسبه نیست',
                'debug' => [
                    'stampId' => $stampId,
                    'currentActionId' => $currentActionId,
                    'peid' => $peid,
                    'licenseIssueType' => $licenseIssueType,
                    'companyId' => $companyId,
                ],
            ];
        }

        /*
         * بررسی اینکه آیا این فرآیند رد شده است یا نه.
         * اگر رد شده باشد، می‌فهمیم از کدام مرحله رد شده.
         */
        $rejectInfo = $this->getRefundRejectInfo($peid, $smartPath);

        $isRejected = !empty($rejectInfo);
        $rejectedFromActionId = $isRejected ? (int)$rejectInfo['RejectedFromActionID'] : null;

        /*
         * اگر رد شده باشد، مرحله جاری نمایشی را همان مرحله‌ای می‌گیریم
         * که درخواست از آنجا رد شده است.
         */
        $displayCurrentActionId = $currentActionId;

        if (
            $isRejected &&
            !empty($rejectedFromActionId) &&
            in_array($rejectedFromActionId, $smartPath, true)
        ) {
            $displayCurrentActionId = $rejectedFromActionId;
        }

        $currentIndex = array_search($displayCurrentActionId, $smartPath, true);

        if ($currentIndex === false) {
            $currentIndex = null;
        }

        $actions = Action::find()
            ->select([
                'ActionID',
                'ActionTitle1',
                'ActionTitle2',
            ])
            ->where(['in', 'ActionID', $smartPath])
            ->andWhere([
                'or',
                ['isDeleted' => null],
                ['<>', 'isDeleted', 1],
            ])
            ->indexBy('ActionID')
            ->asArray()
            ->all();

        $steps = [];

        foreach ($smartPath as $index => $actionId) {
            $actionId = (int)$actionId;

            $isRejectedHere = (
                $isRejected &&
                !empty($rejectedFromActionId) &&
                $actionId === $rejectedFromActionId
            );

            if ($isRejected) {
                $isCurrent = $isRejectedHere;
                $isPast = ($currentIndex !== null && $index < $currentIndex);
                $isFuture = false;
                $isStopped = ($currentIndex !== null && $index > $currentIndex);
            } else {
                $isCurrent = ($displayCurrentActionId === $actionId);
                $isPast = ($currentIndex !== null && $index < $currentIndex);
                $isFuture = ($currentIndex !== null && $index > $currentIndex);
                $isStopped = false;
            }

            $previousActionId = ($index > 0) ? (int)$smartPath[$index - 1] : null;

            $steps[] = [
                'actionId' => $actionId,
                'title' => $actions[$actionId]['ActionTitle1'] ?? 'نامشخص',
                'subtitle' => $actions[$actionId]['ActionTitle2'] ?? '',

                'isCurrent' => $isCurrent,
                'isPast' => $isPast,
                'isFuture' => $isFuture,
                'isStopped' => $isStopped,

                'previousActionId' => $previousActionId,

                /*
                 * این اطلاعات را در فرانت زیر همان مرحله نمایش بده.
                 */
                'isRejectedHere' => $isRejectedHere,
                'rejectInfo' => $isRejectedHere ? [
                    'title' => $rejectInfo['RejectTitle1'] ?: 'رد درخواست',
                    'subtitle' => 'پایان فرآیند',
                    'date' => $rejectInfo['ActionDateHijri'] ?? '',
                    'userName' => trim($rejectInfo['UserName'] ?? '') ?: '',
                    'message' => $rejectInfo['ActionMessage'] ?? '',
                    'autoMessage' => $rejectInfo['ActionAutoMessage'] ?? '',
                ] : null,

                /*
                 * وقتی فرآیند رد شده، برای مراحل بعدی دیگر کاربر نمی‌خواهیم.
                 */
                'users' => ($isRejected && ($isRejectedHere || $isStopped))
                    ? []
                    : $this->getRefundUsersForAction(
                        $actionId,
                        $licenseIssueType,
                        $stampId,
                        $previousActionId,
                        $peid
                    ),
            ];
        }

        return [
            'steps' => $steps,
            'currentActionId' => $currentActionId,
            'displayCurrentActionId' => $displayCurrentActionId,
            'currentIndex' => $currentIndex,
            'licenseIssueType' => $licenseIssueType,
            'companyId' => $companyId,
            'smartPath' => $smartPath,
            'isRejected' => $isRejected,
            'rejectedFromActionId' => $rejectedFromActionId,
        ];
    }

    private function getRefundRejectInfo($peid, array $smartPath)
    {
        if (empty($peid)) {
            return null;
        }

        $reject = (new Query())
            ->from(['ae' => ActionEntity::tableName()])
            ->leftJoin(['a' => Action::tableName()], 'a.ActionID = ae.ActionIDRef')
            ->leftJoin(['u' => User::tableName()], 'u.UserID = ae.UserIDRef')
            ->where([
                'ae.ProcessEntityIDRef' => (int)$peid,
                'ae.ActionIDRef' => self::ACTION_REJECT,
            ])
            ->andWhere('COALESCE(ae.isDeleted, 0) <> 1')
            ->select([
                'ae.ID',
                'ae.FromActionIDRef',
                'ae.ActionIDRef',
                'ae.ActionDateHijri',
                'ae.ActionMessage',
                'ae.ActionAutoMessage',
                'a.ActionTitle1 AS RejectTitle1',
                'a.ActionTitle2 AS RejectTitle2',
                new Expression("CONCAT(COALESCE(u.FirstName, ''), ' ', COALESCE(u.LastName, '')) AS UserName"),
            ])
            ->orderBy(['ae.ID' => SORT_DESC])
            ->one();

        if (!$reject) {
            return null;
        }

        $rejectedFromActionId = (int)($reject['FromActionIDRef'] ?? 0);

        /*
         * حالت اصلی:
         * FromActionIDRef مشخص می‌کند رد درخواست از کدام مرحله انجام شده.
         */
        if ($rejectedFromActionId && in_array($rejectedFromActionId, $smartPath, true)) {
            $reject['RejectedFromActionID'] = $rejectedFromActionId;
            return $reject;
        }

        /*
         * حالت پشتیبان:
         * اگر FromActionIDRef خالی بود یا داخل مسیر نبود،
         * آخرین رکورد قبل از رد درخواست را پیدا می‌کنیم.
         */
        $previous = (new Query())
            ->from(ActionEntity::tableName())
            ->where([
                'ProcessEntityIDRef' => (int)$peid,
            ])
            ->andWhere('COALESCE(isDeleted, 0) <> 1')
            ->andWhere(['<', 'ID', (int)$reject['ID']])
            ->orderBy(['ID' => SORT_DESC])
            ->one();

        if ($previous) {
            $previousActionId = (int)($previous['ActionIDRef'] ?? 0);
            $previousFromActionId = (int)($previous['FromActionIDRef'] ?? 0);

            if (in_array($previousActionId, $smartPath, true)) {
                $reject['RejectedFromActionID'] = $previousActionId;
                return $reject;
            }

            if (in_array($previousFromActionId, $smartPath, true)) {
                $reject['RejectedFromActionID'] = $previousFromActionId;
                return $reject;
            }
        }

        $reject['RejectedFromActionID'] = null;

        return $reject;
    }

    private function calculateRefundSmartPath($currentActionId, $licenseIssueType, $companyId, $peid)
    {
        $licenseIssueType = (int)$licenseIssueType;

        if ($licenseIssueType === 1) {
            return [
                1195,
                1196,
                1197,
                1198,
                1199,
                1201,
            ];
        }

        if ($licenseIssueType === 2) {
            return [
                1195,
                1196,
                1197,
                1200,
                1199,
                1201,
            ];
        }

        return [];
    }

    public function getRefundUsersForAction(
        $actionId,
        $licenseIssueType,
        $stampId,
        $previousActionId = null,
        $peid = null
    ) {
        $actionId = (int)$actionId;
        $licenseIssueType = (int)$licenseIssueType;
        $stampId = (int)$stampId;
        $previousActionId = $previousActionId !== null ? (int)$previousActionId : null;
        $peid = !empty($peid) ? (int)$peid : null;

        $stampModel = StampDocument::find()
            ->where([
                'StampId' => $stampId,
                'isDeleted' => 0,
            ])
            ->one();

        if (!$stampModel) {
            return [];
        }

        $stampObs = Obsnew::find()
            ->where(['obsId' => $stampModel->ObsIDRef])
            ->one();

        if (!$stampObs) {
            return [];
        }

        $companyId = (int)$stampObs->companyIdRef;
        $categoryId = (int)$stampObs->categoryIdRef;
        $projectId = (int)$stampObs->ProjectIDRef;
        $siteId = (int)$stampObs->SiteIDRef;

        $candidateObsIds = ObsProcessAction::find()
            ->select(['OBSCategory3IDRef'])
            ->where(['ActionIDRef' => $actionId])
            ->andWhere('COALESCE(isDeleted, 0) <> 1')
            ->column();

        if (empty($candidateObsIds)) {
            return [];
        }

        $action1195TargetObsId = null;

        if ($actionId === 1195) {
            if (!empty($peid)) {
                $action1195TargetObsId = (new Query())
                    ->select(['OBSCategory3IDRef'])
                    ->from(ActionEntity::tableName())
                    ->where([
                        'ProcessEntityIDRef' => $peid,
                        'ActionIDRef' => 1195,
                    ])
                    ->andWhere('COALESCE(isDeleted, 0) <> 1')
                    ->orderBy(['ID' => SORT_DESC])
                    ->scalar();

                $action1195TargetObsId = $action1195TargetObsId !== null
                    ? (int)$action1195TargetObsId
                    : null;
            }

            if (empty($action1195TargetObsId)) {
                $action1195TargetObsId = (int)$stampModel->ObsIDRef;
            }
        }

        $obsModels = Obsnew::find()
            ->where(['in', 'obsId', $candidateObsIds])
            ->andWhere([
                'or',
                ['isActive' => null],
                ['isActive' => 1],
            ])
            ->all();

        if (empty($obsModels)) {
            return [];
        }

        $refundAccessCondition = function ($obs) use (
            $companyId,
            $categoryId,
            $projectId,
            $siteId
        ) {
            $obsCompanyId = (int)$obs->companyIdRef;
            $obsCategoryId = (int)$obs->categoryIdRef;
            $obsProjectId = (int)$obs->ProjectIDRef;
            $obsSiteId = (int)$obs->SiteIDRef;

            if ($obsCompanyId !== $companyId) {
                return false;
            }

            if ($categoryId === 2) {
                return $obsCategoryId === 2;
            }

            if ($obsCategoryId === 2) {
                return true;
            }

            if ($obsCategoryId !== 1) {
                return false;
            }

            if ($siteId === 0) {
                return (
                    $obsProjectId === $projectId &&
                    $obsSiteId === 0
                );
            }

            return (
                $obsProjectId === $projectId &&
                $obsSiteId === $siteId
            );
        };

        $legacyAccessCondition = function ($obs) use (
            $companyId,
            $categoryId,
            $projectId,
            $siteId,
            $stampObs
        ) {
            $relatedObsId = (int)$stampObs->relatedObsIdRef;

            $obsCompanyId = (int)$obs->companyIdRef;
            $obsCategoryId = (int)$obs->categoryIdRef;
            $obsProjectId = (int)$obs->ProjectIDRef;
            $obsSiteId = (int)$obs->SiteIDRef;
            $obsRelatedObsId = (int)$obs->relatedObsIdRef;

            if ($companyId !== $obsCompanyId) {
                return false;
            }

            if ($obsCompanyId === 248) {
                if ($obsCategoryId === 2) {
                    return true;
                }

                return (
                    $relatedObsId > 0 &&
                    $obsRelatedObsId > 0 &&
                    $obsRelatedObsId === $relatedObsId
                );
            }

            if ($categoryId === 2) {
                return $obsCategoryId === 2;
            }

            if ($obsCategoryId !== 1) {
                return false;
            }

            if ($siteId === 0) {
                return $obsProjectId === $projectId;
            }

            return (
                $obsProjectId === $projectId &&
                $obsSiteId === $siteId
            );
        };

        $refund1199Type1Condition = function ($obs) {
            return (int)$obs->companyIdRef === 248;
        };

        $filteredObsIds = [];
        $fullTitleMap = [];

        foreach ($obsModels as $obs) {
            $keep = false;

            switch ($actionId) {
                case 1195:
                    $keep = (
                        !empty($action1195TargetObsId) &&
                        (int)$obs->obsId === (int)$action1195TargetObsId
                    );
                    break;

                case 1197:
                case 1198:
                    $keep = true;
                    break;

                case 1196:
                
                case 1200:
                    $keep = $refundAccessCondition($obs);
                    break;

                case 1199:
                    if ($licenseIssueType === 1) {
                        $keep = $refund1199Type1Condition($obs);
                    } elseif ($licenseIssueType === 2) {
                        $keep = $refundAccessCondition($obs);
                    }
                    break;

                case 1201:
                case 1202:
                    $keep = $legacyAccessCondition($obs);
                    break;

                default:
                    $keep = false;
                    break;
            }

            if (!$keep) {
                continue;
            }

            $obsId = (int)$obs->obsId;

            $filteredObsIds[] = $obsId;

            $fullTitleMap[$obsId] = !empty($obs->fullTitle)
                ? $obs->fullTitle
                : (!empty($obs->obsTitle) ? $obs->obsTitle : 'نامشخص');
        }

        $filteredObsIds = array_values(array_unique($filteredObsIds));

        if (empty($filteredObsIds)) {
            return [];
        }

        $userObsData = UserObs::find()
            ->select([
                'UserIDRef',
                'OBSCategory3IDRef',
            ])
            ->where(['in', 'OBSCategory3IDRef', $filteredObsIds])
            ->andWhere(['isActive' => 1])
            ->andWhere('COALESCE(isDeleted, 0) <> 1')
            ->asArray()
            ->all();

        if (empty($userObsData)) {
            return [];
        }

        $userIds = array_values(array_unique(array_column($userObsData, 'UserIDRef')));

        if (empty($userIds)) {
            return [];
        }

        $users = User::find()
            ->select([
                'UserID',
                'FirstName',
                'LastName',
            ])
            ->where(['in', 'UserID', $userIds])
            ->andWhere(['isActive' => 1])
            ->andWhere('COALESCE(isDeleted, 0) <> 1')
            ->asArray()
            ->all();

        if (empty($users)) {
            return [];
        }

        $userInfoMap = [];

        foreach ($users as $user) {
            $userInfoMap[(int)$user['UserID']] = $user;
        }

        $userPositionTitleMap = [];

        foreach ($userObsData as $userObs) {
            $userId = (int)$userObs['UserIDRef'];
            $obsId = (int)$userObs['OBSCategory3IDRef'];

            if (
                !isset($userPositionTitleMap[$userId]) &&
                isset($fullTitleMap[$obsId])
            ) {
                $userPositionTitleMap[$userId] = $fullTitleMap[$obsId];
            }
        }

        $result = [];

        foreach ($userIds as $userId) {
            $userId = (int)$userId;

            if (!isset($userInfoMap[$userId])) {
                continue;
            }

            $firstName = trim($userInfoMap[$userId]['FirstName'] ?? '');
            $lastName = trim($userInfoMap[$userId]['LastName'] ?? '');

            $fullName = trim($firstName . ' ' . $lastName);

            $result[] = [
                'id' => $userId,
                'FirstName' => $firstName,
                'LastName' => $lastName,
                'name' => $fullName !== '' ? $fullName : 'نامشخص',
                'positionTitle' => $userPositionTitleMap[$userId] ?? 'نامشخص',
            ];
        }

        return $result;
    }
}