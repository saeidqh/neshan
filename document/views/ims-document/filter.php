<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use app\assets\DatepickerAsset;

DatepickerAsset::register($this);
?>
<div class="row" style="margin-bottom: 3px;">
    <?php
        $form = ActiveForm::begin([
                    'id' => 'form',
                    'method' => 'get',
                    'options' => [
                        'data-pjax' => true,
                    ]
        ]);
    ?>
    
    <div class="col-md-12" style="margin-bottom: -15px;">
        <div class="col-md-4" style="padding-left: 5px;padding-right: 0px;">
            <?= $form->field($filterModel, 'imstitle')->textInput(["list" => "suggestionTitle", "autoComplete" => "off"])
            ->label(Yii::t('document', 'Title')) ?>
        </div>
        <div class="col-md-4" style="padding-left: 5px;padding-right: 0px;">
            <?= $form->field($filterModel, 'imscode')->textInput(["list" => "suggestionCode", "autoComplete" => "off"])
        ->label(Yii::t('document', 'Doc Code')); ?>
        </div>
        <div class="col-md-2" style="padding-left: 5px;padding-right: 0px;">
        <?= $form->field($filterModel, 'revisionNumber')->textInput()->label(Yii::t('document', 'Revision Number')); ?>
        </div>
        <div class="col-md-2" style="padding-left: 5px;padding-right: 0px;">
            <?=
            $form->field($filterModel, 'processCode')->widget(kartik\select2\Select2::className(), [
                'data' => $processCodeArray,
                'options' => [
                    'placeholder' => '--processCode--',
                    'multiple' => TRUE,
                ],
            ])->label(Yii::t('document', 'Process Code'));
            ?>
        </div>
    </div>

    
    <div class="col-md-12" style="margin-bottom: -15px;">
        <div class="col-md-6" style="margin-left: -10px;">
            <?php
            echo $form->field($filterModel, 'relatedIMSDocument')->widget(kartik\widgets\Select2::className(), [
                'data' => $relatedIMSArray,
                'id' => "myRelated",
                'hideSearch' => FALSE,
                'options' => [
                    'placeholder' => '--Select IMS--',
                    'multiple' => TRUE,
                ],
            ]);
            ?>
        </div>
        
        
        <div class="col-md-6" >
            <?php
            echo
            $form->field($filterModel, 'relatedOBS')->widget(kartik\widgets\Select2::className(), [
                'data' => $obsArray,
                'options' => [
                    'placeholder' => '--OBS Related--',
                    'multiple' => TRUE,
                ],
            ]);
            ?>
        </div>
        
    </div>
    <div class="col-md-12" style="margin-bottom: -15px;">
        <div class="col-md-6" style="margin-left: -10px;">
            <?php
            echo
            $form->field($filterModel, 'keywordIdList')->widget(kartik\widgets\Select2::className(), [
                'data' => $keywordArray,
                'options' => [
                    'placeholder' => '--Keyword list--',
                    'multiple' => TRUE,
                ],
            ]);
            ?>
        </div>
        
        
        <div class="col-md-6" >
            <?php echo $form->field($filterModel, 'preparatorUnitList')->widget(kartik\widgets\Select2::className(), [
                'data' => $obsArray,
                'options' => [
                    'placeholder' => '--preparatorUnit--',
                    'multiple' => TRUE,
                ],
                ])->label(Yii::t('document', 'Preparator Unit')); ?>
        </div>
    </div>
        

    <div class="col-md-12" style="margin-bottom: -15px;margin-top: 5px;">
<!--        <div class="col-md-4" style="padding-left: 5px;padding-right: 0px;">
            <?php 
//            echo $form->field($filterModel, 'preparatorUnitList')->widget(kartik\widgets\Select2::className(), [
//                'data' => $obsArray,
//                'options' => [
//                    'placeholder' => '--preparatorUnit--',
//                    'multiple' => TRUE,
//                ],
//                ])->label(Yii::t('document', 'Preparator Unit')); 
            ?>
        </div>-->
        <div class="col-md-6" style="padding-left: 5px;padding-right: 0px;">
            <?= $form->field($filterModel, 'companyIdList')->widget(kartik\select2\Select2::className(), [
                'data' => $companyArray,
                'options' => [
                    'placeholder' => '--Company--',
                    'multiple' => TRUE,
                ],
            ])->label(Yii::t('document', 'Company')); ?>
        </div>
        <div class="col-md-4" style="padding-left: 5px;padding-right: 0px;">
            <?=
            $form->field($filterModel, 'typeList')->widget(kartik\select2\Select2::className(), [
                'data' => $filterModel->typeArray(),
                'options' => [
                    'placeholder' => '--type--',
                    'multiple' => TRUE,
                ],
            ])->label(Yii::t('document', 'IMS Type'));
            ?>
        </div>
        <div class="col-md-2" style="padding-left: 5px;padding-right: 0px;padding-top: 22px;">
            <?=
        Html::submitButton(Yii::t('app', '{icon} Filter', ['icon' => '<i class="im-filter2"></i>']), [
            'class' => 'btn btn-sm btn-success',
        ]);
        ?>
        <?=
        Html::a(Yii::t('app', '{icon} Clear', ['icon' => '<i class="en-cycle"></i>']), '#', [
            'class' => 'btn btn-sm btn-warning',
            'onclick' => 'reset();'
        ]);
        ?>
        </div>
    </div>
    
    

    <datalist id="suggestionTitle">
        <?php
        foreach ($titleArray as $value) {
            echo '<option value="' . $value['title'] . '">';
        }
        ?>
    </datalist>

    <datalist id="suggestionCode">
        <?php
        foreach ($codeListArray as $value) {
            echo '<option value="' . $value['code'] . '">';
        }
        ?>
    </datalist>

<?php ActiveForm::end(); ?>
</div>

<script>
    function reset(){
        $('#imsdocumentsearch-typelist').val('').trigger('change');
        $('#imsdocumentsearch-imstitle').val('').trigger('change');
        $('#imsdocumentsearch-imscode').val('').trigger('change');
        $('#imsdocumentsearch-revisionNumber').val('').trigger('change');
        $('#imsdocumentsearch-processCode').val('').trigger('change');
        $('#imsdocumentsearch-relatedIMSDocument').val('').trigger('change');
        $('#imsdocumentsearch-relatedOBS').val('').trigger('change');
        $('#imsdocumentsearch-preparatorUnitList').val('').trigger('change');
        $('#imsdocumentsearch-companyIdList').val('').trigger('change');
        $('#imsdocumentsearch-typeList').val('').trigger('change');
    }
</script>

    <?php

$jsready = "
        $('#box-shadow').click(function(){
            $('#box-shadow').css('display','none');
            $('#boxfilter').fadeOut(500);
        });

        $('.select-on-check-all').trigger('click');
    ";
$this->registerJs($jsready, \yii\web\View::POS_READY);
$css = "
    .select2-results__options{
        font-size:10px;
        padding: 0px;
        line-height: 1.0;
    }
    ";
$this->registerCss($css);
