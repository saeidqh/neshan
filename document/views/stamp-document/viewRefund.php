<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use kartik\detail\DetailView;
use app\components\PageHeader;
use omnilight\assets\SweetAlertAsset;
use app\assets\JdateTimeAsset;
use app\assets\DatepickerAsset;

JdateTimeAsset::register($this);
DatepickerAsset::register($this);
SweetAlertAsset::register($this);

$this->title = 'جزئیات امحا نشان سازمانی';
$this->params['breadcrumbs'][] = ['label' => 'گردش کار', 'url' => ['/workflow/workflow/refund-stamp']];
$this->params['breadcrumbs'][] = $this->title;

echo PageHeader::widget([
    'title' => $this->title,
    'icon'  => 'im-file',
]);

// دسترسی ویرایش – در صورت نیاز می‌توان شرط دقیق‌تری گذاشت
$canUpdate = \app\access\AccessControl::can(
    'document/stamp-document/update-refund',
    ['sid' => $stampModel->StampId]
);
?>

<style>
    #editRefundBtn {
        top: 8px;
        left: 30px;
        z-index: 1;
        position: absolute;
    }
    .next-action {
        position: fixed;
        z-index: 10000000;
        right: 10px;
        bottom: 10px;
        opacity: 0.8;
    }
    .next-action:hover {
        opacity: 1;
    }
</style>

<div class="row mb10">
    <div class="col-md-12">
        <div style="border:solid 1px #ddd; border-radius:5px; position:relative;">

            <?php Pjax::begin(['id' => 'refund-view-pjax']); ?>

            <?php if ($canUpdate): ?>
                <?= Html::a(
                    '<i class="glyphicon glyphicon-pencil"></i> ویرایش امحا',
                    '#',
                    [
                        'class' => 'btn btn-sm btn-success',
                        'id'    => 'editRefundBtn',
                        'data-pjax' => 0,
                        'data-url' => Url::to(['/document/stamp-document/update-refund', 'sid' => $stampModel->StampId]),
                        'onclick' => 'showUpdateRefundModal(this); return false;',
                    ]
                ); ?>
            <?php endif; ?>

            <?php
            // آماده‌سازی مقادیر برای DetailView
            $destroyDate = $stampModel->DestroyDate ?: '---';
            $destroyDesc = $stampModel->DescriptionDestroy ?: '---';
            $destroyAttachment = $stampModel->DestroyAttachment
                ? Html::a(
                    '<i class="glyphicon glyphicon-download"></i> ' . $stampModel->DestroyAttachment,
                    ['/uploads/stamp/destroy/' . $stampModel->DestroyAttachment],
                    ['target' => '_blank']
                )
                : '---';

            $attributes = [
                [
                    'label' => 'اطلاعات نشان',
                    'value' => '',
                    'displayOnly' => true,
                    'labelColOptions' => ['class' => 'info text-left', 'style' => 'color:#106ca1'],
                ],
                [
                    'columns' => [
                        ['attribute' => 'StampId', 'label' => 'شناسه', 'displayOnly' => true],
                        ['attribute' => 'StampTitle', 'label' => 'عنوان', 'displayOnly' => true],
                    ],
                ],
                [
                    'columns' => [
                        ['attribute' => 'StampPurpose', 'label' => 'هدف', 'displayOnly' => true, 'format' => 'ntext'],
                        ['attribute' => 'StampCode', 'label' => 'کد نشان', 'displayOnly' => true],
                    ],
                ],
                [
                    'columns' => [
                        [
                            'label' => 'موقعیت سازمانی',
                            'value' => $stampModel->obs ? $stampModel->obs->fullTitle : '---',
                            'displayOnly' => true,
                        ],
                        [
                            'label' => 'نوع مدرک',
                            'value' => $stampModel->stampType ? $stampModel->stampType->TypeName : '---',
                            'displayOnly' => true,
                        ],
                    ],
                ],
                [
                    'columns' => [
                        [
                            'label' => 'گردش (OrganizationalMaker)',
                            'value' => $stampModel->getOrganizationalMakerListLabel(),
                            'displayOnly' => true,
                        ],
                        [
                            'label' => 'وضعیت',
                            'value' => $stampModel->getStatusLabal(),
                            'displayOnly' => true,
                        ],
                    ],
                ],
                [
                    'label' => 'اطلاعات امحا',
                    'value' => '',
                    'displayOnly' => true,
                    'labelColOptions' => ['class' => 'info text-left', 'style' => 'color:#106ca1'],
                ],
                [
                    'columns' => [
                        [
                            'label' => 'تاریخ امحا',
                            'value' => $destroyDate,
                            'displayOnly' => true,
                        ],
                        [
                            'label' => 'توضیحات امحا',
                            'value' => $destroyDesc,
                            'displayOnly' => true,
                            'format' => 'ntext',
                        ],
                    ],
                ],
                [
                    'columns' => [
                        [
                            'label' => 'مستندات امحا',
                            'value' => $destroyAttachment,
                            'format' => 'raw',
                            'displayOnly' => true,
                        ],
                    ],
                ],
            ];

            echo DetailView::widget([
                'id' => 'refund-detail',
                'model' => $stampModel,
                'attributes' => $attributes,
                'buttons1' => '',
                'mode' => DetailView::MODE_VIEW,
                'enableEditMode' => false,
                'bordered' => true,
                'striped' => true,
                'condensed' => true,
                'responsive' => true,
                'hover' => true,
                'panel' => [
                    'type' => DetailView::TYPE_PRIMARY,
                ],
            ]);
            ?>

            <?php Pjax::end(); ?>
        </div>
    </div>
