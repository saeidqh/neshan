<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use app\assets\DatepickerAsset;
DatepickerAsset::register($this);
?>
<div class="row">
<?php $form = ActiveForm::begin([
    'id' => 'form',
    'method' => 'get',
    'options' => [
        'data-pjax' => true,
    ]
]);?>
    <div class="col-lg-12 col-md-12">
    <?= $form->field($filterModel, "companyIds")->widget(kartik\widgets\Select2::className(), [
                'data' => $companyItems,
                'options' => [
                    'prompt' => Yii::t('app', '-- select item --'),
                    'multiple' => true,
                ],
                'size' => kartik\widgets\Select2::SMALL,
                'pluginOptions' => [
                    'allowClear' => true,
                ],
                'size' => 'sm',
                'pluginEvents' => [
                    'select2:select' => 'function(){
                        showLastUpdate();
                        }'
                ]
            ]);
?>
    </div>
    <div class="col-lg-6 col-md-6">
        <?php if(isset($subcontractorItems)){ 
            $subcontractorItems[0] = 'All';?>
            <?= $form->field($filterModel, "subcontractorIds")->widget(kartik\widgets\Select2::className(), [
                'data' => $subcontractorItems,
                'size' => kartik\widgets\Select2::SMALL,
                'options' => [
                    'prompt' => Yii::t('app', '-- select item --'),
                    'multiple' => true,
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
                'size' => 'sm'
            ]);
//        ->label('Subcontractor', ['data-toggle' => 'tooltip', 'data-original-title' => 'انتخاب پیمانکار']);?>
        <?php } else if(isset($subProjectItems)){
            $subProjectItems[0] = 'All';?>
            <?= $form->field($filterModel, "subProjectIds")->widget(kartik\widgets\Select2::className(), [
                'data' => $subProjectItems,
                'size' => kartik\widgets\Select2::SMALL,
                'options' => [
                    'prompt' => Yii::t('app', '-- select item --'),
                    'multiple' => true,
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
                'size' => 'sm'
            ]);
//        ->label('Sub project', ['data-toggle' => 'tooltip', 'data-original-title' => 'انتخاب پروژه']);?>
        <?php } ?>
    </div>
    <div class="col-lg-3 col-md-3">
        <?= $form->field($filterModel, "fromDate")->textInput(['placeholder' => 'Developing',  'class' => 'form-control datepicker-fa']); ?>
    </div>
    <div class="col-lg-3 col-md-3">
        <?= $form->field($filterModel, "toDate")->textInput(['placeholder' => 'Developing', 'class' => 'form-control datepicker-fa']); ?>
    </div>
    <div class="col-lg-3 col-md-3">
    <?= $form->field($filterModel, "cerditOrDebtType")->widget(kartik\widgets\Select2::className(), [
                'data' => [
                    'all' =>'همه موارد',
                    '-1'=>'طلبکاران',
                    '+1' => 'بدهکاران' ,
                    '-0-'=> '(صفر)بدون مانده'
                ],
                'size' => kartik\widgets\Select2::SMALL,
                'options' => [
                    'prompt' => '-- select item --',

                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
                'size' => 'sm'
            ]);
//        ->label('Creditor or Debtor', ['data-toggle' => 'tooltip', 'data-original-title' => 'انتخاب بدهکار یا طلبکار']);?>
    </div>
    <div class="col-lg-3 col-md-3">
        <?php $currencyItems[0]="All";?>
    <?= $form->field($filterModel, "currencyIds")->widget(kartik\widgets\Select2::className(), [
                'data' => $currencyItems,
                'options' => [
                    'prompt' => Yii::t('app', '-- select item --'),
                    'multiple' => true,
                ],
                'size' => kartik\widgets\Select2::SMALL,
                'pluginOptions' => [
                    'allowClear' => true,
                ],
                'size' => 'sm'
            ]);
//        ->label('Currency', ['data-toggle' => 'tooltip', 'data-original-title' => 'انتخاب ارز']);?>
    </div>
    <div class="col-lg-3 col-md-3">
    <?= $form->field($filterModel, "paymentType")->widget(kartik\widgets\Select2::className(), [
                'data' => [
                    '2,3and4' => 'محاسبه بر اساس ارز پرداختی',
                    '2,3,4' => 'محاسبه بر اساس ارز قرارداد',
                    'all' => 'هر دو مورد'
                ],
                'size' => kartik\widgets\Select2::SMALL,
                'options' => [
                    'prompt' => Yii::t('app', '-- select item --'),

                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
                'size' => 'sm'
                
            ]);
//        ->label('Payment Type', ['data-toggle' => 'tooltip', 'data-original-title' => 'نوع پرداخت ها']);?>
    </div>
    <div class="col-lg-3 col-md-3" style="direction: rtl;">
    <?= $form->field($filterModel, "zone")->widget(kartik\widgets\Select2::className(), [
                'data' => [
                    'external' =>'External Contractor',
                    'internal'=>'Internal SubContractor',
                    'all' => 'All'
                ],
                'size' => kartik\widgets\Select2::SMALL,
                'options' => [
                    'prompt' => Yii::t('app', '-- select item --'),

                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
                'size' => 'sm'
            ]);
//        ->label('Zone', ['data-toggle' => 'tooltip', 'data-original-title' => 'انتخاب قرارداد داخلی یا خارجی']);?>
    </div>
    <div class="col-lg-12 col-md-12">
    <?= Html::submitButton(Yii::t('app', '{icon} Filter', ['icon' => '<i class="im-filter2"></i>']), [
        'class' => 'btn btn-sm btn-success',
    ]);?>
    <?= Html::a(Yii::t('app', '{icon} Clear', ['icon' => '<i class="en-cycle"></i>']), '#',[
        'class' => 'btn btn-sm btn-warning',
        'onclick' => 'reset();'
    ]);?>
    </div>
 <?php ActiveForm::end();?>
</div>
<?php
$js = "
    function reset(){
        $('#paymentreportcontractfilter-companyids').val('').trigger('change');
        
        $('#paymentreportcontractfilter-subcontractorids').val('').trigger('change');

        $('#paymentreportcontractfilter-cerditordebttype').val('').trigger('change');

        $('#paymentreportcontractfilter-currencyids').val('').trigger('change');

        $('#paymentreportcontractfilter-paymenttype').val('').trigger('change');
        
        $('#paymentreportcontractfilter-zone').val('').trigger('change');
    }
    
    
    
    ";
$this->registerJs($js, \yii\web\View::POS_END);
$jsready = "
    function showLastUpdate(){
        $('.form-group.field-paymentreportcontractfilter-companyids li.select2-selection__choice').mouseover(function(e){
            console.log(e.currentTarget.title);
            $.ajax({
                url:'".Yii::$app->urlManager->createAbsoluteUrl(['reportCash/report/last-update-date'])."&key='+(e.currentTarget.title.toLowerCase())
            })
            .done(function(data){
                showMsgBoxInBottomRight(data.date,'info','last update');
            });
        });
    }
    showLastUpdate();
    
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