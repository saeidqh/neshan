<?php

use app\components\PageHeader;
use app\components\GridViewBox;
use yii\widgets\Pjax;
use yii\helpers\Html;
use app\modules\document\models\Clause;
use omnilight\assets\SweetAlertAsset;
use app\modules\account\assets\UserAsset;

SweetAlertAsset::register($this);
UserAsset::register($this);

$this->title = Yii::t('document', 'Standard Clause');

$canCreateAndUpdateClause = app\access\AccessControl::can('document/clause/create');
?>

<?=
PageHeader::widget([
    'title' => $this->title,
    'icon' => 'en-flow-cascade'
]);
?>
<style>
    #clause-grid-pjax thead,
    #clause-grid-pjax .summary {
        background-color: #1dd6cd;
    }

    #clause-grid-pjax * {
        font-size: 12px;
    }

    .grid-box table th,
    .grid-box table td {
        font-size: 12px !important;
    }

    #clause-grid-pjax table tbody td:nth-child(3) {
        max-width: 400px !important;
        overflow-x: auto;
    }
</style>
<?php GridViewBox::begin([]); ?>
<div class="row">
    <div class="col-md-4 pull-right text-right grid-box-buttons">
        <div class="btn-group ">
            <button type="button" class="btn btn-sm btn-success" data-toggle="dropdown"><i class="im-file-excel"></i>&nbsp;&nbsp;Excel <span class="caret"></span></button>
            <ul class="dropdown-menu text-left" role="menu" style="z-index: 1100;">
                </li>
                <li>
                    <?=
                    yii\bootstrap\Html::a('فعالیت ها', '#', [
                        'target' => '_blank',
                        'export-url' => Yii::$app->urlManager->createAbsoluteUrl(["document/export/clause-activity"]),
                        'onClick' => 'exportExcelFile(this);',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'left',
                        'data-pjax' => 0,
                        'data-original-title' => 'ActionID: 243',
                    ]);
                    ?>
                </li>
                <li>
                    <?=
                    yii\bootstrap\Html::a('بندها و زیربندها', '#', [
                        'target' => '_blank',
                        'export-url' => Yii::$app->urlManager->createAbsoluteUrl(["document/export/clause"]),
                        'onClick' => 'exportExcelFile(this);',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'left',
                        'data-pjax' => 0,
                        'data-original-title' => 'ActionID: 242',
                    ]);
                    ?>
            </ul>
        </div>
        <div class="btn-group">
            <?php if ($canCreateAndUpdateClause) { ?>
                <button class="btn btn-sm btn-primary pull-right ml5"
                        data-url="<?= Yii::$app->urlManager->createAbsoluteUrl(["document/clause/create"]) ?>"
                        onclick="showClauseModal(this)"><i
                        class=""></i> <?= Yii::t('document', 'New Clause') ?> </button>
                    <?php
                }
                echo Html::a(\Yii::t("document", "Standard/Model"), Yii::$app->urlManager->createAbsoluteUrl(['document/standard/list']), [
                    'class' => 'btn btn-sm btn-mini btn-primary',
                    'data-pjax' => '0',
                        ]
                );
                ?>
        </div>
    </div>
