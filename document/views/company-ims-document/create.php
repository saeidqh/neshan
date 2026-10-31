<?php

use yii\helpers\Html;
?>
<style>
    .fix-grid-header .kv-thead-float{
        top:70px !important;
        left:5px !important;
    }
</style>
<div class="row grid-font" style="height:400px; overflow-y:scroll; " >

    <div class="fix-grid-header" >
        <?php
        echo \kartik\grid\GridView::widget([
            'id' => 'company-ims-document-grid',
            'dataProvider' => $imsDocumentDataProvider,
            'filterModel' => $imsDocumentSearchModel,
            'pjax' => false,
            'floatHeader' => true,
            'layout' => "{items}\n{pager}\n{summary}",
            'pjaxSettings' => [
                'options' => [
                    'id' => 'company-ims-document-grid-pjax',
                ],
            ],
            'columns' => [
                [
                    'class' => '\kartik\grid\CheckboxColumn',
                    'options' => [
                        'width' => '10px'
                    ],
                ],
                [
                    'attribute' => 'id',
                    'value' => 'id',
                    'options' => [
                        'width' => '10px'
                    ],
                ],
                [
                    'attribute' => 'companyTitle',
                    'value' => 'companyTitle',
                ],
                [
                    'attribute' => 'imstitle',
                    'value' => 'imstitle',
                ],
                [
                    'attribute' => 'imsType',
                    'filter' => Html::activeDropDownList($imsDocumentSearchModel, 'imsType', $typeArray, ['class' => 'form-control', 'prompt' => 'Select Type']),
                ],
                [
                    'attribute' => 'imscode',
                    'value' => 'imscode',
                ],
                [
                    'attribute' => 'preparationDate',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return (isset($model->preparationDate)) ? \app\components\Utility::convertDate($model->preparationDate, true) : '';
                    },
                    'options' => [
                        'width' => '15px'
                    ],
                ],
                [
                    'attribute' => 'revisionDate',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return (isset($model->revisionDate)) ? \app\components\Utility::convertDate($model->revisionDate, true) : '';
                    },
                    'options' => [
                        'width' => '15px'
                    ],
                ],
                [
                    'attribute' => 'effectiveDate',
                    'value' => function ($model) {
                        return (isset($model->effectiveDate)) ? \app\components\Utility::convertDate($model->effectiveDate, true) : '';
                    },
                ],
                [
                    'attribute' => 'revisionNumber',
                    'value' => 'revisionNumber',
                ],
                [
                    'attribute' => 'processCode',
                    'value' => 'imsprocessCode',
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'filter' => Html::activeDropDownList($imsDocumentSearchModel, 'status', $statusArray, ['class' => 'form-control', 'prompt' => 'Select Type']), /* 'multiple'=>'true' */
                    'value' => function ($model)use ($statusArray) {
                        return $model->status ? '<span class="badge badge-success">' . $statusArray[$model->status] . '</span>' : '<span class="badge badge-danger">' . $statusArray[$model->status] . '</span>';
                    },
                ],
            ]
        ]);
        ?>
    </div>
</div>
<div class='row'>
    <div class='col-md-12' >
        <hr class='p0 m0 mb10' />
        <?=
        Html::button('Add Action(s)', [
            'class' => 'btn btn-success btn-sm pull-right',
            'onclick' => 'return addSelectedDocuments()',
        ])
        ?>
        <?=
        Html::button('Cancel', [
            'class' => 'btn btn-danger btn-sm pull-right',
            'style' => 'margin-right:5px;',
            'data-dismiss' => 'modal',
        ])
        ?>
    </div>

</div>
<script>
    function addSelectedDocuments() {
        var items = $('#company-ims-document-grid').yiiGridView('getSelectedRows');
        if (!(items.length > 0)) {
            showMsgBoxInBottomRight('Please select the doc(s).', 'warning');
            return;
        }
        ajaxProgressLoader('show', '#company-ims-document-form-modal .modal-body');
        var url = $('#btn-add-doc').attr('data-url');
        $.pjax.reload({
            container: "#company-ims-document-form-pjax",
            url: url,
            timeout: false,
            push: false,
            replace: false,
            data: {
                docs: items
            }
        });
    }
</script>