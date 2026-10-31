<?php

use app\components\PageHeader;
use app\components\GridViewBox;
use yii\widgets\Pjax;
use app\modules\document\models\ImsDocumentProcessCode;
use omnilight\assets\SweetAlertAsset;
use app\modules\account\assets\UserAsset;

SweetAlertAsset::register($this);
UserAsset::register($this);

$this->title = Yii::t('document', 'Document Type List');

$canCreateAndUpdateDocumentType = app\access\AccessControl::can('document/ims-document-type/create');
?>

<?=
PageHeader::widget([
    'title' => $this->title,
//    'icon' => 'en-flow-cascade'
]);
?>
<style>
    #ims-document-type-grid-pjax thead,
    #ims-document-type-grid-pjax .summary {
        background-color: #1dd6cd;
    }

    #ims-document-type-grid-pjax * {
        font-size: 12px;
    }

    .grid-box table th,
    .grid-box table td {
        font-size: 12px !important;
    }

    #ims-document-type-grid-pjax table tbody td:nth-child(3) {
        max-width: 400px !important;
        overflow-x: auto;
        /*display: block;*/
    }
</style>
<?php GridViewBox::begin([]); ?>
<div class="row">
    <div class="col-md-4 pull-right text-right grid-box-buttons">
        <div class="btn-group">
            <?php if ($canCreateAndUpdateDocumentType) { ?>
                <button class="btn btn-sm btn-primary pull-right ml5"
                        data-url="<?= Yii::$app->urlManager->createAbsoluteUrl(["document/ims-document-type/create"]) ?>"
                        onclick="showImsDocumentTypeModal(this)">
                    <i class=""></i> <?= Yii::t('document', 'New Document Type') ?> 
                </button>
            <?php } ?>
        </div>
    </div>
</div>
<div class='row'>

    <?php Pjax::begin(); ?>
    <?php
    echo \kartik\grid\GridView::widget([
        'filterModel' => $imsDocumentTypeModel,
        'dataProvider' => $imsDocumentTypeDataProvider,
        'hover' => true,
        'pjax' => true,
        'pjaxSettings' => [
            'options' => [
                'id' => 'ims-document-type-grid-pjax',
            ],
        ],
        'columns' => [
            [
                'attribute' => 'ID',
                'options' => [
                    'width' => '70px'
                ],
            ],
            [
                'attribute' => 'Code',
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
                'filter' => ImsDocumentProcessCode::getStatus(),
                'filterOptions' => ['options' => ['prompt' => Yii::t('document', 'Active')]],
                'format' => 'raw',
                'value' => function ($data) {
                    return "<span class='" . (($data['isActive'] == 1) ? 'text-success' : 'text-danger') . "'><strong>" . (($data['isActive'] == 1) ? Yii::t('document', 'Active') : Yii::t('document', 'In Active')) . "</strong></span>";
                }
            ],
            [
                'class' => '\kartik\grid\ActionColumn',
                'template' => '{update}',
                'buttons' => [
                    'update' => function ($key, $model, $index) use ($canCreateAndUpdateDocumentType) {
                        if ($canCreateAndUpdateDocumentType) {

                            return \yii\helpers\Html::a('<i class="fa-pencil s16"></i>', '#', [
                                        'data-toggle' => 'tooltip',
                                        'data-placement' => 'top',
                                        'data-original-title' => 'Edit Document Type Code',
                                        'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document-type/update', 'id' => $model->ID]),
                                        'onclick' => 'showImsDocumentTypeModal(this)'
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
    'id' => 'ims-document-type-modal',
    'header' => Yii::t('document', 'Create or Edit Document Type Code'),
    'size' => yii\bootstrap\Modal::SIZE_LARGE,
    'options' => [
        'tabindex' => '',
    ]
]);
?>
<?php Pjax::begin(['id' => 'ims-document-type-form-pjax']); ?>

<?php Pjax::end(); ?>
<?php yii\bootstrap\Modal::end(); ?>

<script>

    function showImsDocumentTypeModal(obj) {
        ajaxProgressLoader('show', '.modal-content');
        var url = $(obj).attr('data-url');
        $.ajax({
            url: url,
            dataType: 'html'
        }).done(function (data) {
            $('#ims-document-type-form-pjax').html(data);
            $('#ims-document-type-modal').modal('show');
            ajaxProgressLoader('hide', '.modal-content');
        }).error(function () {
            showMsgBoxInBottomRight('Document Type in not Unknown. you cannot Edit it', 'error');
            return false;
        });
    }

    function documentTypeCallbackPjax(data) {
        if (data['result']) {
            $('#ims-document-type-modal').modal('hide');
            showMsgBoxInBottomRight(data['message'], 'success');

        } else {
            showMsgBoxInBottomRight(data['message'], 'error');
        }
        reloadForm();

    }

    function reloadForm() {
        $.pjax.reload({
            container: '#ims-document-type-grid-pjax',
            timeout: false,
            push: false,
            replace: false,
        });
    }

</script>
