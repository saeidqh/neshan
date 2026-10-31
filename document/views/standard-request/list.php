<?php

use app\components\PageHeader;
use app\components\GridViewBox;
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\Utility;
use app\assets\DatepickerAsset;
use app\assets\JdateTimeAsset;
use app\modules\tender\assets\TenderAsset;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use omnilight\assets\SweetAlertAsset;

const IM_USER = 'im-user';
DatepickerAsset::register($this);
SweetAlertAsset::register($this);
JdateTimeAsset::register($this);
TenderAsset::register($this);
/* Yii::$app->view->on(\yii\web\View::EVENT_END_BODY, function () {
  Yii::$app->view->registerAssetBundle(app\modules\tender\assets\InvoiceGridViewAsset::className());
  }); */
$this->title = "Standard Request";
$obsId = Yii::$app->session->get('ObsPosition');
$obsModel = app\modules\OBS\models\Obsnew::find()->where(['obsId' => $obsId])->one();
$isCandoAdmin = \app\components\Security::isCandoAdmin();
$canCreate = app\access\AccessControl::can('document/standard-request/create');
$standardArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\Standard::find()->where(['isActive' => 1])->orderBy(['sort' => SORT_ASC])->asArray()->all(), 'id', 'title');

$project = \app\modules\wbs\models\Epsnew::find()->joinWith('company c')->alias('e')->where(['e.EPSType' => 'Project', 'e.executionType' => 'executive'])->andWhere("COALESCE(isActive,'')<>'inactive'")
                        ->addSelect(['EPSID', 'EPSTitle' => "concat(IFNULL(projectCode,''), ' - ', IFNULL(EPSTitle,''))"]);
if (!$isCandoAdmin && $obsModel->companyIdRef != 248) {
    $project->andWhere(['c.PersonID' => $obsModel->companyIdRef]);
}
$projectArray = \yii\helpers\ArrayHelper::map($project->all(), 'EPSID', 'EPSTitle');
?>
<style>
    #standard-request-grid-pjax .table-bordered thead th {
        background-color: #0daf50ff !important;
    }

    #standard-request-grid-pjax .kv-expand-detail-row .table-bordered thead th {
        background-color: #85f38aff !important;
    }
