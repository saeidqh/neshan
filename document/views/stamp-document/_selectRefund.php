<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\components\Utility;
use app\modules\document\models\StampRefundsSearch;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel app\modules\document\models\StampRefundsSearch */
?>
<div class="row">
    <div class="col-md-12">
        <?= GridView::widget([
            'id' => 'select-refund-grid',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'pjax' => true,
            'pjaxSettings' => [
                'options' => [
                    'id' => 'select-refund-grid-pjax',
                    'timeout' => 10000,
                    'enablePushState' => false,
                    'enableReplaceState' => false,
                ],
            ],
            'layout' => "{items}\n{pager}",
            'filterRowOptions' => ['class' => 'kv-filter-row'],
            'columns' => [
                [
                    'class' => 'yii\grid\RadioButtonColumn',
                    'name' => 'selectedStampId',
                    'header' => 'انتخاب',
                    'radioOptions' => function ($model) {
                        return ['value' => $model->StampId, 'class' => 'stamp-radio'];
                    },
                ],
                [
                    'attribute' => 'StampId',
                    'label' => Yii::t('document', 'ID'),
                    'width' => '70px',
                    'filterInputOptions' => ['class' => 'form-control input-sm', 'placeholder' => 'جستجوی ID'],
                ],
                [
                    'attribute' => 'StampTitle',
                    'label' => Yii::t('document', 'Title'),
                    'width' => '180px',
                    'filterInputOptions' => ['class' => 'form-control input-sm', 'placeholder' => 'جستجوی عنوان'],
                ],
                [
                    'attribute' => 'parentObsTitle',
                    'label' => Yii::t('automation', 'Parent Obs'),
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Utility::generateTextShorterSpan(
                            Html::encode($model->assistanceTitle ?? ''),
                            30,
                            30
                        );
                    },
                    'width' => '160px',
                    'filter' => false,
                ],
                [
                    'attribute' => 'obsFullTitle',
                    'label' => Yii::t('document', 'Position'),
                    'value' => function ($model) {
                        return $model->obs ? $model->obs->fullTitle : '---';
                    },
                    'width' => '170px',
                    'filterInputOptions' => ['class' => 'form-control input-sm', 'placeholder' => 'جستجوی جایگاه'],
                ],
                [
                    'attribute' => 'OrganizationalMaker',
                    'label' => Yii::t('document', 'گردش'),
                    'value' => function ($model) {
                        return $model->getOrganizationalMakerListLabel();
                    },
                    'width' => '110px',
                    'filter' => StampRefundsSearch::getOrganizationalMakerList(),
                    'filterType' => GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'options' => ['placeholder' => 'انتخاب گردش'],
                        'pluginOptions' => ['allowClear' => true, 'width' => '110px'],
                    ],
                ],
                [
                    'attribute' => 'StampTypeIDRef',
                    'label' => Yii::t('document', 'نوع مدرک'),
                    'value' => function ($model) {
                        return $model->stampType ? $model->stampType->TypeName : '---';
                    },
                    'width' => '130px',
                    'filter' => StampRefundsSearch::getStampTypeList(),
                    'filterType' => GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'options' => ['placeholder' => 'انتخاب نوع'],
                        'pluginOptions' => ['allowClear' => true, 'width' => '130px'],
                    ],
                ],
            ],
        ]); ?>
    </div>
</div>
<div class="row" style="margin-top:15px;">
    <div class="col-md-12 text-left">
        <?= Html::button('<i class="fa fa-check"></i> انتخاب', [
            'class' => 'btn btn-sm btn-success',
            'id' => 'btn-select-stamp',
            'onclick' => 'selectStampForRefund()',
        ]) ?>
        <?= Html::button('انصراف', [
            'class' => 'btn btn-sm btn-default',
            'data-dismiss' => 'modal',
        ]) ?>
    </div>
</div>
<script>
function selectStampForRefund() {
    var sid = $('input[name="selectedStampId"]:checked').val();
    if (!sid) {
        if (typeof showMsgBoxInBottomRight !== 'undefined') {
            showMsgBoxInBottomRight('لطفاً یک نشان انتخاب کنید', 'warning');
        } else {
            alert('لطفاً یک نشان انتخاب کنید');
        }
        return false;
    }
    ajaxProgressLoader('show', '.modal-content');
    $.ajax({
        url: '<?= Url::to(['/document/stamp-document/create-refund']) ?>',
        type: 'GET',
        data: { sid: sid },
        success: function (data) {
            $('#refund-create-pjax').html(data);
            ajaxProgressLoader('hide', '.modal-content');
        },
        error: function () {
            ajaxProgressLoader('hide', '.modal-content');
            if (typeof showMsgBoxInBottomRight !== 'undefined') {
                showMsgBoxInBottomRight('خطا در بارگذاری فرم امحا', 'error');
            } else {
                alert('خطا در بارگذاری فرم امحا');
            }
        }
    });
    return false;
}
</script>