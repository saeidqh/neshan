<?php

use app\components\PageHeader;
use app\components\GridViewBox;
use yii\widgets\Pjax;
use kartik\helpers\Html;
use omnilight\assets\SweetAlertAsset;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\components\Utility;
use app\modules\account\assets\UserAsset;

SweetAlertAsset::register($this);
UserAsset::register($this);

$this->title = Yii::t('document', 'Process Code List');

$canCreateAndUpdateProcessCode = app\access\AccessControl::can('document/process-code/create');
$canViewProcessCodeAction = app\access\AccessControl::can('document/process-code-action/list');
?>

<?=
PageHeader::widget([
    'title' => $this->title,
    'icon' => 'en-flow-cascade'
]);
?>
<style>
    #process-grid-pjax thead,
    #process-grid-pjax .summary {
        background-color: #1dd6cd;
    }

    #process-grid-pjax * {
        font-size: 12px;
    }

    .grid-box table th,
    .grid-box table td {
        font-size: 12px !important;
    }

    #process-grid-pjax table tbody td:nth-child(3) {
        max-width: 400px !important;
        overflow-x: auto;
        /*display: block;*/
    }
</style>
<?php GridViewBox::begin([]); ?>
<div class="row">
    <div class="col-md-4 pull-right text-right grid-box-buttons">
        <div class="btn-group">
            <?php if ($canCreateAndUpdateProcessCode) { ?>
                <button class="btn btn-sm btn-primary pull-right ml5"
                        data-url="<?= Yii::$app->urlManager->createAbsoluteUrl(["document/process-code/create"]) ?>"
                        onclick="showProcessModal(this)"><i
                        class=""></i> <?= Yii::t('document', 'New Process Code') ?> </button>
                <?php } ?>
        </div>
    </div>
</div>
<div class='row'>

    <?php Pjax::begin(); ?>
    <?php
    echo \kartik\grid\GridView::widget([
        'dataProvider' => $processCodeDataProvider,
        'filterModel' => $processCodeModel,
        'hover' => true,
        'pjax' => true,
        'pjaxSettings' => [
            'options' => [
                'id' => 'process-grid-pjax',
            ],
        ],
        'columns' => [
            [
                'attribute' => 'id',
                'options' => [
                    'width' => '70px'
                ],
            ],
            [
                'attribute' => 'code',
                'options' => [
                    'width' => '150px'
                ],
            ],
            [
                'attribute' => 'PersianTitle',
            ],
            [
                'attribute' => 'Title',
            ],
            [
                'label' => \Yii::t("document", 'Process Group'),
                'attribute' => 'ProcessGroup',
                'value' => function ($model) {
                    return $model->getProcessGroupLabel();
                },
                'filter' => $processCodeModel::getProcessGroupList(),
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
                'filter' => $processCodeModel->getStatus(),
                'filterOptions' => ['options' => ['prompt' => Yii::t('document', 'Active')]],
                'format' => 'raw',
                'value' => function ($data) {
                    return "<span class='" . (($data['isActive'] == 1) ? 'text-success' : 'text-danger') . "'><strong>" . (($data['isActive'] == 1) ? Yii::t('document', 'Active') : Yii::t('document', 'In Active')) . "</strong></span>";
                }
            ],
            [
                'class' => '\kartik\grid\ActionColumn',
                'template' => '{update}{form}{workflow}',
                'buttons' => [
                    'update' => function ($key, $model, $index) use ($canCreateAndUpdateProcessCode) {
                        if ($canCreateAndUpdateProcessCode) {

                            return \yii\helpers\Html::a('<i class="fa-pencil s16"></i>', '#', [
                                        'data-toggle' => 'tooltip',
                                        'data-placement' => 'top',
                                        'title' => 'ویرایش',
                                        'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/process-code/update', 'id' => $model->id]),
                                        'onclick' => 'showProcessModal(this)'
                                            ]
                            );
                        }
                    },
                    'form' => function ($url, $model, $key) use ($canViewProcessCodeAction) {
                        if ($canViewProcessCodeAction) {
                            return Html::a('<i class="st-user2 s16"></i>', Url::to(['/document/process-code-action/list', 'pcId' => $model->id]), [
                                        'title' => 'تخصیص فرم',
                                        'class' => 'btn btn-xs btn-link',
                                        'data-pjax' => '0',
                                        'style' => 'padding:1px 2px;color:#AE85BE;',
                                        'target' => '_blank',
                                        'data-toggle' => 'tooltip'
                            ]);
                        }
                    },
                    'workflow' => function ($url, $model, $key) use ($canViewProcessCodeAction) {
                        if ($canViewProcessCodeAction) {
                            return Html::a('<i class="en-flow-tree s16"></i>', Url::to(['/document/process-code-action/workflow-action-list', 'pcId' => $model->id]), [
                                        'title' => 'تخصیص فرآیند',
                                        'class' => 'btn btn-xs btn-link',
                                        'data-pjax' => '0',
                                        'style' => 'padding:1px 2px;color:#AE85BE;',
                                        'target' => '_blank',
                                        'data-toggle' => 'tooltip'
                            ]);
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
    'id' => 'process-modal',
    'header' => Yii::t('document', 'Create or Edit Process Code'),
    'size' => yii\bootstrap\Modal::SIZE_LARGE,
    'options' => [
        'tabindex' => '',
    ]
]);
?>
<?php Pjax::begin(['id' => 'process-form-pjax']); ?>

<?php Pjax::end(); ?>
<?php yii\bootstrap\Modal::end(); ?>

<script>

    function showProcessModal(obj) {
        ajaxProgressLoader('show', '.modal-content');
        var url = $(obj).attr('data-url');
        $.ajax({
            url: url,
            dataType: 'html'
        }).done(function (data) {
            $('#process-form-pjax').html(data);
            $('#process-modal').modal('show');
            ajaxProgressLoader('hide', '.modal-content');
        }).error(function () {
            showMsgBoxInBottomRight('Process Code in not Unknown. you cannot Edit it', 'error');
            return false;
        });
    }

    function processCallbackPjax(data) {
        if (data['result']) {
            $('#process-modal').modal('hide');
            showMsgBoxInBottomRight(data['message'], 'success');

        } else {
            showMsgBoxInBottomRight(data['message'], 'error');
        }
        reloadForm();

    }

    function reloadForm() {
        $.pjax.reload({
            container: '#process-grid-pjax',
            timeout: false,
            push: false,
            replace: false,
        });
    }

</script>
