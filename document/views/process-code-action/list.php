<?php
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\PageHeader;
use app\components\GridViewBox;

$this->title = $this->title = 'دسترسی های  ' . \yii\helpers\Html::encode($processCodeModel->PersianTitle);
echo PageHeader::widget([
    'title' => $this->title,
    'icon' =>'fa-group',
]); 

?>
<style >
    .bor{border:solid 1px purple;}
    .bor-red{border:solid 1px red;}
    
    .tbl-row-doted td,
    .tbl-row-doted th{
        border-bottom: dotted 1px yellow;
    }
    #obs-action-grid thead th,
    #obs-action-grid thead td,
    #obs-action-grid .summary{
        background-color: #559ABB;
    }
    #obs-related-user-grid thead th,
    #obs-related-user-grid .summary
    {
        background-color:#DEBE9A;
    }
    
    .orange-box{
        padding: 10px;
        margin-bottom: 25px;
        border-radius: 4px;
        box-shadow: none;
        border:solid 1px #B77701;
        overflow: hidden;
        position: relative;
        background:rgb(255, 212, 0);
        color: black;
    }
    .green-box{
        padding: 10px;
        margin-bottom: 25px;
        border-radius: 4px;
        box-shadow: none;
        border: solid 1px #62861C;
        overflow: hidden;
        position: relative;
        background: rgb(153, 187, 85);
        color: black;
    }
    .grid-font .summary, 
    .grid-font td,
    .grid-font th{
        font-size: 11px;
    }
    .ddl-role-box *{
        direction:rtl;
    }
    
    #obs-action-grid-box .kv-grid-wrapper{
        height:460px !important; 
    }
</style>
<div class='row' >
    <div class='col-md-12 ' >
        <?php
        GridViewBox::begin([
            'cssClass' => 'border-box',
                'htmlOptions' => [
                    'id' => 'obs-actions-box'
                ],
        ]);
        ?>
            <div class='row'>
                <div class="col-md-12 pt0 pb5" style="margin-top:-10px;border-bottom: solid 1px #377988;">
                    <span><i class='im-checkmark2 s16'></i>&nbsp;<b> <?= 'Process Code '. \yii\helpers\Html::encode($processCodeModel->code) . ' Action' ?> </b></span>
                    <?= Html::a('Add Action', '#', [
                        'id' => 'btn-add-action',
                        'class' =>'btn btn-sm btn-primary pull-right ml10',
                        'onclick'=>'return showNewObsActionList(this)',
                        'data-url'=> Yii::$app->urlManager->createAbsoluteUrl('document/process-code-action/create') ,
                        'data-pcId' => $pcId,
                    ]) ?>
                    
                    <?= Html::a('Delete Action', '#', [
                        'id' => 'btn-add-action',
                        'class' =>'btn btn-sm btn-warning pull-right',
                        'onclick'=>'return deleteObsActions(this)',
                        'data-url'=> Yii::$app->urlManager->createAbsoluteUrl('document/process-code-action/delete') ,
                        'data-pcId' => $pcId,
                    ]) ?>
                </div>
            </div>
            <div class='row grid-font' id='obs-action-grid-box'>
                <?php
                echo \kartik\grid\GridView::widget([
                    'id' => 'obs-action-grid',
                    'dataProvider' => $processCodeActionDataProvider,
                    'filterModel' => $processCodeActionSearchModel,
                    'floatHeader' => true,
                    'floatOverflowContainer' => true,
                    'layout'=>"{items}\n{pager}\n{summary}",
                    'pjax' => true,
                    'pjaxSettings' => [
                        'options' => [
                            'id' => 'obs-action-grid-pjax',
                        ],
                    ],
                    'columns' => [
                        [
                            'class' => '\kartik\grid\CheckboxColumn',
                            'options' => [
                                'width' => '10px'
                            ],
                        ],
                        [
                            'attribute' => 'actionId',
                            'value' => 'action.ActionID',
                            'options' => [
                                'width' => '20px'
                            ],
                        ],
                        [
                            'attribute' => 'actionTitle',
                            'value' => 'action.ActionTitle',
                            'options' => [
                                'width' => '100px'
                            ],
                        ],
                        [
                            'attribute' => 'module',
                            'format' => 'raw',
                            'options' => ['width' => 60],
                            'filter' => Html::activeDropDownList($processCodeActionSearchModel, 'module', app\modules\account\models\FormAction ::ararryModule(), ['class' => 'form-control', 'style'=>'font-size:10px', 'prompt' => 'Select Type']),
                            'value' => function($model) {
                                if(!isset($model->action->module)){
                                    return '';
                                }else{
                                    return app\modules\account\models\FormAction ::ararryModule()[$model->action->module];
                                }
                            }
                        ],
                        [
                            'attribute' => 'description',
                            'value' => 'action.Description',
                            'options' => [
                                'width' => '180px'
                            ],
                        ],
                        [
                            'attribute' => 'SecurityLevel',
                            'filter' => Html::activeDropDownList($processCodeActionSearchModel, 'SecurityLevel', app\modules\account\models\FormAction::ararrySecurityLevel(), ['class' => 'form-control', 'style'=>'font-size:10px', 'prompt' => 'Select Type']),
                            'value' => function($model){
                                return isset($model->action->SecurityLevel)?app\modules\account\models\FormAction::ararrySecurityLevel()[$model->action->SecurityLevel]:"";
                            },
                            'options' => [
                                'width' => '20px'
                            ],
                        ],
                        [
                            'attribute' => 'actionType',
                            'value' => 'action.actionType',
                            'options' => [
                                'width' => '20px'
                            ],
                        ],
                    ],
                ]);
                ?>
            </div>
        <?php GridViewBox::end(); ?>
    </div>