</div>

<?php if ($todoExists): ?>
    <?= Html::a(
        '<i class="fa-circle-arrow-right rotate-360" style="font-size:48px;"></i>',
        Url::to(['/workflow/next-action/next-action', 'peid' => $processEntityModel->ProcessEntityID, 'type' => 'refund-stamp']),
        [
            'class' => 'pull-right mb5 animated bounceInLeft next-action',
            'onclick' => 'showNextActionModal(this); return false;',
            'data-pjax' => 0,
            'style' => 'color:#24cc42;',
            'data-original-title' => 'اقدام بعدی',
            'data-toggle' => 'tooltip',
        ]
    ); ?>
<?php endif; ?>

<!-- مودال ویرایش امحا -->
<?php
Modal::begin([
    'id' => 'refund-update-modal',
    'size' => Modal::SIZE_LARGE,
    'header' => '<h4 class="modal-title"><i class="glyphicon glyphicon-pencil"></i> ویرایش اطلاعات امحا</h4>',
]);
Pjax::begin(['id' => 'refund-update-pjax']);
Pjax::end();
Modal::end();
?>

<!-- مودال اقدام بعدی -->
<?php
Modal::begin([
    'id' => 'refund-nextaction-modal',
    'size' => Modal::SIZE_LARGE,
    'header' => '<h4 class="modal-title"><i class="im-arrow-right2"></i> اقدام بعدی</h4>',
]);
Pjax::begin(['id' => 'refund-nextaction-pjax']);
Pjax::end();
Modal::end();
?>

<script>
function showUpdateRefundModal(obj) {
    var url = $(obj).data('url');
    if (typeof ajaxProgressLoader !== 'undefined') {
        ajaxProgressLoader('show', '.modal-content');
    }
    $.ajax({
        url: url,
        type: 'GET',
        success: function(data) {
            $('#refund-update-pjax').html(data);
            $('#refund-update-modal').modal('show');
            if (typeof ajaxProgressLoader !== 'undefined') {
                ajaxProgressLoader('hide', '.modal-content');
            }
        },
        error: function() {
            if (typeof ajaxProgressLoader !== 'undefined') {
                ajaxProgressLoader('hide', '.modal-content');
            }
            if (typeof showMsgBoxInBottomRight !== 'undefined') {
                showMsgBoxInBottomRight('خطا در بارگذاری فرم ویرایش', 'error');
            } else {
                alert('خطا در بارگذاری فرم ویرایش');
            }
        }
    });
    return false;
}

function showNextActionModal(obj) {
    var url = $(obj).attr('href');
    if (typeof ajaxProgressLoader !== 'undefined') {
        ajaxProgressLoader('show', '.modal-content');
    }
    $.ajax({
        url: url,
        type: 'GET',
        success: function(data) {
            $('#refund-nextaction-pjax').html(data);
            $('#refund-nextaction-modal').modal('show');
            if (typeof ajaxProgressLoader !== 'undefined') {
                ajaxProgressLoader('hide', '.modal-content');
            }
        },
        error: function() {
            if (typeof ajaxProgressLoader !== 'undefined') {
                ajaxProgressLoader('hide', '.modal-content');
            }
            if (typeof showMsgBoxInBottomRight !== 'undefined') {
                showMsgBoxInBottomRight('خطا در بارگذاری', 'error');
            }
        }
    });
    return false;
}

// رفرش Pjax بعد از ذخیره ویرایش یا اقدام بعدی
function reloadRefundView() {
    $.pjax.reload({
        container: '#refund-view-pjax',
        timeout: false,
        push: false,
        replace: false,
    });
}
</script>