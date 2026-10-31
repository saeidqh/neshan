<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\assets\JdateTimeAsset;
use app\assets\DatepickerAsset;
use kartik\widgets\SwitchInput;

JdateTimeAsset::register($this);
DatepickerAsset::register($this);


$activityArray = \app\modules\document\models\ClauseActivity::find();
if($clauseActivityModel->id > 0) {
    $activityArray->where(['<>', 'id', $clauseActivityModel->id]);
}
$activityArray = \yii\helpers\ArrayHelper::map($activityArray->all(), 'id', 'title');
?>

<?php
$form = ActiveForm::begin(['id' => 'clause-activity-form',
    'enableClientValidation' => true,
    'options' => [
        'id' => 'clause-activity-grid-pjax',
        'data-pjax' => true,
        'enctype' => 'multipart/form-data'
        ]]);
?>

<div class="row" style="direction: rtl">
    <div class='col-md-12'>
        <?= $form->field($clauseActivityModel, 'title')->textInput(); ?>
    </div>
    <div class='col-md-12'>
        <?= $form->field($clauseActivityModel, 'similarActivityArray')->widget(kartik\widgets\Select2::className(), [
            'data' => $activityArray,
            'options' => [
                'placeholder' => '',
                'multiple' => true,
            ],
        ]); ?>
    </div>
</div>

<div class="row"  style="direction: rtl">
    <div class="col-md-6" >
        <?= $form->field($clauseActivityModel, 'maxSize')->textInput(); ?>
    </div>

    <div class="col-md-6" >
        <?=
        $form->field($clauseActivityModel, 'extentionArray')->widget(kartik\widgets\Select2::className(), [
            'data' => $clauseActivityModel->getExtensionList(),
            'options' => [
                'placeholder' => '--Select--',
                'multiple' => true,
            ],
        ]);
        ?>
    </div>
</div>

<div class="row"  style="direction: ltr; text-align: right;">
    <div class="col-md-6" >
        <?= $form->field($clauseActivityModel, 'file')->widget(kartik\widgets\FileInput::className(), [
            'pluginOptions' => [
                'showPreview' => false,
                'showUpload' => false,
//                'allowedFileExtensions' => ['xlsm','xlsb', 'xls', 'xlsx', 'pdf', 'zip', 'rar'],
            ]
        ]);
        ?>
    </div>

    <div class="col-md-6">
        <?= $form->field($clauseActivityModel, 'completeMethod')->textarea(); ?>
    </div>
</div>

<hr class="p0 mb10" style='border-color:#B5B5B5'/>
<div class="row" >
    <div class="col-md-12 text-right" >
        <div class="form-group">
            <?= Html::submitButton($clauseActivityModel->isNewRecord ? '<i class="fa-plus"></i> ' . Yii::t('contract', 'Create') : '<i class="fa-edit"></i> Update', ['class' => $clauseActivityModel->isNewRecord ? 'btn btn-sm btn-success' : 'btn btn-sm btn-primary']) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = '$(".field-clauseactivity-file .file-caption-name").html("' . $clauseActivityModel->fileName . '");';
$this->registerJs($js, \yii\web\View::POS_READY);
?>



