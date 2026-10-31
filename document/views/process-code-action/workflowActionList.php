<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;

$this->title = 'دسترسی های فرآیندی ' . \yii\helpers\Html::encode($processCodeModel->PersianTitle);

$this->params['breadcrumbs'] = ['label' => 'Process Duties'];
?>
<?=
app\components\PageHeader::widget([
    'title' => $this->title,
    'icon' => 'st-cube',
    'breadcrumbs' => $this->params['breadcrumbs'],
]);
?>
<style>
    #process-duties-list thead th,
    #process-duties-list thead td {
        background-color: #C9A8D4;
    }
    .grid-font .summary, 
    .grid-font td,
    .grid-font th{
        font-size: 11px;
    }

</style>

<?php
app\components\GridViewBox::begin([
    'cssClass' => 'user-action-box'
])
?>
<div class="row">
    <div class="col-md-6  mb5 pull-right">
        <button class="btn btn-sm btn-primary pull-right" data-url="<?= Yii::$app->urlManager->createAbsoluteUrl(['document/process-code-action/workflow-action-create', 'pcId' => $processCodeModel->id]) ?>" onclick="newProcessDuty(this)"><i class="fa-plus"></i>&nbsp;&nbsp;Add New Action</button>
    </div>
    <div class="row">
        <div class="col-lg-12 col-md-12 ">
            <div class="panel-head-green grid-font" id='process-duties-list'>
                <?php Pjax::begin(['id' => 'doclist-pjax']); ?>  
                <?=
                GridView::widget([
                    'id' => 'obs-process-action',
                    'dataProvider' => $processCodeActionDataProvider,
                    'filterModel' => $processCodeActionSearchModel,
                    'columns' => [
                        [
                            'attribute' => 'workflowActionId',
                            'value' => 'ActionIDRef',
                            'label' => 'Action ID',
                        ],
                        [
                            'attribute' => 'workflowActionTitle',
                            'value' => 'workflowAction.ActionTitle1',
                            'label' => 'Action Title',
                        ],
                        [
                            'attribute' => 'processId',
                            'label' => 'Process Id',
                            'value' => 'workflowAction.ProcessIDRef',
                        ],
                        [
                            'attribute' => 'processTitle',
                            'label' => 'Process Title',
                            'value' => 'workflowAction.process.ProcessTitle',
                        ],
                        [
                            'label' => 'Actions',
                            'format' => 'raw',
                            'value' => function ($data) {
                                $url = Yii::$app->urlManager->createAbsoluteUrl(['/document/process-code-action/workflow-action-delete','id' => $data->id]);
                                return Html::a('<span class="fa-remove s16" style="color:rgb(255, 0, 0)"></span>', $url, [
                                            'title' => Yii::t('yii', 'Delete'),
                                            'data-confirm' => Yii::t('yii', 'Are you sure to delete this item?'),
                                            'data-method' => 'post',
                                ]);
                            },
                            'options' => [
                                'width' => 50,
                            ],
                        ],
                    ],
                ]);
                ?>       
<?php Pjax::end(); ?>
            </div>

            <div class="modal fade" id="myLargeModal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
                <div class="modal-dialog modal-lg modal-dialog-center" style="margin-top: -132.5px; margin-left: -450px;">
                    <div class="modal-content" style="max-height: 265px;">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title" id="myLargeModalLabel">Process Action List</h4>
                        </div>
                        <div class="modal-body" style="max-height: 165px;">
<?php Pjax::begin(['id' => 'document-form-pjax']); ?>


<?php Pjax::end(); ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>         
    </div>    
</div>    
<?php \app\components\GridViewBox::end(); ?>

<script>

function newProcessDuty(obj) {
    ajaxProgressLoader('show', '.modal-content');
    var url = $(obj).attr('data-url');
    $.ajax({
        url: url,
        dataType: 'html'
    }).done(function(data) {
        $('#document-form-pjax').html(data);
        $('#myLargeModal').modal('show');
        ajaxProgressLoader('hide', '.modal-content');
    }).error(function() {
        showMessageBox(
                'Error on connecting to server. <br />Plase login again and try later!',
                'Error',
                'err'
                );

        return false;
    });

}
</script>