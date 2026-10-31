<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\assets\JdateTimeAsset;
use app\assets\DatepickerAsset;
use kartik\widgets\SwitchInput;

JdateTimeAsset::register($this);
DatepickerAsset::register($this);

$clauseArray = \app\modules\document\models\Clause::clauseArray();
$standardArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\Standard::find()->where(['isActive' => 1])->orderBy('sort')->all(), 'id', 'title');
?>

<?php
$form = ActiveForm::begin(['id' => 'clause-form',
    'enableClientValidation' => true,
    'options' => [
        'id' => 'clause-grid-pjax',
        'data-pjax' => true,
        'enctype' => 'multipart/form-data'
        ]]);
?>

<div class="row"  style="direction: rtl">
    <div class="col-md-6" >
        <?=
        $form->field($clauseModel, 'type')->dropDownList(
                $clauseModel->getTypeList()
                , [
            'prompt' => 'Select Type'
        ])->label(Yii::t('document', 'Clause Type') . ' *');
        ?>
    </div>

    <div class=' col-md-6'>
        <?= $form->field($clauseModel, 'title')->textInput()->label(Yii::t('document', 'Clause Name') . ' *'); ?>
    </div>
</div>

<div class="row"  style="direction: rtl">
    <div class="col-md-6" >
        <?= $form->field($clauseModel, 'mainClause')->textInput()->label('بند *'); ?>
    </div>

    <div class=' col-md-6'>
        <?= $form->field($clauseModel, 'parentIdRef')->dropDownList($clauseArray, ['prompt' => '--Select Parent--'])->label('والد'); ?>
    </div>
</div>

<div class="row"  style="direction: rtl">
    <div class="col-md-6" >
        <?=
        $form->field($clauseModel, 'standard')->widget(kartik\widgets\Select2::className(), [
            'data' => $standardArray,
            // 'disabled' => $clauseModel->parentIdRef > 0,
            'options' => [
                'placeholder' => '--Select--',
                'multiple' => true,
            ],
        ])->label(Yii::t('document', 'Standard/Model') . ' *');
        ?>
    </div>

    <div class="col-md-6" >
        <?=
        $form->field($clauseModel, 'codifiedInformation')->dropDownList(
                $clauseModel->getCodifiedInformationList()
                , [
            'prompt' => 'Select Codified Information'
        ])->label(Yii::t('document', 'Codified Information') . ' *');
        ?>
    </div>
</div>

<div class="row" style="direction: rtl">
    <div class='col-md-12'>
        <?= $form->field($clauseModel, 'description')->textarea()->label(Yii::t('document', 'Description') . ' *'); ?>
    </div>
</div>

<div class="row" style="direction: rtl">
    <div class="col-md-6">
        <?= $form->field($clauseModel, "isActive")->widget(SwitchInput::className()); ?>
    </div>                
    <div class="col-md-3">
        <?= $form->field($clauseModel, 'sort')->textInput(); ?>
    </div>
    <div class="col-md-3">
        <?= $form->field($clauseModel, 'weight')->textInput(); ?>
    </div>
</div>


<hr class="p0 mb10" style='border-color:#B5B5B5'/>
<div class="row" >
    <div class="col-md-12 text-right" >
        <div class="form-group">
            <?= Html::submitButton($clauseModel->isNewRecord ? '<i class="fa-plus"></i> ' . Yii::t('contract', 'Create') : '<i class="fa-edit"></i> Update', ['class' => $clauseModel->isNewRecord ? 'btn btn-sm btn-success' : 'btn btn-sm btn-primary']) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>


<script>
    $('#clause-parentidref').change(function () {
        var id = $(this).val();
        /*if(id > 0) {
           $("#clause-standard").attr('disabled', 'disabled').addClass('disabled');
        } else {
            $("#clause-standard").removeAttr('disabled').removeClass('disabled');
        } */  
        if (id > 0) {
                ajaxProgressLoader('show', '#clause-grid-pjax');
                var url = '<?= Yii::$app->urlManager->createAbsoluteUrl(['document/clause/standard-items']) ?>' + '&id=' + id;
                $.ajax({
                    url: url,
                    dataType: "json"
                }).done(function (data) {
                    $("#clause-standard").val(data).trigger('change');      
                }).error(function () {
                    showMsgBoxInBottomRight(
                            "Please select a valid element!",
                            "warning"
                            );
                });
                ajaxProgressLoader('hide', '#clause-grid-pjax');        
        }   
    });
</script>