</div>
<?php yii\bootstrap\Modal::begin([
        'header'=>'<h5 class="p0 m0">Action List</h5>',
        'id'=>'modal-new-action-list',
        'size'=>'modal-lg',
        'options'=>[
            'tabindex'=>false,
        ]
]) ?>

    <?php Pjax::begin(['id' => 'new-action-list-pjax']); ?>
        Loading...
    <?php Pjax::end(); ?>

<?php yii\bootstrap\Modal::end() ?>

<script >
    var pcId = <?= $pcId ?>;
    $(document).ready(function(){
        $('#new-action-list-pjax').on("pjax:complete", function(dt,res,ev) {
            ajaxProgressLoader('hide', '#modal-new-action-list .modal-body');
            ajaxProgressLoader('hide', '#obs-actions-box');
            if(ev == 'success'){
                var res = dt.currentTarget.innerHTML;
                var p =res.split('*');
                if(p[0] == 'true'){
                    showMsgBoxInBottomRight(p[1],'success');
                    var pcId = $('#btn-add-action').attr('data-pcId');
                    showObsActions(pcId);
                    $('#modal-new-action-list').modal('hide');
                }
            }
            $(document).resize();
        });
        $('#obs-pjax').on("pjax:complete", function(dt,res,ev) {
            ajaxProgressLoader('hide', '.orange-box');
            ajaxProgressLoader('hide', '#obs-actions-box');
            if(ev == 'success'){
                var res = dt.currentTarget.innerHTML+'';
                var p =res.split('*');
                if(p[0] == 'true'){
                    showMsgBoxInBottomRight(p[1],'success');
                    var pcId = $('#btn-add-action').attr('data-pcId');
                    showObsActions(pcId);
                }
            }
        });
    });
    function showNewObsActionList(obj){

        ajaxProgressLoader('show', '#modal-new-action-list .modal-body');
        $('#modal-new-action-list').modal('show');
        var url = $(obj).attr('data-url');
        var pcId = $(obj).attr('data-pcId');
        $.pjax.reload({
            container: "#new-action-list-pjax",
            url: url,
            timeout: false,
            push: false,
            replace: false,
            data:{
                pcId:pcId
            }
        });
        return false;
    }
    function showObsActions(pcId){
        $.pjax.reload({
            container: "#obs-action-grid-pjax",
            timeout: false,
            push: false,
            replace: false
        });
    }
    function deleteObsActions(obj){
        var items = $('#obs-action-grid').yiiGridView('getSelectedRows');
        
        
        if(!(items.length>0)){
            showMsgBoxInBottomRight('Please select the action(s).','warning');
            return;
        }
        ajaxProgressLoader('show', '#obs-actions-box');
        var url = $(obj).attr('data-url');
        var pcId = $(obj).attr('data-pcId');
        $.pjax.reload({
            container: "#new-action-list-pjax",
            url: url,
            timeout: false,
            push: false,
            replace: false,
            data:{
                pcId:pcId,
                actions:items
            }
        });
        return false;
    }
</script>
