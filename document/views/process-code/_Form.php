<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\assets\JdateTimeAsset;
use app\assets\DatepickerAsset;
use kartik\widgets\SwitchInput;

JdateTimeAsset::register($this);
DatepickerAsset::register($this);
?>


<style>
    .display-none {
        display: none;
    }


</style>

<?php
$form = ActiveForm::begin(['id' => 'position-form',
            'enableClientValidation' => true,
            'options' => [
                'id' => 'position-grid-pjax',
                'data-pjax' => true,
                'enctype' => 'multipart/form-data'
        ]]);
?>

<div class="row" style="direction: rtl">

    <div class="col-md-6">
        <?=
        $form->field($processCodeModel, 'Title')->textInput()->label(Yii::t('document', 'English Title'));
        ?>
    </div>

    <div class=' col-md-6'>
        <?=
        $form->field($processCodeModel, 'PersianTitle')->textInput()->label(Yii::t('document', 'Persian Title') . ' *');
        ?>
    </div>

</div>

<div class="row" style="direction: rtl">

    <div class=' col-md-6'>
        <?=
        $form->field($processCodeModel, 'ProcessGroup')->dropDownList(
                $processCodeModel::getProcessGroupList(),
                [
                    'prompt' => 'گروه فرآیند',
                ]
        )->label(Yii::t('document', 'Process Group') . ' *');
        ?>
    </div>

    <div class="col-md-6">
        <?=
        $form->field($processCodeModel, 'code')->textInput()->label(Yii::t('document', 'Code') . ' *');
        ?>
    </div>

</div>

<div class="row" style="direction: rtl">
    
    <div class="col-md-6">
        <?= $form->field($processCodeModel, "isActive")->widget(SwitchInput::className()); ?>
    </div>

    <div class="col-md-6">
        <?=
        $form->field($processCodeModel, 'sort')->textInput();
        ?>
    </div>

</div>


<hr class="p0 mb10" style='border-color:#B5B5B5'/>
<div class="row">
    <div class="col-md-12 text-right">
        <div class="form-group">
            <?= Html::submitButton($processCodeModel->isNewRecord ? '<i class="fa-plus"></i> ' . Yii::t('contract', 'Create') : '<i class="fa-edit"></i> Update', ['class' => $processCodeModel->isNewRecord ? 'btn btn-sm btn-success' : 'btn btn-sm btn-primary']) ?>
        </div>
    </div>
</div>


<?php ActiveForm::end(); ?>


