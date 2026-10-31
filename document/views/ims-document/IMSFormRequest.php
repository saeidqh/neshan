<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin(['id' => 'ims-document-form',
            'enableClientValidation' => true,
            'options' => [
                'data-pjax' => '1',
                'enctype' => 'multipart/form-data',
        ]]);

$disabled = $id > 0 ? true : false;
?>

<style>
    .hidden-widget {
        display: none;
    }
</style>

<div class="row"
     style="background-color: rgb(228, 246, 253);margin-left: -20px;margin-right: -20px;margin-top: -15px;margin-bottom: -15px;">
    <br>

    <div class="col-md-12 col-lg-12">
        <!--        <div class="col-lg-6 col-md-6" style="padding-left: 5px;padding-right: 0px;margin-left: -4px;">
        <?php
        echo $form->field($imsDocumentModel, 'preparatorUnit')
                ->widget(kartik\widgets\Select2::className(), [
                    'data' => $obsArray,
                    'options' => [
                        'prompt' => '...select item ...'
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
        ]);
        ?>
                </div>-->
        <div class="col-lg-12 col-md-12" style="padding-left: 0px;padding-right: 0px;">
            <?=
            $form->field($imsDocumentModel, 'companyIdRef')->widget(kartik\select2\Select2::className(), [
                'data' => $companyArray,
                'disabled' => $disabled,
                'pluginEvents' => [
                    'change' => 'function(){
                                    apiPreparatorUnitContent($(this).val())
                                }',
                ]
            ]);
            ?>
        </div>
    </div>
    <div class="col-md-12 col-lg-12">
        <div class="col-lg-6 col-md-6" style="padding-left: 5px;padding-right: 0px;margin-left: -4px;">
            <?=
            $form->field($imsDocumentModel, 'processCode')->widget(kartik\select2\Select2::className(), [
                'data' => $processCodeArray,
                'disabled' => $disabled,
                'options' => [
                    'placeholder' => '-- Select Process Code --',
                ],
            ]);
            ?>
        </div>
        <div class="col-lg-6 col-md-6" style="padding-left: 5px;padding-right: 0px;">
            <?=
            $form->field($imsDocumentModel, 'type')->widget(kartik\select2\Select2::className(), [
                'data' => $imsTypeArray,
                'disabled' => $disabled,
                'options' => [
                    'placeholder' => '-- Select IMS Type --',
                ],
            ]);
            ?>
        </div>

    </div>
    <div class="col-md-12 col-lg-12">
<?= $form->field($imsDocumentModel, 'title')
        ->textInput(["list" => "suggestionTitle", "autoComplete" => "off"])
