<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use omnilight\assets\SweetAlertAsset;
use app\assets\JdateTimeAsset;
use app\assets\DatepickerAsset;
use kartik\detail\DetailView;
use app\components\PageHeader;

JdateTimeAsset::register($this);
DatepickerAsset::register($this);
SweetAlertAsset::register($this);

echo PageHeader::widget([
    'title' => Yii::t("automation", 'View Stamp Document'),
    'icon'  => 'im-file',
]);

$canUpdateStamp = \app\access\AccessControl::can(
    'document/stamp-document/update-workflow',
    ["peid" => $processEntityModel->ProcessEntityID]
);

?>

<style>
    #editStamp {
        top: 8px;
        left: 30px;
        z-index: 1;
        position: absolute;
    }
    .next-action:hover { 
        opacity: 1; 
    }
    .next-action {
        position: fixed;
        z-index: 10000000;
        right: 10px;
        bottom: 10px;
        opacity: 0.7;
    }
</style>

<div class="row mb10">
    <div class="col-md-12">
        <div style="border:solid 1px #dddddd; border-radius: 5px;">
            <?php Pjax::begin(['id' => 'stamp-pjax']); ?>

<?php if ($canUpdateStamp): ?>
    <?= Html::a(
        '<i class="glyphicon glyphicon-pencil"></i> &nbsp;' . Yii::t("automation", 'Edit'),
        '#',  // ✅ خالی
        [
            'class' => 'btn btn-sm btn-success',
            'id' => 'editStamp',
            'data-pjax' => 0,
            'data-url' => Url::to(['/document/stamp-document/update-workflow', 'sid' => $stampModel->StampId]),
            'onclick' => 'showStampModal(this); return false;',  // ✅ AJAX
        ]
    ); ?>
