<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\assets\JdateTimeAsset;
use app\assets\DatepickerAsset;

?>


<style>
    .display-none {
        display: none;
    }


</style>

<?php
$form = ActiveForm::begin(['id' => 'document-base-type-grid',
    'enableClientValidation' => true,
    'options' => [
        'id' => 'document-base-type-grid-pjax',
        'data-pjax' => true,
        'enctype' => 'multipart/form-data'
    ]]);
?>


<div class="row" style="direction: rtl">

    <div class=' col-md-6'>
        <?=
        $form->field($certificateModel, 'base')->textInput()->label(Yii::t('document', 'Base'));
        ?>
    </div>

    <div class=' col-md-6'>
        <?=
        $form->field($certificateModel, 'authorizedWork')->textInput()->label(Yii::t('document', 'Authorized Work'));
        ?>
    </div>

</div>
<div class="row" style="direction: rtl">

    <div class=' col-md-6'>
        <?=
        $form->field($certificateModel, 'authorizedCapacity')->textInput()->label(Yii::t('document', 'Authorized Capacity'));
        ?>
    </div>
    <div class=' col-md-6'>
        <?=
        $form->field($certificateModel, 'approvedContract')->textInput()->label(Yii::t('document', 'Approved Contract'));
        ?>
    </div>
</div>


<hr class="p0 mb10" style='border-color:#B5B5B5'/>
<div class="row">
    <div class="col-md-12 text-right">
        <div class="form-group">
            <?= Html::submitButton($certificateModel->isNewRecord ? '<i class="fa-plus"></i> ' . Yii::t('contract', 'Create') : '<i class="fa-edit"></i> Update', ['class' => $certificateModel->isNewRecord ? 'btn btn-sm btn-success' : 'btn btn-sm btn-primary']) ?>
        </div>
    </div>
</div>


<?php ActiveForm::end(); ?>


