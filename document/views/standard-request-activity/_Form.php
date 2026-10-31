<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\assets\JdateTimeAsset;
use app\assets\DatepickerAsset;
use kartik\widgets\SwitchInput;

JdateTimeAsset::register($this);
DatepickerAsset::register($this);
$companyId = Yii::$app->session->get('CompanyID');
$isCandoAdmin = \app\components\Security::isCandoAdmin();
?>

<?php
$form = ActiveForm::begin(['id' => 'standard-request-form',
            'enableClientValidation' => true,
            'options' => [
                'id' => 'standard-request-form-pjax',
                'data-pjax' => true,
                'enctype' => 'multipart/form-data'
        ]]);
?>
<div class="row" style="direction: rtl; text-align: right;">
    <div class="col-md-12">
        <?= $form->field($clauseActivityModel, 'title')->textInput(['disabled' => 'disabled', 'readonly' => true])->label('فعالیت'); ?>
    </div>
</div>
<?php if($standardRequestActivityModel->radehStatus != 'verify' || $companyId == 248 || $isCandoAdmin) { ?>
<div class="row" style="direction: ltr; text-align: right;">
    <div class="col-lg-12 col-md-12">
        <?=
        $form->field($standardRequestActivityModel, 'imsDocumentIdRef')->widget(kartik\widgets\Select2::className(), [
            'data' => $mainIMSArray,
            'hideSearch' => FALSE,
            'options' => [
                'placeholder' => '--Select IMS Document--',
                'multiple' => false,
            ],
            'pluginOptions' => [
                // 'allowClear' => true
            ],
        ]);
        ?>
    </div>

    <div class="col-md-12">
        <?= $form->field($standardRequestActivityModel, 'file')->widget(kartik\widgets\FileInput::className(), [
            'pluginOptions' => [
                'showPreview' => false,
                'showUpload' => false,
//                'allowedFileExtensions' => ['xlsm','xlsb', 'xls', 'xlsx', 'pdf', 'zip', 'rar'],
            ]
        ]);
        ?>
    </div>
</div>
<?php } ?>

<?php if($standardRequestActivityModel->radehStatus != 'verify') { ?>
<div class="row" style="direction: rtl;">
    <div class="col-md-12">
        <?= $form->field($standardRequestActivityModel, "radehDescription")->textarea(); ?>
    </div>

    <div class="col-md-12">
        <?= $form->field($standardRequestActivityModel, 'radehStatus')->dropDownList($standardRequestActivityModel->getRadehStatusList(), ['prompt' => '--Select Status--']); ?>
    </div>
</div>
<?php } ?>

<?php if($standardRequestActivityModel->id > 0 && $standardRequestActivityModel->radehStatus == 'verify' && ($companyId == 248 || $isCandoAdmin)) { ?>
<div class="row" style="direction: rtl">
    <div class="col-md-12">
        <?= $form->field($standardRequestActivityModel, "holdingDescription")->textarea(); ?>
    </div>

    <div class="col-md-12">
        <?= $form->field($standardRequestActivityModel, 'holdingStatus')->dropDownList($standardRequestActivityModel->getHoldingStatusList(), ['prompt' => '--Select Status--']); ?>
    </div>
</div>
<?php } ?>

<hr class="p0 mb10" style='border-color:#B5B5B5'/>
<div class="row" >
    <div class="col-md-12 text-right" >
        <div class="form-group">
            <?= Html::submitButton($standardRequestActivityModel->isNewRecord ? '<i class="fa-plus"></i> ' . Yii::t('contract', 'Create') : '<i class="fa-edit"></i> Update', ['class' => $standardRequestActivityModel->isNewRecord ? 'btn btn-sm btn-success' : 'btn btn-sm btn-primary']) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = '$(".field-standardrequestactivity-file .file-caption-name").html("' . $standardRequestActivityModel->fileName . '");';
$this->registerJs($js, \yii\web\View::POS_READY);
?>


