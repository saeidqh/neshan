<?php

namespace app\modules\document\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use app\modules\OBS\models\Obsnew;
use app\modules\workflow\models\ProcessEntity;
use app\modules\workflow\models\Process;

class StampRefundsSearch extends StampDocument
{
    public $obsFullTitle;
    public $stampTypeName;
    public $parentObsTitle;

    public function rules()
    {
        return [
            [[
                'StampId', 'StampTitle', 'StampTypeIDRef', 'OrganizationalMaker',
                'obsFullTitle', 'stampTypeName', 'parentObsTitle',
            ], 'safe'],
        ];
    }

    public function search($params = [])
    {
        $isCandoAdmin = \app\access\AccessControl::isAccess([
            'obs', 'check' => 'isCandoAdmin', 'errorException' => false,
        ]);

        $processEntityTable = ProcessEntity::tableName();
        $processTable = Process::tableName();

        $query = StampDocument::find()
            ->alias('s')
            ->select(['s.*'])
            ->joinWith(['obs o', 'stampType st'])
            ->where(['s.isDeleted' => 0, 's.Status' => StampDocument::STATUS_APPROVED]);

        if (!$isCandoAdmin) {
            $obsId = Yii::$app->session->get('ObsPosition');
            $currentObs = Obsnew::findOne($obsId);
            if (!$currentObs) {
                throw new \yii\web\BadRequestHttpException('OBS معتبر نیست.');
            }

            $companyId = (int) $currentObs->companyIdRef;
            $categoryId = (int) $currentObs->categoryIdRef;
            $projectId = (int) $currentObs->ProjectIDRef;
            $siteId = (int) $currentObs->SiteIDRef;

            $query->andWhere(['o.companyIdRef' => $companyId]);

            if ($categoryId === 1 && $projectId > 0 && $siteId === 0) {
                $query->andWhere(['o.categoryIdRef' => 1, 'o.ProjectIDRef' => $projectId]);
            } elseif ($categoryId === 1 && $projectId > 0 && $siteId > 0) {
                $query->andWhere([
                    'o.categoryIdRef' => 1,
                    'o.ProjectIDRef' => $projectId,
                    'o.SiteIDRef' => $siteId,
                ]);
            } elseif ($categoryId !== 2) {
                $query->andWhere('0 = 1');
            }
        }

        $query->andWhere([
            'not exists',
            (new Query())
                ->from(['pe' => $processEntityTable])
                ->innerJoin(['p' => $processTable], 'p.ProcessID = pe.ProcessIDRef')
                ->where('pe.ObjectIDRef = s.StampId')
                ->andWhere([
                    'p.ProccessTypeIDRef' => Process::REFUND_STAMP_PROCESS_TYPE,
                    'pe.isDeleted' => 0,
                ]),
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return new ActiveDataProvider([
                'query' => $query->orderBy(['s.StampId' => SORT_DESC]),
                'pagination' => ['pageSize' => Yii::$app->params['pageSize'] ?? 20],
            ]);
        }

        $query
            ->andFilterWhere(['like', 's.StampId', $this->StampId])
            ->andFilterWhere(['s.StampTypeIDRef' => $this->StampTypeIDRef])
            ->andFilterWhere(['s.OrganizationalMaker' => $this->OrganizationalMaker])
            ->andFilterWhere(['like', 's.StampTitle', $this->StampTitle])
            ->andFilterWhere(['like', 'o.obsTitle', $this->obsFullTitle]);

        return new ActiveDataProvider([
            'query' => $query->orderBy(['s.StampId' => SORT_DESC]),
            'pagination' => ['pageSize' => Yii::$app->params['pageSize'] ?? 20],
        ]);
    }

    public static function getOrganizationalMakerList()
    {
        return [
            1 => Yii::t('document', 'رده بالا دستی'),
            2 => Yii::t('document', 'رده'),
        ];
    }

    public static function getStampTypeList()
    {
        return \yii\helpers\ArrayHelper::map(
            StampTypeDocument::find()->all(),
            'StampTypeId',
            'TypeName'
        );
    }
}