</style>
<?=
PageHeader::widget([
    'title' => $this->title,
    'icon' => IM_USER,
]);
?>
<div class="row">
    <?php GridViewBox::begin(); ?>
    <div class="row">
        <div class="col-md-4 col-sm-12 pull-left text-left" style="margin-top:-5px;margin-bottom: 5px;">
            <div class="btn-group">
                <?php 
                echo Html::a(\Yii::t("document", "Certificate List"), Yii::$app->urlManager->createAbsoluteUrl(['document/certificate-document/list']), [
                    'class' => 'btn btn-sm btn-mini btn-primary',
                    'data-pjax' => '0',
                        ]
                );
                if ($canCreate) { ?>
                    <button class="btn btn-sm btn-primary pull-right ml5"
                            data-url="<?= Yii::$app->urlManager->createAbsoluteUrl(["document/standard-request/create"]) ?>"
                            onclick="showStandardRequestModal(this)">
                        <i class="fa-plus"></i>  درخواست ممیزی </button>
                <?php }?>
            </div>
        </div>

    </div>
    <div class="row">
        <?php Pjax::begin(['id' => 'standard-request-grid-pjax']); ?>
        <?=
        \kartik\grid\GridView::widget([
            'id' => 'standard-request-grid',
            'filterModel' => $standardSearchModel,
            'dataProvider' => $standardDataProvider,
            // 'options' => ['style' => 'background-color: #0daf6cff;'],
            'rowOptions' => function ($model, $key, $index, $grid) {
                $id = $model['id'];
                $class = $index % 2 ? 'odd' : 'even';
                return array('index' => $index, 'class' => $class . ' dblclick', 'id' => 'row-' . $id);
            },
            'floatHeader' => true,
            'columns' => [
                [
                    'class' => \kartik\grid\ExpandRowColumn::className(),
                    'value' => function ($url, $model, $key) {
                        return \kartik\grid\GridView::ROW_COLLAPSED;
                    },
                    'detailUrl' => Yii::$app->urlManager->createAbsoluteUrl(['document/standard-request-activity/list']),
                    // 'detailUrl' => function($model, $key, $index) {
                    //     return Url::to(['standard-request-activity/list', 'cid'=> $model->id]);
                    // },

                    'headerOptions' => ['class' => 'kartik-sheet-style'],
                    'expandOneOnly' => false,
                    'expandIcon' => '<span class="fa-plus"></span>',
                    'collapseIcon' => '<span class="fa-minus"></span>',
                ],
                [
                    'attribute' => 'id',
                    'label' => Yii::t('automation', 'ID'),
                    'options' => [
                        'width' => '60px',
                    ],
                ],
                [
                    'attribute' => 'companyIdRef',
                    'label' => Yii::t('automation', 'Company'),
                    'value' => function ($model) {
                        return $model->company->companyName;
                    },
                    'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'data' => $companyList,
                        'options' => ['placeholder' => 'انتخاب شرکت'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'width' => '200'
                        ]
                    ],
                ],
                [
                    'attribute' => 'projectIdRef',
                    'value' => function ($model) {
                        return $model->projectTitle;
                    },
                    'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'data' => $projectArray,
                        'options' => ['placeholder' => Yii::t('document', 'Project Scope')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ]
                    ],
                ],
                [
                    'attribute' => 'standard',
                    'label' => Yii::t('document', 'Standard'),
                    'value' => function ($model) {
                        return $model->standardTitle;
                    },
                    'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'data' => $standardArray,
                        'options' => ['placeholder' => Yii::t('document', 'Standard')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ]
                    ],
                ],
                [
                    'attribute' => 'type',
                    'label' => Yii::t('document', 'Request Type'),
                    'value' => function ($model) {
                        return app\modules\document\models\StandardRequest::getType($model->type);
                    },
                    'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'data' => app\modules\document\models\StandardRequest::getTypeList(),
                        'options' => ['placeholder' => Yii::t('document', 'Request Type')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ]
                    ],
                ],
                [
                    'attribute' => 'requestDate',
                    'label' => Yii::t('document', 'Request Date'),
                    'format' => 'raw',
                    'value' => function ($model) {
                        return '<table class="inner-table">'
                        . '<tr><td width=\'200\'>'
                        . $model->requestDate
                        . '<tr><td >'
                        . $model->createdUser->fullName
                        . '</table>';
                    },
                ],
                [
                    'attribute' => 'auditStartDate',
                ],
                [
                    'attribute' => 'auditFinishDate',
                ],
                [
                    'attribute' => 'managerialDate',
                    'label' => 'تایید مدیرعامل',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return '<table class="inner-table">'
                        . '<tr><td width=\'200\'>'
                        . $model->managerialDate
                        . '<tr><td >'
                        . (!empty($model->managerialUser) ? $model->managerial->fullName : '')
                        . '</table>';
                    },
                ],
                [
                    'attribute' => 'holdingDate',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return '<table class="inner-table">'
                        . '<tr><td width=\'200\'>'
                        . $model->holdingDate
                        . '<tr><td >'
                        . (!empty($model->holdingUserIdRef) ? $model->holdingUser->fullName : '')
                        . '</table>';
                    },
                ],
                /*[
                    'attribute' => 'auditDate',
                ],
                [
                    'attribute' => 'result',
                    'value' => function ($model) {
                        return !empty($modelresult) ? app\modules\document\models\StandardRequest::getResultType($model->type) : '';
                    },
                    'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'data' => app\modules\document\models\StandardRequest::getResultList(),
                        'options' => ['placeholder' => Yii::t('document', 'Standard Result')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ]
                    ],
                ],*/
                [
                    'attribute' => 'auditPlanStartDate',
                ],
                [
                    'attribute' => 'auditPlanFinishDate',
                ],
                [
                    'class' => yii\grid\ActionColumn::className(),
                    'header' => Yii::t('workflow', 'Actions'),
                    'template' => '{update}{verified}',
                    'options' => [
                        'width' => '100px;',
                    ],
                    'buttons' => [
                        'update' => function ($key, $model, $index) use ($canCreate, $obsModel, $isCandoAdmin) {
                            if ($isCandoAdmin || ($canCreate && $model->isLock == 0 && ($obsModel->companyIdRef == 248 || ($obsModel->companyIdRef == $model->companyIdRef && empty($model->managerialDate) && empty($model->holdingDate))))) {
                                return \yii\helpers\Html::a('<i class="fa-pencil s16"></i>', '#', [
                                            'class' => 'btn btn-xs ',
                                            'data-toggle' => 'tooltip',
                                            'data-placement' => 'top',
                                            'title' => 'ویرایش',
                                            'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/standard-request/update', 'id' => $model->id]),
                                            'onclick' => 'showStandardRequestModal(this)'
                                                ]
                                        );
                            }
                        },
                        'verified' => function ($key, $model, $index) use ($obsModel, $isCandoAdmin) {
                            if ($isCandoAdmin || ($model->isLock == 0 && empty($obsModel->parentObsIdRef) && $obsModel->companyIdRef == $model->companyIdRef && (empty($model->managerialDate) || empty($model->holdingDate)))) {
                                $iconClass = !empty($model->managerialDate) ? 'im-checkbox-checked' : 'im-checkbox-unchecked';
                                return kartik\helpers\Html::a('<i class="' . $iconClass . ' s15"></i>', '#', [
                                            'data-pjax' => '0',
                                            'title' => 'Verified Human Resource',
                                            'data-toggle' => 'tooltip',
                                            'data-placement' => 'top',
                                            'title' => 'وضعیت(تایید مدیرعامل)',
                                            'data-url' => Yii::$app->urlManager->createAbsoluteUrl(["document/standard-request/managerial-verified", 'id' => $model->id]),
                                            'onclick' => 'confirmBox("Are you sure to change Verified?!", "Yes!", "changeVerified(' . $model->id . ')");',
                                ]);
                            }
                        },
                    ]
                ]
            ],
        ]);
        ?>
        <?php Pjax::end(); ?>
    </div>
    <?php GridViewBox::end() ?>