</div>
<div class='row'>

    <?php Pjax::begin(); ?>
    <?php
    echo \kartik\grid\GridView::widget([
        'id' => 'clause-grid',
        'dataProvider' => $clauseDataProvider,
        'filterModel' => $clauseSearchModel,
        'hover' => true,
        'pjax' => true,
        'pjaxSettings' => [
            'options' => [
                'id' => 'clause-grid-pjax',
            ],
        ],
        'columns' => [
            [
                'class' => \kartik\grid\ExpandRowColumn::className(),
                'value' => function ($url, $model, $key) {
                    return \kartik\grid\GridView::ROW_COLLAPSED;
                },
                'detailUrl' => Yii::$app->urlManager->createAbsoluteUrl(['document/clause/details']),
                /* 'detailUrl' => function ($model, $key, $index, $column) {
                  return Yii::$app->urlManager->createAbsoluteUrl(['document/clause/details', 'clauseIdRef' => $model->id]);
                  }, */
                'headerOptions' => ['class' => 'kartik-sheet-style'],
                'expandOneOnly' => true,
                'expandIcon' => '<span class="fa-plus"></span>',
                'collapseIcon' => '<span class="fa-minus"></span>',
            ],
            [
                'attribute' => 'id',
                'options' => [
                    'width' => '70px'
                ],
            ],
            [
                'attribute' => 'standard',
                'format' => 'raw',
            ],
            [
                'attribute' => 'title',
            ],
            [
                'attribute' => 'type',
                'filter' => Clause::getTypeList(),
                'filterOptions' => ['options' => ['prompt' => 'select']],
                'format' => 'raw',
                'value' => function ($model) {
                    return Clause::getType($model->type);
                }
            ],
            /* [
              'attribute' => 'mainClause',
              'options' => [
              'width' => '100px'
              ],
              ],
              [
              'attribute' => 'subClause',
              'options' => [
              'width' => '100px'
              ],
              ], */
            [
                'attribute' => 'fullClauseNo',
            ],
            [
                'attribute' => 'description',
            ],
            [
                'attribute' => 'codifiedInformation',
                'filter' => Clause::getCodifiedInformationList(),
                'filterOptions' => ['options' => ['prompt' => 'select']],
                'format' => 'raw',
                'value' => function ($model) {
                    return Clause::getCodifiedInformation($model->codifiedInformation);
                }
            ],
            [
                'attribute' => 'activityCount',
                'label' => 'تعداد فعالیت',
                'options' => [
                    'width' => '60px'
                ],
            ],
            [
                'attribute' => 'activityFileCount',
                'label' => 'تعداد فایل نمونه',
                'options' => [
                    'width' => '60px'
                ],
            ],
            [
                'attribute' => 'weight',
                'options' => [
                    'width' => '60px'
                ],
            ],
            [
                'attribute' => 'sort',
                'options' => [
                    'width' => '100px'
                ],
            ],
            [
                'attribute' => 'isActive',
                'options' => [
                    'width' => '200px'
                ],
                'filter' => Clause::getIsActiveList(),
                'filterOptions' => ['options' => ['prompt' => Yii::t('automation', 'Active')]],
                'format' => 'raw',
                'value' => function ($data) {
                    return "<span class='" . (($data['isActive'] == 1) ? 'text-success' : 'text-danger') . "'><strong>" . (($data['isActive'] == 1) ? Yii::t('automation', 'Active') : Yii::t('automation', 'In Active')) . "</strong></span>";
                }
            ],
            [
                'class' => '\kartik\grid\ActionColumn',
                'template' => '{update}',
                'buttons' => [
                    'update' => function ($key, $model, $index) use ($canCreateAndUpdateClause) {
                        if ($canCreateAndUpdateClause) {
                            return \yii\helpers\Html::a('<i class="fa-pencil s16"></i>', '#', [
                                        'data-toggle' => 'tooltip',
                                        'data-placement' => 'top',
                                        'data-original-title' => 'Edit Certificate',
                                        'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/clause/update', 'id' => $model->id]),
                                        'onclick' => 'showClauseModal(this)'
                                            ]
                                    );
                        }
                    },
                ],
            ]
        ]
    ]);
    ?>
    <?php Pjax::end(); ?>
</div>
<?php GridViewBox::end(); ?>

<?php
yii\bootstrap\Modal::begin([
    'id' => 'clause-modal',
    'header' => Yii::t('document', 'Standard Clause'),
    'size' => yii\bootstrap\Modal::SIZE_LARGE,
    'options' => [
        'tabindex' => '',
    ]
]);
?>
<?php Pjax::begin(['id' => 'clause-form-pjax']); ?>

<?php Pjax::end(); ?>
<?php yii\bootstrap\Modal::end(); ?>

