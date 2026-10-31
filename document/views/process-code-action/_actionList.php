<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
?>
<style>
    .fix-grid-header .kv-thead-float{
        top:70px !important;
        left:5px !important;
    }
</style>
<div class="row grid-font" style="height:400px; overflow-y:scroll; " >

    <div class="fix-grid-header" >
        <?php
        echo \kartik\grid\GridView::widget([
            'id' => 'action-list-grid',
            'dataProvider' => $formActionDataProvider,
            'filterModel' => $formActionSearchModel,
            'pjax' => false,
            'floatHeader' => true,
            'layout' => "{items}\n{pager}\n{summary}",
            'pjaxSettings' => [
                'options' => [
                    'id' => 'action-list-grid-pjax',
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
                    'attribute' => 'module',
                    'format' => 'raw',
                    'options' => ['width' => 60],
                    'filter' => Html::activeDropDownList($formActionSearchModel, 'module', app\modules\account\models\FormAction ::ararryModule(), ['class' => 'form-control', 'style' => 'font-size:10px', 'prompt' => '']),
                    'value' => function($model) {
                        if (!isset($model->module)) {
                            return '';
                        } else {
                            return app\modules\account\models\FormAction ::ararryModule()[$model->module]; // == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Block</span>';
                        }
                    }
                ],
                [
                    'attribute' => 'ActionID',
                    'options' => [
                        'width' => '68px'
                    ],
                ],
                [
                    'attribute' => 'ActionTitle',
                ],
                [
                    'attribute' => 'Description',
                ],
                [
                    'attribute' => 'SecurityLevel',
                    'filter' => Html::activeDropDownList($formActionSearchModel, 'SecurityLevel', app\modules\account\models\FormAction::ararrySecurityLevel(), ['class' => 'form-control', 'prompt' => 'Select Type']), /* 'multiple'=>'true' */
                    'value' => function($model) {
                        return isset($model->SecurityLevel) ? app\modules\account\models\FormAction::ararrySecurityLevel()[$model->SecurityLevel] : "";
                    },
                ],
                [
                    'attribute' => 'actionType',
                ],
            ]
        ]);
        ?>
    </div>
</div>
<div class='row'>
    <div class='col-md-12' >
        <hr class='p0 m0 mb10' />
        <?=
        Html::button('Add Action(s)', [
            'class' => 'btn btn-success btn-sm pull-right',
            'onclick' => 'return addSelectedActionsToObs()',
        ])
        ?>
        <?=
        Html::button('Cancel', [
            'class' => 'btn btn-danger btn-sm pull-right',
            'style' => 'margin-right:5px;',
            'data-dismiss' => 'modal',
        ])
        ?>
    </div>

</div>
<?php
// کد نمایش پیغام های کاربر
if (Yii::$app->request->isAjax)
    echo app\components\Utility::showUserMessages(Yii::$app->session->getAllFlashes());
?>

<script>
    function addSelectedActionsToObs() {
        var items = $('#action-list-grid').yiiGridView('getSelectedRows');
        if (!(items.length > 0)) {
            showMsgBoxInBottomRight('Please select the action(s).', 'warning');
            return;
        }
        ajaxProgressLoader('show', '#modal-new-action-list .modal-body');
        var url = $('#btn-add-action').attr('data-url');
        var pcId = $('#btn-add-action').attr('data-pcId');
        $.pjax.reload({
            container: "#new-action-list-pjax",
            url: url,
            timeout: false,
            push: false,
            replace: false,
            data: {
                pcId: pcId,
                actions: items
            }
        });

    }
</script>