</div>

<?php
yii\bootstrap\Modal::begin([
    'id' => 'standard-request-modal',
    'header' => Yii::t('document', 'Standard Request'),
    'size' => yii\bootstrap\Modal::SIZE_LARGE,
    'options' => [
        'tabindex' => false,
    ]
]);
?>
<?php Pjax::begin(['id' => 'standard-request-form-pjax']); ?>

<?php Pjax::end(); ?>
<?php yii\bootstrap\Modal::end(); ?>

<?php
yii\bootstrap\Modal::begin([
    'id' => 'standard-request-activity-modal',
    'header' => Yii::t('document', 'Standard Request'),
    'options' => [
        'tabindex' => false,
    ]
]);
?>
<?php Pjax::begin(['id' => 'standard-request-activity-form-pjax']); ?>

<?php Pjax::end(); ?>
<?php yii\bootstrap\Modal::end(); ?>

<?php
$js = "
      $('.modal-content').draggable();
    ";
$this->registerJs($js);
?>

<script>

    function showStandardRequestModal(obj) {
        ajaxProgressLoader('show', 'standard-request-modal .modal-content');
        var url = $(obj).attr('data-url');
        $.ajax({
            url: url,
            dataType: 'html'
        }).done(function (data) {
            $('#standard-request-form-pjax').html(data);
            $('#standard-request-modal').modal('show');
            ajaxProgressLoader('hide', '#standard-request-modal .modal-content');
        }).error(function () {
            showMsgBoxInBottomRight('Server Error', 'error');
            return false;
        });
    }

    function standardRequestCallbackPjax(data) {
        if (data['result']) {
            $('#standard-request-modal').modal('hide');
            swal({
                title: data.message,
                type: 'success',
                text: 'Are you want to reload data?',
                confirmButtonText: "Reload",
                showConfirmButton: true,
                showCancelButton: true,
            }, function (isConfirm) {
                if (isConfirm) {
                    reloadForm();
                }
            });
        } else {
            showMsgBoxInBottomRight(data['message'], 'error');
        }
    }

    function reloadForm() {
        $.pjax.reload({
            container: '#standard-request-grid-pjax',
            timeout: false,
            push: false,
            replace: false,
        });
    }

    function changeVerified(id) {
        ajaxProgressLoader('show');
        var url = '<?= Yii::$app->urlManager->createAbsoluteUrl(['document/standard-request/managerial-verified']) ?>' + "&id=" + id;
        $.ajax({
            url: url,
            dataType: "json"
        }).done(function (data) {
            if (data.result == 1) {
                swal('success', data.message, 'success');
                reloadForm();
            } else {
                swal('Error', data.message, 'error');
            }
        }).error(function () {
            showMsgBoxInBottomRight('Error on connecting to server. <br />Plase login again and try later!', 'error');
            return false;
        });
        ajaxProgressLoader('hide');
        return false;

    }

    function showStandardRequestActivityModal(obj) {
        ajaxProgressLoader('show', '#standard-request-activity-modal .modal-content');
        var url = $(obj).attr('data-url');
        $.ajax({
            url: url,
            dataType: 'html'
        }).done(function (data) {
            $('#standard-request-activity-form-pjax').html(data);
            $('#standard-request-activity-modal').modal('show');
            ajaxProgressLoader('hide', '#standard-request-activity-modal .modal-content');
        }).error(function () {
            showMsgBoxInBottomRight('Server Error', 'error');
            return false;
        });
    }

    function standardRequestActivityCallbackPjax(data) {
        if (data['result']) {
            $('#standard-request-activity-modal').modal('hide');
            swal({
                title: data.message,
                type: 'success',
                text: 'Are you want to reload data?',
                confirmButtonText: "Reload",
                showConfirmButton: true,
                showCancelButton: true,
            }, function (isConfirm) {
                if (isConfirm) {
                    location.reload();
                }
            });
        } else {
            showMsgBoxInBottomRight(data['message'], 'error');
        }
    }

</script>