<?php
yii\bootstrap\Modal::begin([
    'id' => 'clause-activity-modal',
    'header' => Yii::t('document', 'Clause Activity'),
    'size' => yii\bootstrap\Modal::SIZE_LARGE,
    'options' => [
        'tabindex' => '',
    ]
]);
?>
<?php Pjax::begin(['id' => 'clause-activity-form-pjax']); ?>

<?php Pjax::end(); ?>
<?php yii\bootstrap\Modal::end(); ?>

<?php
$js = "
      $('.modal-content').draggable();
    ";
$this->registerJs($js);
?>

<script>

    function showClauseModal(obj) {
        ajaxProgressLoader('show', '.modal-content');
        var url = $(obj).attr('data-url');
        $.ajax({
            url: url,
            dataType: 'html'
        }).done(function (data) {
            $('#clause-form-pjax').html(data);
            $('#clause-modal').modal('show');
            ajaxProgressLoader('hide', '.modal-content');
        }).error(function () {
            showMsgBoxInBottomRight('Server Error', 'error');
            return false;
        });
    }

    function clauseCallbackPjax(data) {
        if (data['result']) {
            $('#clause-modal').modal('hide');
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
            container: '#clause-grid-pjax',
            timeout: false,
            push: false,
            replace: false,
        });
    }

    function showClauseActivityModal(obj) {
        ajaxProgressLoader('show', '.modal-content');
        var url = $(obj).attr('data-url');
        $.ajax({
            url: url,
            dataType: 'html'
        }).done(function (data) {
            $('#clause-activity-form-pjax').html(data);
            $('#clause-activity-modal').modal('show');
            ajaxProgressLoader('hide', '.modal-content');
        }).error(function () {
            showMsgBoxInBottomRight('Server Error', 'error');
            return false;
        });
    }

    function clauseActivityCallbackPjax(data) {
        if (data['result']) {
            $('#clause-activity-modal').modal('hide');
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

    function deleteActivity(id) {
        ajaxProgressLoader('show', 'clause-grid-pjax');
        var obj = $('#btn-del-activity-' + id);
        var url = $(obj).attr('data-url');
        $.ajax({
            url: url,
            dataType: "json"
        }).done(function (data) {
            if (data.result == 1) {
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
                swal('Error', data.message, 'error');
            }
        }).error(function () {
            showMsgBoxInBottomRight('Error on connecting to server. <br />Please login again and try later!', 'error');
            return false;
        });
        ajaxProgressLoader('hide', '.obs-list .row');
        return false;

    }

    function deleteActivityFile(id) {
        ajaxProgressLoader('show', 'clause-grid-pjax');
        var obj = $('#btn-del-activity-file-' + id);
        var url = $(obj).attr('data-url');
        $.ajax({
            url: url,
            dataType: "json"
        }).done(function (data) {
            if (data.result == 1) {
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
                swal('Error', data.message, 'error');
            }
        }).error(function () {
            showMsgBoxInBottomRight('Error on connecting to server. <br />Please login again and try later!', 'error');
            return false;
        });
        ajaxProgressLoader('hide', '.obs-list .row');
        return false;
    }
    
    function getClauseSearchFilter() {
        var inputFilter = $('#clause-grid-filters input').serialize();
        var dropdownFilter = "ClauseSearch[type]=" + $('#clausesearch-type').val();
        dropdownFilter += '&ClauseSearch[codifiedInformation]=' + $('#clausesearch-codifiedinformation').val();
        dropdownFilter += '&ClauseSearch[isActive]=' + $('#clausesearch-isactive').val();
//        var advancedFilter = $('#advancedFilterForm').serialize();
//        advancedFilter = advancedFilter.replace('r=contract%2Fcontract%2Flist', '');
//           + '&' + advancedFilter
        return '&' + inputFilter;
    }

    function exportExcelFile(selector) {
        var exportUrl = $(selector).attr('export-url') + getClauseSearchFilter();
        $(selector).attr('href', exportUrl);
    }

</script>
