<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\assets\JdateTimeAsset;
use app\assets\DatepickerAsset;

JdateTimeAsset::register($this);
DatepickerAsset::register($this);

/* @var $stampModel app\modules\document\models\StampDocument */
?>

<style>
    .stamp-info-box {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 12px 15px;
        margin-bottom: 15px;
        direction: rtl;
    }
    .stamp-info-box .badge { margin-left: 6px; }
</style>

<!-- اطلاعات نشان (فقط نمایش) -->
<div class="stamp-info-box">
    <div class="row">
        <div class="col-md-4">
            <strong>شناسه:</strong>
            <i class="badge badge-brown"><?= Html::encode($stampModel->StampId) ?></i>
        </div>
        <div class="col-md-4">
            <strong>عنوان:</strong>
            <i class="badge badge-brown"><?= Html::encode($stampModel->StampTitle) ?></i>
        </div>
        <div class="col-md-4">
            <strong>موقعیت:</strong>
            <i class="badge badge-brown"><?= Html::encode($stampModel->obs->fullTitle ?? '---') ?></i>
        </div>
        <div class="col-md-4" style="margin-top:8px;">
            <strong>نوع مدرک:</strong>
            <i class="badge badge-brown"><?= Html::encode($stampModel->stampType->TypeName ?? '---') ?></i>
        </div>
        <div class="col-md-4" style="margin-top:8px;">
            <strong>گردش:</strong>
            <i class="badge badge-brown"><?= Html::encode($stampModel->getOrganizationalMakerListLabel()) ?></i>
        </div>
    </div>
</div>

<?php
$form = ActiveForm::begin([
    'id' => 'refund-form',
    'action' => Url::to(['/document/stamp-document/create-refund', 'sid' => $stampModel->StampId]),
    'enableClientValidation' => true,
    'options' => [
        'enctype' => 'multipart/form-data',
        'data-pjax' => 0,
    ],
]);
?>

<div class="row" style="direction: rtl">
    <div class="col-md-8">
        <?= $form->field($stampModel, 'DescriptionDestroy')->textInput([
            'placeholder' => 'شامل: تاریخ و شناسه و گیرنده نامه امحاء یا تاریخ صورت جلسه و حاضرین در جلسه امحاء',
        ])->label('توضیحات *') ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($stampModel, 'DestroyDate')->textInput([
            'placeholder' => 'برای مثال 1403/09/03',
            'class' => 'form-control jdate',
        ])->label(Yii::t('document', 'DestroyDate') . ' *') ?>
    </div>
    <div class="col-md-12">
        <?= $form->field($stampModel, 'DestroyAttachmentFile')->fileInput()->label('بارگذاری مستندات امحا') ?>
        <div style="font-size: 12px; color: #777; margin-top: -10px; margin-bottom: 10px;">
            حداکثر حجم مجاز: ۵۰۰ کیلوبایت - فرمت‌های مجاز: pdf, jpg, jpeg, png
        </div>
    </div>
</div>

<hr class="p0 mb10" style="border-color:#B5B5B5"/>

<div class="row">
    <div class="col-md-12 text-right">
        <?= Html::submitButton('<i class="fa-plus"></i> ثبت امحا', [
            'class' => 'btn btn-sm btn-success',
        ]) ?>
        <?= Html::button('انصراف', [
            'class' => 'btn btn-sm btn-default',
            'data-dismiss' => 'modal',
        ]) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = "
$('#refund-form').on('beforeSubmit', function (e) {
    e.preventDefault();
    var form = $(this);
    var formData = new FormData(this);

    ajaxProgressLoader('show', '.modal-content');

    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json'
    }).done(function (data) {
        ajaxProgressLoader('hide', '.modal-content');
        if (data.result) {
            $('#refund-create-modal').modal('hide');
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                showMsgBoxInBottomRight(data.message || 'ثبت شد', 'success');
                if (typeof reloadRefundForm === 'function') reloadRefundForm();
            }
        } else {
            showMsgBoxInBottomRight(data.message || 'خطا در ثبت', 'error');
        }
    }).fail(function (xhr) {
        // اگر redirect HTML برگشت (مثلاً view-refund)
        if (xhr.status === 302 || (xhr.responseText && xhr.responseText.indexOf('view-refund') > -1)) {
            window.location.href = xhr.getResponseHeader('X-Redirect') || window.location.href;
            return;
        }
        // در حالت redirect معمولی Yii
        if (xhr.responseURL && xhr.responseURL.indexOf('view-refund') > -1) {
            window.location.href = xhr.responseURL;
            return;
        }
        ajaxProgressLoader('hide', '.modal-content');
        // fallback: صفحه را رفرش کن یا پیام بده
        showMsgBoxInBottomRight('در حال انتقال...', 'info');
    });

    return false;
});
";
$this->registerJs($js);
?>