?>

    </div>

    <datalist id="suggestionTitle">
        <?php
        foreach ($titleArray as $value) {
            echo '<option value="' . $value['title'] . '">';
        }
        ?>
    </datalist>

    <div class="col-lg-12 col-md-12">
        <?=
        $form->field($imsDocumentModel, 'mainDocumentID')->widget(kartik\widgets\Select2::className(), [
            'data' => $mainIMSArray,
            'hideSearch' => FALSE,
            'options' => [
                'placeholder' => '--Select Main Document--',
                'multiple' => false,
            ],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
        ?>
    </div>

    <div class="col-md-12" >
        <?=
        $form->field($imsDocumentModel, 'clause')->widget(kartik\widgets\Select2::className(), [
            'data' => $clauseArray,
            'options' => [
                'placeholder' => '--Select--',
                'multiple' => true,
            ],
        ])
        ?>
    </div>

<?php if ($imsDocumentModel->revisionNumber > 0) { ?>
        <div class="col-md-12 col-lg-12">
            <div class="col-lg-6 col-md-6" style="padding-left: 5px;padding-right: 0px;margin-left: -4px;">
                <?= $form->field($imsDocumentModel, 'code')
                        ->textInput(["list" => "suggestionCode", "autoComplete" => "off", 'disabled' => $disabled]);
                ?>
            </div>
            <div class="col-lg-6 col-md-6" style="padding-left: 5px;padding-right: 0px;">
            <?= $form->field($imsDocumentModel, 'revisionNumber')
                    ->textInput(['disabled' => 'disabled']);
            ?>
            </div>
        </div>

        <datalist id="suggestionCode">
    <?php
    foreach ($codeListArray as $value) {
        echo '<option value="' . $value['code'] . '">';
    }
    ?>
        </datalist>

        <div class="row ml5 mr5" >
            <div class="col-lg-8 col-md-8" >
                <?= $form->field($imsDocumentModel, 'autoCode')->textInput(['class' => 'form-control input-sm', 'tabindex' => '100', 'disabled' => 'disabled']) ?>
            </div>
                <?php if (false) { ?>
                <div class="col-lg-4 col-md-4" style="padding-top: 23px;">
                    <?=
                    Html::a('<i class="fa-refresh"></i> Generate Document Code', '#', [
                        'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/generate-auto-code']),
                        'class' => 'btn btn-sm btn-primary pull-right mb10',
                        'id' => 'btnGenerateDocumentCode',
                        'style' => 'background-color: #2bcd8a;',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
                        'data-original-title' => 'Create Document Code',
                        'onclick' => 'return btnGenerateDocumentCode(this);',
                    ]);
                    ?>
                </div>
                <?php } ?>
        </div>

            <?php } ?>

    <div class="col-lg-12 col-md-12">
            <?php if (in_array('preparationDate', $visibleField)) { ?>
            <div class="col-lg-4 col-md-4" style="padding-left: 0px;padding-right: 5px;">
                <label class="control-label" for=""><?php echo Yii::t('document', 'Preparation Date Hijri'); ?></label>
                <?=
                Html::input("text", "", "", [
                    "id" => "imsdocument-preparationHijriDate",
                    "class" => "form-control input-sm datepicker-fa",
                    "placeholder" => "example 1395/02/02",
                    'disabled' => $disabled
                ]);
                ?>
            </div>
            <div class="hidden-widget col-lg-4 col-md-4" style="padding-left: 5px;padding-right: 0px;">
                <?= $form->field($imsDocumentModel, 'preparationDate')->widget(kartik\widgets\DatePicker::className()); ?>
            </div>
            <?php } if (in_array('revisionDate', $visibleField)) { ?>
            <div class="col-lg-4 col-md-4" style="padding-left: 0px;padding-right: 5px;">
                <label class="control-label" for=""><?php echo Yii::t('document', 'Revision Date Hijri'); ?></label>
                <?=
                Html::input("text", "", "", [
                    "id" => "imsdocument-revisionHijriDate",
                    "class" => "form-control input-sm datepicker-fa",
                    "placeholder" => "example 1395/02/02",
                    'disabled' => !$disabled
                ]);
                ?>
            </div>
            <div class="hidden-widget col-lg-4 col-md-4" style="padding-left: 5px;padding-right: 0px;">
                <?= $form->field($imsDocumentModel, 'revisionDate')->widget(kartik\widgets\DatePicker::className()); ?>
            </div>

            <?php } if (in_array('effectiveDate', $visibleField)) { ?>
            <div class="col-lg-4 col-md-4" style="padding-left: 0px;padding-right: 0px;">
                <label class="control-label" for=""><?php echo Yii::t('document', 'Effective Date Hijri'); ?></label>
                <?=
                Html::input("text", "", "", [
                    "id" => "imsdocument-effectiveHijriDate",
                    "class" => "form-control input-sm datepicker-fa",
                    "placeholder" => "example 1395/02/02",
                    'disabled' => !$disabled
                ]);
                ?>
            </div>
            <div class="hidden-widget col-lg-4 col-md-4" style="padding-left: 5px;padding-right: 0px;">
                <?= $form->field($imsDocumentModel, 'effectiveDate')->widget(kartik\widgets\DatePicker::className()); ?>
            </div>
            <?php } ?>
    </div>

            <?php if ($imsDocumentModel->revisionNumber > 0) { ?>
        <div class="col-lg-12 col-md-12">
            <div class="col-lg-6 col-md-6" style="padding-left: 0px;padding-right: 0px;">
                <?=
                        $form->field($imsDocumentModel, 'reportObject')
                        ->widget(\kartik\widgets\FileInput::className(), [
                            'pluginOptions' => [
                                'showPreview' => false,
                                'showUpload' => false,
                                'allowedFileExtensions' => [
                                    'xls', 'xlsx', 'xlsb', 'pdf', 'rar', 'zip', 'doc', 'docx', 'jpg', 'jpeg', 'bmp', 'png',
                                ],
                            ],
                            'options' => ['accept' => '*']
                        ])
                ?>
            </div>
            <div class="col-lg-6 col-md-6" style="padding-left: 0px;padding-right: 0px;">
                <?=
                        $form->field($imsDocumentModel, 'nativeObject')
                        ->widget(\kartik\widgets\FileInput::className(), [
                            'pluginOptions' => [
                                'showPreview' => false,
                                'showUpload' => false,
                                'allowedFileExtensions' => [
                                    'xls', 'xlsx', 'xlsb', 'pdf', 'rar', 'zip', 'doc', 'docx', 'jpg', 'jpeg', 'bmp', 'png',
                                ],
                            ],
                            'options' => ['accept' => '*']
                        ])
                ?>
            </div>
        </div>
    <?php } ?>
    <!--    <div class="col-lg-12 col-md-12">
    <?php
    echo $form->field($imsDocumentModel, 'relatedIMSDocument')->widget(kartik\widgets\Select2::className(), [
        'data' => $relatedIMSArray,
        'id' => "myRelated",
        'hideSearch' => FALSE,
        'options' => [
            'placeholder' => '--Select IMS--',
            'multiple' => TRUE,
        ],
    ]);
    ?>
        </div>-->
    <div class="col-lg-12 col-md-12">
