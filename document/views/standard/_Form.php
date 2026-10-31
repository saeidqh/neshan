<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\assets\JdateTimeAsset;
use app\assets\DatepickerAsset;
use kartik\widgets\SwitchInput;

JdateTimeAsset::register($this);
DatepickerAsset::register($this);

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
    <div class="col-md-6">
        <?= $form->field($standardModel, 'area')->textInput(); ?>
    </div>

    <div class=' col-md-6'>
        <?= $form->field($standardModel, 'title')->textInput(); ?>
    </div>
</div>

<div class="row" style="direction: rtl">
    <div class='col-md-12'>
        <?= $form->field($standardModel, 'description')->textarea(); ?>
    </div>
</div>

<div class="row" style="direction: rtl">
    <div class="col-md-6">
        <?= $form->field($standardModel, "isActive")->widget(SwitchInput::className()); ?>
    </div>

    <div class="col-md-6">
        <?=
        $form->field($standardModel, 'sort')->textInput();
        ?>
    </div>
</div>


<hr class="p0 mb10" style='border-color:#B5B5B5'/>
<div class="row" >
    <div class="col-md-12 text-right" >
        <div class="form-group">
            <?= Html::submitButton($standardModel->isNewRecord ? '<i class="fa-plus"></i> ' . Yii::t('contract', 'Create') : '<i class="fa-edit"></i> Update', ['class' => $standardModel->isNewRecord ? 'btn btn-sm btn-success' : 'btn btn-sm btn-primary']) ?>
        </div>
    </div>
</div>



<?php ActiveForm::end(); ?>


