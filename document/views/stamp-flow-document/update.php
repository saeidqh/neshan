<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $flowModel app\modules\document\models\StampFlowDocument */

$form = ActiveForm::begin([
    'id' => 'flow-form-update',
    'enableClientValidation' => true,
    'options' => [
        'data-pjax' => true,
        'enctype' => 'multipart/form-data',
    ],
]);
?>

<style>
    .display-none { display: none; }
    #content > div:first-child { padding: 0; }
    #content .panel { margin-top: 10px !important; }
    .text-on-panel {
        background: #FFF2B3 none repeat scroll 0 0;
        color: #0b58a2;
        height: auto;
        margin-left: 20px;
        padding: 3px 5px;
        position: absolute;
        margin-top: -31px;
        border: 2px solid #337ab7;
        border-radius: 8px;
    }
    .panel { margin-top: 27px !important; }
    .panel-body {
        border: 2px solid #337ab7;
        border-radius: 8px;
        padding-bottom: 0 !important;
    }
    .panel-body strong { font-size: 13px !important; }
    select { padding: 0 !important; font-size: 12px !important; }
</style>

<div>

    <div class="panel panel-primary" id="personal-specification" style="direction: rtl">
        <div class="panel-body">
            <h4 class="text-on-panel text-primary">
                <strong style="font-size: 1.9rem">
                    <?= Yii::t("automation", 'Personal Specification') ?>
                </strong>
            </h4>

            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <?= $form->field($flowModel, 'EmploymentType')
                        ->dropDownList($flowModel->getEmploymentTypeList(), [
                            'prompt' => Yii::t('automation', 'Employment Type'),
                            'disabled' => true
                        ])
                        ->label(Yii::t('automation', 'Employment Type')); ?>
                </div>

                <div class="col-lg-6 col-md-6">
                    <?= $form->field($flowModel, 'NationalCode')
                        ->textInput(['disabled' => false, 'autocomplete' => 'off'])
                        ->label(Yii::t('automation', 'NationalCode')); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <?= $form->field($flowModel, 'LastName')
                        ->textInput(['disabled' => true])
                        ->label(Yii::t('automation', 'Last Name')); ?>
                </div>

                <div class="col-lg-6 col-md-6">
                    <?= $form->field($flowModel, 'FirstName')
                        ->textInput(['disabled' => true])
                        ->label(Yii::t('automation', 'First Name')); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-primary" style="direction: rtl">
        <div class="panel-body">
            <h4 class="text-on-panel text-primary">
                <strong style="font-size: 1.9rem">
                    <?= Yii::t("automation", 'Company Information') ?>
                </strong>
            </h4>

            <div class="row" style="direction: rtl">
                <div class="col-md-6">
                    <?= $form->field($flowModel, 'ReceivedDate')
                        ->textInput(['placeholder' => 'مانند 1403/06/02'])
                        ->label(Yii::t('document', 'Received Date')); ?>
                </div>

                <div class="col-md-6">
                    <?= $form->field($flowModel, 'ObsIDRef')
                        ->widget(app\components\ObsnewPickerPro::className(), ['child' => true])
                        ->label(Yii::t('obs', 'Organizational Position *')); ?>
                </div>
            </div>

            <div class="row" style="direction: rtl">
                <div class="col-md-6">
                    <?= $form->field($flowModel, 'SerialNumber')
                        ->textInput(['maxlength' => true, 'placeholder' => 'شناسه یکتا نشان '])
                        ->label('شناسه یکتا نشان '); ?>
                </div>

                <div class="col-md-6">
                    <?= $form->field($flowModel, 'FinalStampFile')
                        ->fileInput()
                        ->label('آپلود تصویر نهایی نشان'); ?>
                </div>
            </div>
            <div class="row" style="direction: rtl">
    <div class="col-md-6">
        <?= $form->field($flowModel, 'AttachmentFile')
            ->fileInput([
                'id' => 'attachment-file-input',
                'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png',
            ])
            ->label('آپلود مستندات'); ?>

        <div style="font-size: 12px; color: #777; margin-top: -10px; margin-bottom: 10px;">
            حداکثر حجم مجاز: ۵۰۰ کیلوبایت - فرمت‌های مجاز:
            pdf, doc, docx, jpg, jpeg, png
        </div>
    </div>

    <div class="col-md-6">
        <?php if (!$flowModel->isNewRecord && !empty($flowModel->AttachmentDocument)): ?>
            <label>مستندات بارگذاری شده</label>
            <div style="margin-top: 5px;">
                <?= Html::a(
                    '<i class="fa fa-download"></i> دانلود مستندات',
                    Url::to([
                        '/document/stamp-flow-document/download-attachment',
                        'sid' => $flowModel->FlowId
                    ]),
                    [
                        'class' => 'btn btn-sm btn-info',
                        'data-pjax' => '0',
                        'target' => '_blank',
                    ]
                ) ?>

                <span style="margin-right: 10px; color: #555;">
                    <?= Html::encode($flowModel->AttachmentDocument) ?>
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>

            <div class="row">
                <div class="col-md-12 text-right">
                    <?= Html::submitButton(
                        '<i class="fa fa-edit"></i> ' . Yii::t('contract', 'Update'),
                        [
                            'id' => 'submit-button-update',
                            'class' => 'btn btn-sm btn-primary'
                        ]
                    ) ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?php ActiveForm::end(); ?>

<script>
    $(document).ready(function () {

        $('#flow-form-update').on('beforeSubmit', function () {
            $('#submit-button-update').prop('disabled', true);
        });

        $('#stampflowdocument-nationalcode').on('change blur', function () {
            var nationalCode = $(this).val();

            if (nationalCode !== null && nationalCode !== '') {
                ajaxProgressLoader('show', '#personal-specification');

                var url = '<?= Yii::$app->urlManager->createAbsoluteUrl(['automation/automation/human-info']) ?>'
                    + '&nationalCode=' + encodeURIComponent(nationalCode)
                    + '&hasDecree=false';

                $.ajax({
                    url: url,
                    dataType: 'json'
                }).done(function (data) {
                    const humanModel = data.humanModel;

                    if (humanModel !== null && humanModel.Verified === 1 && humanModel.Status === 'Employment') {
                        $('#stampflowdocument-firstname').val(humanModel.FirstName || '');
                        $('#stampflowdocument-lastname').val(humanModel.LastName || '');
                        $('#stampflowdocument-employmenttype').val(humanModel.EmploymentType || '');
                    } else {
                        $('#stampflowdocument-firstname').val('');
                        $('#stampflowdocument-lastname').val('');
                        $('#stampflowdocument-employmenttype').val('');
                        showMsgBoxInBottomRight("اشتغال شخص توسط گزینش منابع انسانی هلدینگ تایید نشده است", "Error");
                    }
                }).fail(function () {
                    showMsgBoxInBottomRight("خطای سرور", "Error");
                }).always(function () {
                    ajaxProgressLoader('hide', '#personal-specification');
                });
            }
        });

    });
</script>