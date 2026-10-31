<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin(['id' => 'ims-document',
            'enableClientValidation' => true,
            'options' => [
                'data-pjax' => '1',
                'enctype' => 'multipart/form-data',
        ]]);
?>

<div class="row" style="background-color: rgb(228, 246, 253);margin-left: -20px;margin-right: -20px;margin-top: -15px;margin-bottom: -15px;">
    <br> 

    <div class="col-md-12 col-lg-12">
        <div class="col-lg-6 col-md-6" style="padding-left: 5px;padding-right: 0px;margin-left: -4px;">
            <?php
            echo $form->field($imsDocumentKeywordListModel, 'title')->textInput();
            ?>
        </div>
        

    <br>
    <br>
    <div class="col-lg-12 col-md-12">
        <div class="form-group">
<?= Html::submitButton('<i class="fa-plus"></i> ' . $imsDocumentKeywordListModel->isNewRecord ? Yii::t('app', 'Create'): Yii::t('app', 'Update'), ['class' => 'btn btn-sm btn-success']) ?>
        </div>
    </div>

</div>

<?php ActiveForm::end(); ?>




