<?php

use app\components\PageHeader;
use app\components\GridViewBox;
use yii\widgets\Pjax;
use app\modules\document\models\Clause;
use omnilight\assets\SweetAlertAsset;
use app\modules\account\assets\UserAsset;
use yii\helpers\Html;

SweetAlertAsset::register($this);
UserAsset::register($this);

$this->title = Yii::t('document', 'Standard/Model');

$canCreateAndUpdateStandard = app\access\AccessControl::can('document/standard/create');
?>

<?=
PageHeader::widget([
    'title' => $this->title,
    'icon' => 'en-flow-cascade'
]);
?>
<style>
    #standard-grid-pjax thead,
    #standard-grid-pjax .summary {
        background-color: #1dd6cd;
    }

    #standard-grid-pjax * {
        font-size: 12px;
    }

    .grid-box table th,
    .grid-box table td {
        font-size: 12px !important;
    }

    #standard-grid-pjax table tbody td:nth-child(3) {
        max-width: 400px !important;
        overflow-x: auto;
    }
</style>
<?php GridViewBox::begin([]); ?>
<div class="row">
    <div class="col-md-4 pull-right text-right grid-box-buttons">
        <div class="btn-group">
            <?php 
                echo Html::a(\Yii::t("document", "Clause"), Yii::$app->urlManager->createAbsoluteUrl(['document/clause/list']), [
                    'class' => 'btn btn-sm btn-mini btn-primary',
                    'data-pjax' => '0',
                        ]
                );
                if ($canCreateAndUpdateStandard) { ?>
                <button class="btn btn-sm btn-primary pull-right ml5"
                        data-url="<?= Yii::$app->urlManager->createAbsoluteUrl(["document/standard/create"]) ?>"
                        onclick="showStandardModal(this)"><i
                        class=""></i> <?= Yii::t('document', 'New Standard/Model') ?> </button>
            <?php } ?>
        </div>
    </div>
</div>
<div class='row'>

    <?php Pjax::begin(); ?>
    <?php
    echo \kartik\grid\GridView::widget([
        'dataProvider' => $standardDataProvider,
        'filterModel' => $standardSearchModel,
        'hover' => true,
        'pjax' => true,
        'pjaxSettings' => [
            'options' => [
                'id' => 'standard-grid-pjax',
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
                'attribute' => 'title',
            ],
            [
                'attribute' => 'area',
            ],
            [
                'attribute' => 'description',
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
                    'update' => function ($key, $model, $index) use ($canCreateAndUpdateStandard) {
                        if ($canCreateAndUpdateStandard) {
                            return \yii\helpers\Html::a('<i class="fa-pencil s16"></i>', '#', [
                                        'data-toggle' => 'tooltip',
                                        'data-placement' => 'top',
                                        'data-original-title' => 'Edit Certificate',
                                        'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/standard/update', 'id' => $model->id]),
                                        'onclick' => 'showStandardModal(this)'
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
    'id' => 'standard-modal',
    'header' => Yii::t('document', 'Standard'),
    'size' => yii\bootstrap\Modal::SIZE_LARGE,
    'options' => [
        'tabindex' => '',
    ]
]);
?>
<?php Pjax::begin(['id' => 'standard-form-pjax']); ?>

<?php Pjax::end(); ?>
<?php yii\bootstrap\Modal::end(); ?>

<script>

    function showStandardModal(obj) {
        ajaxProgressLoader('show', '.modal-content');
        var url = $(obj).attr('data-url');
        $.ajax({
            url: url,
            dataType: 'html'
        }).done(function (data) {
            $('#standard-form-pjax').html(data);
            $('#standard-modal').modal('show');
            ajaxProgressLoader('hide', '.modal-content');
        }).error(function () {
            showMsgBoxInBottomRight('Server Error', 'error');
            return false;
        });
    }

    function standardCallbackPjax(data) {
        if (data['result']) {
            $('#standard-modal').modal('hide');
            showMsgBoxInBottomRight(data['message'], 'success');

        } else {
            showMsgBoxInBottomRight(data['message'], 'error');
        }
        reloadForm();

    }

    function reloadForm() {
        $.pjax.reload({
            container: '#standard-grid-pjax',
            timeout: false,
            push: false,
            replace: false,
        });
    }




</script>
