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
$standardArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\Standard::find()->where(['isActive' => 1])->orderBy(['sort' => SORT_ASC])->asArray()->all(), 'id', 'title');
$companyArray = \app\components\CandoHelper::companyChildDropdown($companyId, 'array');
if(!empty($standardRequestModel->companyIdRef)) {
    $project = \app\modules\wbs\models\Epsnew::find()->joinWith('company c')->alias('e')->where(['c.PersonID' => $standardRequestModel->companyIdRef, 'e.EPSType' => 'Project', 'e.executionType' => 'executive'])->andWhere("COALESCE(isActive,'')<>'inactive'")
                        ->addSelect(['EPSID', 'EPSTitle' => "concat(IFNULL(projectCode,''), ' - ', IFNULL(EPSTitle,''))"])->all();
    $projectArray = \yii\helpers\ArrayHelper::map($project, 'EPSID', 'EPSTitle');
} else {
    $projectArray = [];
}
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

<?php if($isCandoAdmin || empty($standardRequestModel->managerialDate)) { ?>
<div class="row" style="direction: rtl">
    <div class="col-md-4">
        <?= $form->field($standardRequestModel, 'standardArray')->widget(kartik\select2\Select2::className(), [
                'data' => $standardArray,
                    'options' => [
                    'multiple' => true,
                       'prompt' => 'استاندارد',
                    ],
            ])->label(Yii::t('document', 'Standard/Model') . ' *') ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($standardRequestModel, 'projectArray')->widget(kartik\widgets\Select2::className(), [
            'data' => $projectArray,
            'id' => 'project_id',
            'options' => [
                'multiple' => true,
                'placeholder' => 'پروژه',
            ],
        ])->label(Yii::t('document', 'Project Scope') . ' *');
        ?>
    </div>
    <div class=' col-md-4'>
        <?=
        $form->field($standardRequestModel, 'companyIdRef')->widget(kartik\select2\Select2::className([]), [
            'data' => $companyArray,
            'options' => [
                'disabled' => $standardRequestModel->id > 0, 
                'readonly' => $standardRequestModel->id > 0,
                'prompt' => 'شرکت/موسسه',
            ],
        ])->label(Yii::t('document', 'Company') . ' *');
        ?>
    </div>
</div>

<div class="row" style="direction: rtl">
    <div class="col-md-3">
        <?= $form->field($standardRequestModel, 'auditFinishDate')->textInput(['placeholder' => 'مانند 1404/01/01'])->label(Yii::t('document', 'Audit Finish Date') . ' *'); ?>
    </div>

    <div class="col-md-3">
        <?= $form->field($standardRequestModel, "auditStartDate")->textInput(['placeholder' => 'مانند 1404/01/01'])->label(Yii::t('document', 'Audit Start Date') . ' *'); ?>
    </div>

    <div class="col-md-3">
        <?= $form->field($standardRequestModel, "year")->textInput(['placeholder' => 'مانند 1404'])->label(Yii::t('document', 'Year') . ' *'); ?>
    </div>

    <div class='col-md-3'>
        <?= $form->field($standardRequestModel, 'type')->dropDownList(app\modules\document\models\StandardRequest::getTypeList(), ['prompt' => 'نوع درخواست'])->label(Yii::t('document', 'Request Type') . ' *'); ?>
    </div>
</div>

<div class="row" style="direction: rtl">
    <div class="col-md-6">
        <?= $form->field($standardRequestModel, "enManagementSystemScope")->textarea()->label('محدوده فعالیت سیستم مدیریت(انگلیسی) *'); ?>
    </div>

    <div class="col-md-6">
        <?= $form->field($standardRequestModel, 'faManagementSystemScope')->textarea()->label('محدوده فعالیت سیستم مدیریت(فارسی) *'); ?>
    </div>
</div>

<div class="row" style="direction: rtl; display: flex; align-items: center;">
    <div class="col-md-9">
        <span>آیا به این بلوغ سازمانی رسیده ایم که نوع استقرار استانداردها(فعالیت ها و فایل ها) یکپارچه با هلدینگ و سایر رده ها می باشد؟ *</span>
    </div>
    <div class="col-md-3">
        <?= $form->field($standardRequestModel, 'enableSimilarActivity')->dropDownList(app\modules\document\models\StandardRequest::getEnableSimilarActivityList(), ['prompt' => ''])->label(''); ?>
    </div>
</div>
<?php } ?>

<?php if($standardRequestModel->id > 0 && ($companyId == 248 || $isCandoAdmin)) { ?>
<div class="row" style="direction: rtl">
    
    <div class="col-md-3">
        <?php if($isCandoAdmin || $standardRequestModel->isLock != 1) { ?>
            <?= $form->field($standardRequestModel, 'isLock')->dropDownList([0 => 'فعال', 1 => 'بایگانی'], ['prompt' => 'وضعیت'])->label('وضعیت'); ?>
        <?php } ?>
    </div>

    <div class="col-md-3">
        <?= $form->field($standardRequestModel, 'auditPlanFinishDate')->textInput(['placeholder' => 'مانند 1404/01/01']); ?>
    </div>
    <div class="col-md-3">
        <?= $form->field($standardRequestModel, 'auditPlanStartDate')->textInput(['placeholder' => 'مانند 1404/01/01']); ?>
    </div>

    <div class="col-md-3">
        <?= $form->field($standardRequestModel, 'holdingVerify')->checkbox([
            'template' => "{input} {label} {error}", 
            'label' => 'تایید هلدینگ',
            'class' => 'custom-checkbox'
        ]) ?>
    </div>
</div>

<?php } ?>

<hr class="p0 mb10" style='border-color:#B5B5B5'/>
<div class="row" >
    <div class="col-md-12 text-right" >
        <div class="form-group">
            <?= Html::submitButton($standardRequestModel->isNewRecord ? '<i class="fa-plus"></i> ' . Yii::t('contract', 'Create') : '<i class="fa-edit"></i> Update', ['class' => $standardRequestModel->isNewRecord ? 'btn btn-sm btn-success' : 'btn btn-sm btn-primary']) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<script>
    $('#standardrequest-companyidref').on('change', function (){
        var companyId = $(this).val();
        if (companyId) {
            $.ajax({
                url: '<?= Yii::$app->urlManager->createAbsoluteUrl(['obs/company/project-dropdown']) ?>',
                type: 'GET',
                data: { companyId: companyId },
                success: function (data) {
                    $('#standardrequest-projectarray').html(data);
                }
            });
        }
    });

    $('#standardrequest-islock').on('change', function (){
        var isLock = $(this).val();
        if (isLock == 1) {
            swal('هشدار', 'در صورت بایگانی کردن امکان فعالسازی مجدد وجود ندارد', 'warning');
        }
    });
</script>