<?=
$form->field($imsDocumentModel, 'relatedOBS')->widget(kartik\widgets\Select2::className(), [
    'data' => $processCodeArray,
    'options' => [
        'placeholder' => '--Code Related--',
        'multiple' => TRUE,
    ],
]);
?>
    </div>

    <br>
    <br>
    <div class="col-lg-12 col-md-12">
        <div class="form-group">
<?= Html::submitButton('<i class="fa-plus"></i> ' . (($imsDocumentModel->revisionNumber == 0) ? Yii::t('document', 'Create') : Yii::t('document', 'Revision')), ['id' => 'submit-button', 'class' => 'btn btn-sm btn-success']) ?>
        </div>
    </div>


</div>

<?php ActiveForm::end(); ?>


<script>
    $('.datepicker-fa').each(function () {
        $(this).datepicker({dateFormat: 'yy/mm/dd'});
    });
    function btnGenerateDocumentCode(obj) {
        if (!$(obj).is('[disabled=disabled]')) {
            ajaxProgressLoader('show', '.popup-box');
            var url = $(obj).attr('data-url');
            var cid = $('#imsdocument-companyidref').val();
            var processCode = $('#imsdocument-processcode').val();
            var type = $('#imsdocument-type').val();
//            var pid = $('#imsdocument-parentid').val();
            $.ajax({
                url: url,
                data: {
                    "cid": cid,
                    "pcid": processCode,
                    "tid": type,
//                    "pid":pid,
                }
            }).done(function (data) {
                if (data == null) {
                    $('#btnGenerateDocumentCode').attr('disabled', 'disabled');
                }
                $('#imsdocument-autocode').val(data);
            }).error(function () {
                showMsgBoxInBottomRight('Error on connecting to server. <br />Plase login again and try later!', 'error');
                ajaxProgressLoader('hide', '.popup-box');
                return false;
            });
            return false;
        }
    }
</script>

<script>
    $(document).load(function () {
        $('.hidden-xs').html('');
    });
    $(document).ready(function () {
        $('#ims-document-form').on('beforeSubmit', function () {
            $('#submit-button').prop('disabled', true);
        }).on('submit', function () {
            if (!this.checkValidity()) {
                $('#submit-button').prop('disabled', false);
            }
        });
        $("#imsdocument-maindocumentid").change(function () {
            let mainDoc = $(this).val();
            if (mainDoc > 0) {
                $("#imsdocument-clause").prop('disabled', true).trigger('change');
            } else {
                $("#imsdocument-clause").prop('disabled', false).trigger('change');
            }
        });

        if ($("#imsdocument-effectivedate").val() != "") {
            $('#imsdocument-effectiveHijriDate').val(GregorianToShamsi($("#imsdocument-effectivedate").val()));
        }
        $('#imsdocument-effectiveHijriDate').change(function () {
            $('#imsdocument-effectivedate').val(ShamsiToGregorian($(this).val()));
        });
        $('#imsdocument-effectivedate').change(function () {
            $('#imsdocument-effectiveHijriDate').val(GregorianToShamsi($(this).val()));
        });

        if ($("#imsdocument-preparationdate").val() != "") {
            $('#imsdocument-preparationHijriDate').val(GregorianToShamsi($("#imsdocument-preparationdate").val()));
        }
        $('#imsdocument-preparationHijriDate').change(function () {
            $('#imsdocument-preparationdate').val(ShamsiToGregorian($(this).val()));
        });
        $('#imsdocument-preparationdate').change(function () {
            $('#imsdocument-preparationHijriDate').val(GregorianToShamsi($(this).val()));
        });

        if ($("#imsdocument-revisiondate").val() != "") {
            $('#imsdocument-revisionHijriDate').val(GregorianToShamsi($("#imsdocument-revisiondate").val()));
        }
        $('#imsdocument-revisionHijriDate').change(function () {
            $('#imsdocument-revisiondate').val(ShamsiToGregorian($(this).val()));
        });
        $('#imsdocument-revisiondate').change(function () {
            $('#imsdocument-revisionHijriDate').val(GregorianToShamsi($(this).val()));
        });

        $('.datepicker-fa').each(function () {
            $(this).datepicker({dateFormat: 'yy/mm/dd'});
        });
    });

    function apiPreparatorUnitContent(companyId) {
//            $('#imsdocument-preparatorunit').html(null);
//            if(companyId>0) {
//                var url = '<?= Yii::$app->urlManager->createAbsoluteUrl(['obs/obs/all-child']) ?>' + "&companyId=" + companyId;
//                $.ajax({
//                    url: url
//                }).done(function(data) {
//                    $('#imsdocument-preparatorunit').html(data);
//                });
//            }
    }
</script>

