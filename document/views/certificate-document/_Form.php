<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\assets\JdateTimeAsset;
use app\assets\DatepickerAsset;

JdateTimeAsset::register($this);
DatepickerAsset::register($this);
$jDate = new \app\components\jDateTime(true, true, 'Asia/Tehran');

$standardArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\Standard::find()->where(['isActive' => 1])->orderBy('sort')->all(), 'id', 'title');
?>
    <style>
        .display-none{
            display:none;
        }
    </style>

<?php
$form = ActiveForm::begin(['id' => 'responsibility-form',
    'enableClientValidation' => true,
    'options' => [
        'id' => 'responsibility-grid-pjax',
        'data-pjax' => true,
        'enctype' => 'multipart/form-data'
    ]]);
?>


    <!-- <div class="row" style="direction: rtl">
        <div class="col-md-6">
            <?=
            $form->field($certificateModel, 'certificateParentType')->widget(kartik\widgets\Select2::className(), [
                'data' => null,
                'id' => 'tender-tendercityidref',
                'options' => [
                    'placeholder' => Yii::t('document', 'Type'),
                    'disabled'=> true,
                    'readonly' => true
                ],
            ])->label(Yii::t('document', 'type'));
            ?>
        </div>

        <div class=" col-md-6">
            <div class="overlay" id="city-overlay">
                <div class="cv-spinner">
                    <span class="spinner"></span>
                </div>
            </div>
            <?=
            $form->field($certificateModel, 'certificateTypeIdRef')->widget(kartik\widgets\Select2::className(), [
                'data' => $certificateType,
                'id' => 'state-select',
                'options' => [
                    'disabled'=> true,
                    'readonly' => true,
                    'placeholder' => Yii::t('document', 'Certificate Type'),
                    'onchange' => '
                                $(document).ajaxSend(function (){
                                $("#certificatedocument-certificateparenttype").html("");
                                $("#city-overlay").fadeIn(1);
                                });
                                if($(this).val() == 4 || $(this).val() == 5 || $(this).val() == 6 || $(this).val() == 7){
                                $.get("' . Yii::$app->urlManager->createUrl(['document/certificate-document/type-child-list']) . '&lid="+$(this).val(), function(data) {
                                    $("#certificatedocument-certificateparenttype").html(data);
                                    $("#certificatedocument-certificateparenttype").prop("disabled",false);
                                    }).done(function () {
                                    $("#city-overlay").fadeOut(1);
                                });}else{
                                $("#certificatedocument-certificateparenttype").html("");
                                $("#certificatedocument-certificateparenttype").prop("disabled",true);
                                };
                                     ',
                ],
            ])->label(Yii::t('document', 'Certificate Type'));
            ?>
        </div>
    </div> -->

    <div class="row"  style="direction: rtl">
    <div class="col-md-12" >
        <?=
        $form->field($certificateModel, 'standardIdRef')->widget(kartik\widgets\Select2::className(), [
            'data' => $standardArray,
            'options' => [
                'placeholder' => '--Select--',
            ],
        ]);
        ?>
    </div>
    </div>
    <div class="row"  style="direction: rtl">

        <div class=' col-md-6'>
            <?=
            $form->field($certificateModel, 'issuingOrganization')->textInput()->label(Yii::t('document', 'issuing Organization'));
            ?>
        </div>

        <div class="col-md-6" >
            <?=
            $form->field($certificateModel, 'companyIdRef')->widget(kartik\select2\Select2::className(), [
                'data' => $companyArray,
                'options' => [

                    'prompt' => '-- Select --',
                ],
            ])->label(Yii::t('document', 'company'));
            ?>
        </div>

    </div>

    <div class="row" style="direction: rtl">

        <div class="col-md-6">
            <?=
            $form->field($certificateModel, 'expirationDate')->textInput([
                "class" => "form-control datepicker-fa",
                "placeholder" => "برای مثال " . $jDate->date('Y/m/d', time(), true),

            ])->label(Yii::t('document', 'expiration Date'));
            ?>
        </div>

        <div class=" col-md-6">
            <?=
            $form->field($certificateModel, 'issueDate')->textInput([
                "class" => "form-control datepicker-fa",
                "placeholder" => "برای مثال " . $jDate->date('Y/m/d', time(), true),

            ])->label(Yii::t('document', 'issue Date'));
            ?>
        </div>

    </div>


    <div class="row" style="direction: rtl">

        <div class="col-md-6">
            <?=
            $form->field($certificateModel, 'certificateFile')
                ->widget(\kartik\widgets\FileInput::className(), [
                    'pluginOptions' => [
                        'showPreview' => false,
                        'showUpload' => false,
                        'allowedFileExtensions' => [
                             'pdf', 'rar', 'zip', 'jpg', 'jpeg', 'bmp',
                        ],
                    ],
                    'options' => ['accept' => '*']
                ])->label(Yii::t('document', 'Certificate File'));
            ?>
        </div>



        <div class="col-lg-6 col-md-6">
            <?php
            echo $form->field($certificateModel, 'activityIdList')->widget(kartik\widgets\Select2::className(), [
                'data' => $certificateActivity,
                'options' => [
                    'placeholder' => '--Select Activity--',
                    'multiple' => true,
                    'tabindex' => '120'
                ],
            ]);
            ?>
        </div>


    </div>


    <hr class="p0 mb10" style='border-color:#B5B5B5'/>
    <div class="row" >
        <div class="col-md-12 text-right" >
            <div class="form-group">
                <?= Html::submitButton($certificateModel->isNewRecord ? '<i class="fa-plus"></i> '. Yii::t('contract', 'Create') : '<i class="fa-edit"></i> Update', ['class' => $certificateModel->isNewRecord ? 'btn btn-sm btn-success' : 'btn btn-sm btn-primary']) ?>
            </div>
        </div>
    </div>



<?php ActiveForm::end(); ?>
<?php
$js = '$(".field-certificatedocument-certificatefile .file-caption-name").html("' . $certificateModel->certificateDocument . '");';
$this->registerJs($js, \yii\web\View::POS_READY);
?>
<script>


    $(document).ready(function () {

        $('.datepicker-fa').each(function () {
            $(this).datepicker({dateFormat: 'yy/mm/dd'});
        });
    });



</script>
