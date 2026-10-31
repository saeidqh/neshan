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
    </div>
</div>

<?php
$form = ActiveForm::begin([
    'id' => 'refund-update-form',
    'action' => Url::to(['/document/stamp-document/update-refund', 'sid' => $stampModel->StampId]),
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
        <?php if ($stampModel->DestroyAttachment): ?>
            <div style="font-size:12px; color:#337ab7;">
                فایل فعلی: <?= Html::encode($stampModel->DestroyAttachment) ?>
            </div>
        <?php endif; ?>
        <div style="font-size: 12px; color: #777; margin-top: -10px; margin-bottom: 10px;">
            حداکثر حجم مجاز: ۵۰۰ کیلوبایت - فرمت‌های مجاز: pdf, jpg, jpeg, png
        </div>
    </div>
</div>

<hr class="p0 mb10" style="border-color:#B5B5B5"/>

<div class="row">
    <div class="col-md-12 text-right">
        <?= Html::submitButton('<i class="fa-save"></i> به‌روزرسانی', [
            'class' => 'btn btn-sm btn-primary',
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
// تابع پردازش پاسخ از سرور
function refundUpdateCallback(data) {
    if (data.result) {
        // بستن مودال
        $('#refund-update-modal').modal('hide');
        // ریدایرکت با تاخیر 300ms
        if (data.redirect) {
            setTimeout(function() {
                window.location.href = data.redirect;
            }, 300);
        } else {
            if (typeof showMsgBoxInBottomRight !== 'undefined') {
                showMsgBoxInBottomRight(data.message || 'به‌روزرسانی شد', 'success');
            }
            if (typeof reloadRefundView === 'function') {
                reloadRefundView();
            }
        }
    } else {
        if (typeof showMsgBoxInBottomRight !== 'undefined') {
            showMsgBoxInBottomRight(data.message || 'خطا در به‌روزرسانی', 'error');
        } else {
            alert(data.message || 'خطا در به‌روزرسانی');
        }
    }
}

// ارسال فرم با Ajax
$('#refund-update-form').on('beforeSubmit', function (e) {
    e.preventDefault();
    var form = $(this);
    var formData = new FormData(this);

    if (typeof ajaxProgressLoader !== 'undefined') {
        ajaxProgressLoader('show', '.modal-content');
    }

    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'html'  // دریافت پاسخ به صورت HTML
    }).done(function (response) {
        if (typeof ajaxProgressLoader !== 'undefined') {
            ajaxProgressLoader('hide', '.modal-content');
        }

        // استخراج محتوای اسکریپت از پاسخ (حذف تگ‌های <script>)
        var scriptMatch = response.match(/<script[^>]*>([\\s\\S]*?)<\\/script>/i);
        if (scriptMatch && scriptMatch[1]) {
            // اجرای محتوای اسکریپت (که شامل فراخوانی refundUpdateCallback است)
            try {
                eval(scriptMatch[1]);
            } catch (e) {
                console.error('خطا در اجرای اسکریپت:', e);
                if (typeof showMsgBoxInBottomRight !== 'undefined') {
                    showMsgBoxInBottomRight('خطا در پردازش پاسخ سرور', 'error');
                }
            }
        } else {
            // اگر پاسخ HTML نبود، شاید JSON باشد
            try {
                var data = JSON.parse(response);
                if (data.result) {
                    $('#refund-update-modal').modal('hide');
                    if (data.redirect) {
                        setTimeout(function(){ window.location.href = data.redirect; }, 300);
                    }
                } else {
                    showMsgBoxInBottomRight(data.message || 'خطا', 'error');
                }
            } catch(e) {
                console.error('پاسخ نامعتبر:', response);
                if (typeof showMsgBoxInBottomRight !== 'undefined') {
                    showMsgBoxInBottomRight('پاسخ نامعتبر از سرور', 'error');
                }
            }
        }
    }).fail(function () {
        if (typeof ajaxProgressLoader !== 'undefined') {
            ajaxProgressLoader('hide', '.modal-content');
        }
        if (typeof showMsgBoxInBottomRight !== 'undefined') {
            showMsgBoxInBottomRight('خطا در ارتباط با سرور', 'error');
        } else {
            alert('خطا در ارتباط با سرور');
        }
    });

    return false;
});
";
$this->registerJs($js);
?>