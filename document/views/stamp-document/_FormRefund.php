<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\assets\JdateTimeAsset;
use app\assets\DatepickerAsset;
use yii\helpers\Url;

JdateTimeAsset::register($this);
DatepickerAsset::register($this);
$jDate = new \app\components\jDateTime(true, true, 'Asia/Tehran');
?>


<style>
    .display-none {
        display: none;
    }


</style>

<?php
$form = ActiveForm::begin([
    'id' => 'refund-form',
    'action' => Url::to(['/document/stamp-document/refund',
        'id' => $stampModel->StampId
    ]),
    'enableClientValidation' => true,
    'options' => [
        'enctype' => 'multipart/form-data',
        'data-pjax' => 0,
    ],
]);
?>


<div class="row" style="direction: rtl">

    <div class=" col-md-8">
        <?=
        $form->field($stampModel, 'DescriptionDestroy')->textInput([

            "placeholder" => "شامل: تاریخ و شناسه و گیرنده نامه امحاء یا تاریخ صورت جلسه و حاضرین در جلسه امحاء",

        ])->label('توضیحات'. ' *');
        ?>
    </div>

    <div class="col-md-4">
        <?=
        $form->field($stampModel, 'DestroyDate')->textInput([

            "placeholder" => "برای مثال 1403/09/03 ",

        ])->label(Yii::t('document', 'DestroyDate') . ' *');
        ?>
    </div>
    <div class="col-md-12">
    <?= $form->field($stampModel, 'DestroyAttachmentFile')->fileInput()->label('بارگذاری مستندات امحا') ?>
</div>
          <div style="font-size: 12px; color: #777; margin-top: -10px; margin-bottom: 10px;">
            حداکثر حجم مجاز: ۵۰۰ کیلوبایت - فرمت‌های مجاز:
            pdf,  jpg, jpeg, png
        </div>

</div>


<hr class="p0 mb10" style='border-color:#B5B5B5'/>
<div class="row">
    <div class="col-md-12 text-right">
        <div class="form-group">
            <?= Html::submitButton($stampModel->isNewRecord ? '<i class="fa-plus"></i> ' . Yii::t('contract', 'Create') : '<i class="fa-edit"></i> Update', ['class' => $stampModel->isNewRecord ? 'btn btn-sm btn-success' : 'btn btn-sm btn-primary']) ?>
        </div>
    </div>
</div>


<?php ActiveForm::end(); ?>

