<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\assets\JdateTimeAsset;
use app\assets\DatepickerAsset;

JdateTimeAsset::register($this);
DatepickerAsset::register($this);
$jDate = new \app\components\jDateTime(true, true, 'Asia/Tehran');
$stampType = \yii\helpers\ArrayHelper::map(\app\modules\document\models\StampTypeDocument::find()
            ->all(), 'StampTypeId', 'TypeName');
$isDirectCreate = isset($isDirectCreate) ? (bool)$isDirectCreate : false;
$isStampFileRequired = $isDirectCreate && $stampModel->isNewRecord;

if (!isset($flowModel)) {
    $flowModel = null;
}
?>

<style>
    .display-none {
        display: none;
    }

    .stamp-flow-section .text-on-panel {
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

    .stamp-flow-section .panel {
        margin-top: 27px !important;
    }

    .stamp-flow-section .panel-body {
        border: 2px solid #337ab7;
        border-radius: 8px;
        padding-bottom: 0 !important;
    }

    .stamp-flow-section .panel-body strong {
        font-size: 13px !important;
    }

    .stamp-flow-section select {
        padding: 0 !important;
        font-size: 12px !important;
    }
</style>

<?php
$form = ActiveForm::begin([
    'id' => 'stamp-form',
    'enableClientValidation' => true,
    'options' => [
        'enctype' => 'multipart/form-data',
        'data-pjax' => 0,
    ],
]);
?>

<div class="row" style="direction: rtl">
    <div class='col-md-3'>
        <?= $form->field($stampModel, 'SerialNumber')->textInput(); ?>
    </div>

    <div class='col-md-3'>
        <?= $form->field($stampModel, 'StampPurpose')->textInput()->label(Yii::t('document', 'Stamp Purpose')); ?>
    </div>

    <div class='col-md-6'>
        <?= $form->field($stampModel, 'StampTitle')->textInput()->label(Yii::t('document', 'Stamp Title') . ' *'); ?>
    </div>
</div>


<div class="row" style="direction: rtl">
        <div class="col-md-3">
        <?=
        $form->field($stampModel, 'RequestDate')->textInput([
            "placeholder" => "برای مثال 1403/09/03 ",
        ])->label(Yii::t('document', 'RequestDate') . ' *');
        ?>
    </div>

        <div class="col-md-3">
        <?=
        $form->field($stampModel, 'StampTypeIDRef')->widget(kartik\widgets\Select2::className(), [
            'options' => [
                'placeholder' => '--Select--',
            ],
            'data' => $stampType,
        ])->label(Yii::t('document', 'Stamp Type') . ' *');
        ?>
    </div>
        <div class="col-md-6">
        <?=
        $form->field($stampModel, 'ObsIDRef')
            ->widget(app\components\ObsnewPickerPro::className(), ['child' => true])
            ->label(Yii::t('document', 'Position') . ' *');
        ?>
    </div>
<?= $form->field($stampModel, 'ProcessCodeIDRef')->hiddenInput(['id' => 'stampdocument-processcodeidref'])->label(false); ?>
</div>

<div class="row" style="direction: rtl">
    <div class="col-md-6">
        <?=
        $form->field($stampModel, 'internalScopeIds')->widget(kartik\widgets\Select2::className(), [
            'data' => $internalScopes,
            'options' => [
                'placeholder' => '--Select--',
                'multiple' => true,
                'tabindex' => '120'
            ],
        ])->label(Yii::t('document', 'Internal Scope'));
        ?>
    </div>
    <div class="col-md-6">
        <?=
        $form->field($stampModel, 'externalScopeIds')->widget(kartik\widgets\Select2::className(), [
            'data' => $externalScopes,
            'options' => [
                'placeholder' => '--Select--',
                'multiple' => true,
                'tabindex' => '120'
            ],
        ])->label(Yii::t('document', 'External Scope'));
        ?>
    </div>
</div>

<div class="row" style="direction: rtl">
    <?= $form->field($stampModel, 'HumanResourceIDRef')->hiddenInput()->label(false); ?>
    <div class="col-lg-3 col-md-3">
        <?= $form->field($stampModel, 'FullName')->textInput(['readonly' => true]); ?>
    </div>

    <div class="col-lg-3 col-md-3">
        <?= $form->field($stampModel, 'NationalCode')->textInput(['disabled' => $stampModel->StampTypeIDRef != 4]); ?>
    </div>

    <div class="col-md-6" style="direction: ltr; text-align: right;">
        <?=
        $form->field($stampModel, 'StampFile')
            ->widget(\kartik\widgets\FileInput::className(), [
                'pluginOptions' => [
                    'showPreview' => true,
                    'showUpload' => false,
                    'allowedFileExtensions' => ['png', 'jpg'],
                    'maxFileSize' => 800
                ],
                'options' => [
                    'accept' => '*',
                    'required' => $isStampFileRequired,
                ]
            ])->label(Yii::t('document', 'Stamp File') . ($isStampFileRequired ? ' *' : ''));
        ?>
    </div>
</div>

<div class="row" style="direction: rtl; display:flex; align-items:center;">


    <div class="col-md-10">
        <div style="padding-top: 10px;">
            <strong> * سطح مجوز دهنده : </strong>
            اینجانب با تعهد اعلام می دارم در نشان سازمانی فوق،
            <span style="color: #a32d00">نام</span>
            و یا
            <span style="color: #a32d00">آرم و نشان/لوگو</span>
            کارگاه، پروژه، موسسه، شرکت، هلدینگ و رده بالا دست
        </div>
    </div>
        <div class="col-md-2">
        <?=
        $form->field($stampModel, 'OrganizationalMaker')->widget(kartik\widgets\Select2::className(), [
            'data' => \app\modules\document\models\StampDocument::getOrganizationalMakerListInForm(),
            'options' => [
                'placeholder' => 'وجود دارد ؟',
            ],
        ])->label(Yii::t('document', '') . '');
        ?>
    </div>
</div>
<?php if ($isDirectCreate && $flowModel !== null): ?>

    <div class="stamp-flow-section">

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
                        ->textInput([
                            'autocomplete' => 'off',
                            'required' => true,
                        ])
                        ->label(Yii::t('automation', 'NationalCode') . ' *'); ?>
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
                            ->textInput(['placeholder' => 'مانند 1403/06/02','required' => true,])
                            ->label(Yii::t('document', 'Received Date') . ' *'); ?>
                    </div>

                    <div class="col-md-6">
                        <?= $form->field($flowModel, 'ObsIDRef')
                            ->widget(app\components\ObsnewPickerPro::className(), ['child' => true])
                            ->label(Yii::t('obs', 'Organizational Position *')); ?>
                    </div>
                </div>

            </div>
        </div>

    </div>

<?php endif; ?>
<hr class="p0 mb10" style='border-color:#B5B5B5'/>
<div class="row">
    <div class="col-md-12 text-right">
        <div class="form-group">
            <?= Html::submitButton($stampModel->isNewRecord ? '<i class="fa-plus"></i> ' . Yii::t('contract', 'Create') : '<i class="fa-edit"></i> Update', ['class' => $stampModel->isNewRecord ? 'btn btn-sm btn-success' : 'btn btn-sm btn-primary']) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = '$(".field-stampdocument-stampfile .file-caption-name").html("' . $stampModel->StampDocument . '");';
$this->registerJs($js, \yii\web\View::POS_READY);
?>

<script>
    $('#stampdocument-nationalcode').change(function () {
        var nationalCode = $(this).val();
        if (nationalCode !== null && nationalCode !== '') {
            ajaxProgressLoader('show', '#personal-specification');
            var url = '<?= Yii::$app->urlManager->createAbsoluteUrl(['automation/automation/human-info']) ?>' + '&nationalCode=' + nationalCode;
            $.ajax({
                url: url,
                dataType: "json"
            }).done(function (data) {
                const humanModel = data.humanModel;
                if (humanModel !== null) {
                    $('#stampdocument-humanresourceidref').val(humanModel.Id);
                    $('#stampdocument-fullname').val(humanModel.FirstName + ' ' + humanModel.LastName);
                } else {
                    $('#stampdocument-fullname').val(null);
                    $('#stampdocument-humanresourceidref').val(null);
                }
            }).error(function () {
                showMsgBoxInBottomRight(
                        "Please select a valid element!",
                        "warning"
                        );
            });
            ajaxProgressLoader('hide', '#personal-specification');
        } else {
            $('#stampdocument-fullname').val(null);
            $('#stampdocument-humanresourceidref').val(null);
        }
    });

    $('#stampdocument-stamptypeidref').change(function () {
        var stamptype = $(this).val();
        if (stamptype == 4) {
            $('#stampdocument-fullname').val(null);
            $('#stampdocument-humanresourceidref').val(null);
            $("#stampdocument-nationalcode").removeAttr('disabled');
        } else {
            $('#stampdocument-fullname').val(null);
            $('#stampdocument-nationalcode').val(null);
            $('#stampdocument-humanresourceidref').val(null);
            $("#stampdocument-nationalcode").attr('disabled', 'disabled');
        }
    });


    // =========================================================
    // ✅ پر کردن خودکار ProcessCode با انتخاب Obs
    // =========================================================
    $('#stampdocument-obsidref').on('change', function() {
        var obsId = $(this).val();
        if (obsId) {
            ajaxProgressLoader('show', '.modal-content');
            $.ajax({
                url: '<?= Yii::$app->urlManager->createAbsoluteUrl(['document/stamp-document/get-process-code']) ?>',
                type: 'GET',
                data: {obsId: obsId},
                dataType: 'json',
                success: function(data) {
                    if (data.processCode) {
                        $('#stampdocument-processcodeidref').val(data.processCode);
                    } else {
                        $('#stampdocument-processcodeidref').val('');
                    }
                    ajaxProgressLoader('hide', '.modal-content');
                },
                error: function() {
                    ajaxProgressLoader('hide', '.modal-content');
                    if (typeof showMsgBoxInBottomRight !== 'undefined') {
                        showMsgBoxInBottomRight('خطا در دریافت کد فرایند', 'error');
                    }
                }
            });
        }
    });
    // =========================================================
    // ✅ پر کردن اطلاعات بهره‌بردار با کد ملی در ثبت مستقیم
    // =========================================================
    $(document).off('change blur', '#stampflowdocument-nationalcode');
    $(document).on('change blur', '#stampflowdocument-nationalcode', function () {
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

                if (
                    humanModel !== null &&
                    humanModel.Verified == 1 &&
                    humanModel.Status === 'Employment'
                ) {
                    $('#stampflowdocument-firstname').val(humanModel.FirstName || '');
                    $('#stampflowdocument-lastname').val(humanModel.LastName || '');
                    $('#stampflowdocument-employmenttype').val(humanModel.EmploymentType || '');
                } else {
                    $('#stampflowdocument-firstname').val('');
                    $('#stampflowdocument-lastname').val('');
                    $('#stampflowdocument-employmenttype').val('');

                    if (typeof showMsgBoxInBottomRight !== 'undefined') {
                        showMsgBoxInBottomRight("اشتغال شخص توسط گزینش منابع انسانی هلدینگ تایید نشده است", "Error");
                    }
                }
            }).fail(function () {
                if (typeof showMsgBoxInBottomRight !== 'undefined') {
                    showMsgBoxInBottomRight("خطای سرور", "Error");
                }
            }).always(function () {
                ajaxProgressLoader('hide', '#personal-specification');
            });
        } else {
            $('#stampflowdocument-firstname').val('');
            $('#stampflowdocument-lastname').val('');
            $('#stampflowdocument-employmenttype').val('');
        }
    });
</script>