<?php endif; ?>

            <?php
            // آماده کردن مقدار Scope ها به صورت رشته
            $internalScopeString = '';
            if (is_array($internalScope) && !empty($internalScope)) {
                $scopeTitles = array_column($internalScope, 'ScopeTitle');
                $internalScopeString = implode(', ', $scopeTitles);
            }
            
            $externalScopeString = '';
            if (is_array($externalScope) && !empty($externalScope)) {
                $scopeTitles = array_column($externalScope, 'ScopeTitle');
                $externalScopeString = implode(', ', $scopeTitles);
            }
            
            // DetailView تنظیمات
            $attributes = [
                [
                    'label' => Yii::t("automation", 'Stamp Information'),
                    'labelColOptions' => ['class' => 'info text-left', 'style' => 'color:#106ca1'],
                    'value' => '',
                    'displayOnly' => true
                ],
                [
                    'columns' => [
                        [
                            'attribute' => 'StampId',
                            'label' => Yii::t("document", 'Stamp ID'),
                            'displayOnly' => true,
                        ],
                        [
                            'attribute' => 'SerialNumber',
                            'label' => Yii::t("document", 'Serial Number'),
                            'displayOnly' => true,
                        ],
                    ]
                ],
                [
                    'columns' => [
                        [
                            'attribute' => 'StampTitle',
                            'label' => Yii::t("document", 'Stamp Title'),
                            'displayOnly' => true,
                        ],
                        [
                            'attribute' => 'StampCode',
                            'label' => Yii::t("document", 'Stamp Code'),
                            'displayOnly' => true,
                        ],
                    ]
                ],
                [
                    'columns' => [
                        [
                            'attribute' => 'StampPurpose',
                            'label' => Yii::t("document", 'Stamp Purpose'),
                            'format' => 'ntext',
                            'displayOnly' => true,
                        ],
                    ]
                ],
                [
                    'columns' => [
                        [
                            'attribute' => 'ProcessCodeIDRef',
                            'label' => Yii::t("document", 'Process Code'),
                            'value' => $stampModel->processCode ? $stampModel->processCode->code : '---',
                            'displayOnly' => true,
                        ],
                        [
                            'attribute' => 'StampTypeIDRef',
                            'label' => Yii::t("document", 'Stamp Type'),
                            'value' => $stampModel->stampType ? $stampModel->stampType->TypeName : '---',
                            'displayOnly' => true,
                        ],
                    ]
                ],
                [
                    'columns' => [
                        [
                            'attribute' => 'ObsIDRef',
                            'label' => Yii::t("document", 'Position'),
                            'value' => $stampModel->obs ? $stampModel->obs->obsTitle : '---',
                            'displayOnly' => true,
                        ],
                        [
                            'attribute' => 'RequestDate',
                            'label' => Yii::t("document", 'Request Date'),
                            'displayOnly' => true,
                        ],
                    ]
                ],
                [
                    'label' => Yii::t("automation", 'Scopes'),
                    'labelColOptions' => ['class' => 'info text-left', 'style' => 'color:#106ca1'],
                    'value' => '',
                    'displayOnly' => true
                ],
                [
                    'columns' => [
                        [
                            'attribute' => 'internalScope',
                            'label' => Yii::t("document", 'Internal Scope'),
                            'value' => $internalScopeString ?: '---',
                            'format' => 'raw',
                            'displayOnly' => true,
                        ],
                        [
                            'attribute' => 'externalScope',
                            'label' => Yii::t("document", 'External Scope'),
                            'value' => $externalScopeString ?: '---',
                            'format' => 'raw',
                            'displayOnly' => true,
                        ],
                    ]
                ],
                [
                    'label' => Yii::t("document", 'Organizational Maker'),
                    'attribute' => 'OrganizationalMaker',
                    'value' => $stampModel->getOrganizationalMakerListLabel(),
                    'displayOnly' => true,
                ],
                [
                    'label' => Yii::t("document", 'Stamp File'),
                    'value' => $stampModel->StampDocument
                        ? Html::a(
                            '<i class="glyphicon glyphicon-download"></i> ' . $stampModel->StampDocument,
                            ['/uploads/stamp/' . $stampModel->StampDocument],
                            ['target' => '_blank']
                        )
                        : Yii::t("automation", 'No file'),
                    'format' => 'raw',
                    'displayOnly' => true,
                ],
            ];

            echo DetailView::widget([
                'id' => 'stamp-detail',
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

<!-- ✅ دکمه شناور اقدام بعدی (Next Action Floating Button) -->
<?php if ($todoExists): ?>
    <?= Html::a(
        '<i class="fa-circle-arrow-right rotate-360" style="font-size:48px"></i>',
        Url::to(['/workflow/next-action/next-action', 'peid' => $processEntityModel->ProcessEntityID, 'type' => 'stamp-document']),
        [
            'class' => 'pull-right mb5 animated bounceInLeft next-action',
            'onclick' => 'showNextActionModal(this); return false;',
            'data-pjax' => 0,
            'style' => 'color:#24cc42;',
            'data-original-title' => 'برو به اقدام بعدی',
            'data-toggle' => 'tooltip'
        ]
    ); ?>
<?php endif; ?>

<!-- ✅ مودال اقدام بعدی (Next Action Modal) -->
<?php
Modal::begin([
    'id' => 'stamp-nextaction-modal',
    'size' => Modal::SIZE_LARGE,
    'header' => '<h4 class="modal-title"><i class="im-arrow-right2"></i> اقدام بعدی</h4>',
]);

Pjax::begin(['id' => 'pjax-modal']);

Pjax::end();
Modal::end();
?>
<script>
function showBeneficiaryModalFromAlert(url, stampId) {
    if(typeof ajaxProgressLoader !== 'undefined'){
        ajaxProgressLoader('show', '.modal-content');
    }
    $.ajax({
        url: url,
        type: 'GET',
        success: function(data){
            $('#pjax-beneficiary').html(data);
            $('#beneficiary-modal').modal('show');
            if(typeof ajaxProgressLoader !== 'undefined'){
                ajaxProgressLoader('hide', '.modal-content');
            }
        },
        error: function(){
            if(typeof ajaxProgressLoader !== 'undefined'){
                ajaxProgressLoader('hide', '.modal-content');
            }
            if(typeof showMsgBoxInBottomRight !== 'undefined'){
                showMsgBoxInBottomRight('خطا در بارگذاری فرم بهره‌بردار!', 'error');
            }
        }
    });
}

function showStampModal(obj) {
    if (typeof ajaxProgressLoader !== 'undefined') {
        ajaxProgressLoader('show', '.modal-content');
    }
    
    var url = $(obj).attr('data-url');
    
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'html',
        success: function(data) {
            // بررسی وجود مودال
            if ($('#stamp-modal').length === 0) {
                $('body').append(`
                    <div class="modal fade" id="stamp-modal" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">ویرایش سند مهر</h4>
                                </div>
                                <div class="modal-body" id="stamp-modal-body"></div>
                            </div>
                        </div>
                    </div>
                `);
            }
            
            $('#stamp-modal .modal-body').html(data);
            $('#stamp-modal').modal('show');
            
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


function beneficiaryCallbackPjax(data) {
    if (data.result) {
        $('#beneficiary-modal').modal('hide');
        showMsgBoxInBottomRight(data.message, 'success');
        // رفرش صفحه برای به‌روزرسانی وضعیت
        location.reload();
    } else {
        showMsgBoxInBottomRight(data.message, 'error');
    }
    if (typeof owghatData === 'undefined') {
    var owghatData = {};
}
}
</script>
<!-- ✅ جاوااسکریپت مربوط به Next Action -->
<?php
$js = "
    function showNextActionModal(obj){
        var url = $(obj).attr('href');
        if(typeof ajaxProgressLoader !== 'undefined'){
            ajaxProgressLoader('show', '.modal-content');
        }
        $.ajax({
            url: url,
            type: 'GET',
            success: function(data){
                $('#pjax-modal').html(data);
                $('#stamp-nextaction-modal').modal('show');
                if(typeof ajaxProgressLoader !== 'undefined'){
                    ajaxProgressLoader('hide', '.modal-content');
                }
            },
            error: function(){
                if(typeof ajaxProgressLoader !== 'undefined'){
                    ajaxProgressLoader('hide', '.modal-content');
                }
                if(typeof showMsgBoxInBottomRight !== 'undefined'){
                    showMsgBoxInBottomRight('خطا در بارگذاری داده‌ها!', 'error');
                }
            }
        });
        return false;
    }

    $('#pjax-modal').on('pjax:send', function(){
        if(typeof ajaxProgressLoader !== 'undefined'){
            ajaxProgressLoader('show', '.modal-content');
        }
    });
    
    $('#pjax-modal').on('pjax:complete', function(){
        if(typeof ajaxProgressLoader !== 'undefined'){
            ajaxProgressLoader('hide', '.modal-content');
        }
    });
";

$this->registerJs($js, yii\web\View::POS_END);
?>