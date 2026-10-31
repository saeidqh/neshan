<?php

use yii\helpers\Html;
use kartik\grid\GridView;
?>
<div class="row">
<div class="col-lg-12 col-md-12">  
    <div class='row panel-head-brown' style='max-height:400px; overflow-y: scroll'>
        <?= GridView::widget([
            'id' => 'action-grid',
            'dataProvider' => $actionDataProvider,
            'filterModel' => $actionModel,
            'rowOptions'=> function ($model, $key, $index, $grid)use($pcId){
                    $class=$index%2?'odd':'even';
                    $myaction = app\components\ActiveModel::find('\app\modules\workflow\models\Action')->processCode($pcId)->andWhere(['ActionID' => $model->ActionID])->one();
                    if(!empty($myaction) && $myaction->ActionID == $model->ActionID)
                    {
                        $class = 'alert-danger';
                    }
                    return array('key'=>$key,'index'=>$index, 'class'=> $class);
            },
            'columns' => [
                [
                    'class' => '\kartik\grid\CheckboxColumn',
                    'checkboxOptions' => function ($model, $key, $index, $column)use($pcId) {
                        $myaction = \app\modules\workflow\models\Action::find()->processCode($pcId)
                                ->andWhere(['ActionID' => $model->ActionID])->one();
                        $check = '';
                        if(!empty($myaction) && $myaction->ActionID == $model->ActionID)
                        {
                            $check = 'checked';
                        }
                        return ['value' => $model->ActionID, 'checked' => $check,];
                    }
                ],
                'ActionID',
                'ActionTitle1',
                'ActionTitle2',
                [
                    'attribute' => 'ProcessID',
                    'label' => 'Process ID',
                    'value' => 'process.ProcessID'
                ],
                [
                    'attribute' => 'ProcessTitle',
                    'label' => 'Process Title',
                    'value' => 'process.ProcessTitle',    
                ],
               [
                    'attribute' => 'processTypeTitle',    
                    'label' => 'Process Type',
                    'value' => 'process.type.proccessTypeTitle'
                ],
                [
                    'attribute' => 'companyTitle',
                    'label' => 'Company',
                    'value' => 'process.company.PersonTitle'
                ],
            ]
        ]);
        ?>
    </div> 
    <div > 
        <?=
        Html::a('<i class="fa-plus"></i> Insert Action', '#', [
            'data-original-title' => 'Insert',
            'class' => 'btn btn-success tip insert-process pull-right mt5',
            'data-pjax' => '0',
            'data-toggle' => 'tooltip',
            'data-placement' => 'top',
            'data-original-title' => 'Insert Process Duties item',
            'onclick' => 'insertProcess();',
            'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/process-code-action/workflow-action-create', 'pcId' => $pcId]),
            'data-update-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/process-code-action/workflow-action-list', 'pcId' => $pcId])
        ]);
        ?>
    </div> 
</div>
</div>

<script>

function insertProcess() {

    var items = $('#action-grid').yiiGridView('getSelectedRows');
    var url = $('.insert-process').attr('data-url');
    var update_url = $('.insert-process').attr('data-update-url');

    $.ajax({
        type: 'POST',
        url: url,
        data: {items: items},
    }).done(function(data) {
        if (data == 1) {
            $.pjax.reload({
                container: '#doclist-pjax',
                timeout: false,
                url: update_url,
                push: false,
                replace: false,
            });
            $('#myLargeModal').modal('hide');
        }

    }).error(function() {
        showMessageBox(
                "Error on connecting to server. <br />Plase login again and try later!",
                "Error",
                "err"
                );
        ajaxProgressLoader('hide', '.popup-box');
        return false;
    });
    return false;
}
</script>
