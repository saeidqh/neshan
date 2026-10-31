<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use kartik\widgets\Select2;
use kartik\grid\GridView;
?>

<div class="row">
    <div class="col-lg-6 col-md-6">

        <div id="grid-view" style="height: 400px;overflow: scroll;overflow-x: hidden">
        <!-- ________________ -->
        <div class="row">
            <ul class="pull-right pull-right" id="menu" style="margin-right: 20px;">
                    <li style="display:inline;">
                        <?php
                            echo Html::a('<i class="fa-plus"></i>&nbsp' . Yii::t('document', 'New'), '#', [
                                'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/keyword-create']),
                                'onclick' => 'showImsDocumentKeywordEdit(this)',
                                'class' => 'btn btn-sm btn-success'
                            ]);
                        ?>
                    </li>
                </ul> 
            
        </div>

            <?=
            GridView::widget([
                'id' => 'ims-document-keyword-list-main-grid',
                'pjax' => false,
                'dataProvider' => $imsDocumentKeywordListDataProvider,
                'filterModel' => $imsDocumentKeywordListSearchModel,
                'columns' => [
                    [
                        'class' => \kartik\grid\CheckboxColumn::className(),
                        'rowSelectedClass' => 'success',
                        'checkboxOptions' => function ($model, $key, $index, $column) {
                            $check = '';
                            if ($model->hasImsRelated == 1) {
                                $check = 'checked';
                            }
                            return ['value' => $model->id, 'checked' => $check];
                        }
                            ],
                            [
                                'attribute' => 'title',
                                'value' => 'title',
                                'options' => [
                                    'width' => '100px;'
                                ]
                            ],
                            [
                                'class' => kartik\grid\ActionColumn::className(),
                                'template' => '{edit}',
                                'options' => [
                                    'width' => '30px',
                                ],
                                'buttons' => [
                                    'edit' => function($key, $model, $index) {
                                        return Html::a('&nbsp;<i class="fa-pencil s16"></i>', '#', [
                                                    'data-pjax' => '0',
                                                    'data-toggle' => 'tooltip',
                                                    'data-placement' => 'top',
                                                    'data-original-title' => 'Edit IMS Document item',
                                                    'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/keyword-update', 'kwid' => $model->id]),
                                                    'onclick' => 'showImsDocumentKeywordEdit(this)'
                                                        ]
                                        );
                                    },
                                        ]
                                    ],
                                ]
                            ]);
                            ?>
                        </div>
                        <?=
                        Html::submitButton('<i class="im-arrow-right"></i> '.Yii::t('document', 'add select item'), [
                            'class' => 'btn btn-sm btn-success pull-right',
                            'onclick' => 'addItemIms(this)',
                            'data-url' => Yii::$app->urlManager->createAbsoluteUrl(["document/ims-document/create-keyword", 'imsId' => $imsDocumentModel->id]),
                        ]);
                        ?>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div style="height: 400px;overflow: scroll;overflow-x: hidden">
                            <?=
                            GridView::widget([
                                'id' => 'ims-document-keyword-list-grid',
                                'pjax' => false,
                                'dataProvider' => $imsDocumentKeywordRelatedDataProvider,
                                'filterModel' => $imsDocumentKeywordRelatedSearchModel,
                                'columns' => [
                                    [
                                        'class' => \kartik\grid\CheckboxColumn::className(),
                                    ],
                                    [
                                        'attribute' => 'title',
                                        'options' => [
                                            'width' => '100px;'
                                        ]
                                    ],
                                ]
                            ])
                            ?>
                        </div>
                        <?=
                        Html::submitButton('<i class="im-arrow-left"></i> '.Yii::t('document', 'remove select item'), [
                            'class' => 'btn btn-sm btn-danger pull-left',
                            'onclick' => 'deleteItemIms(this)',
                            'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/delete-keyword', 'imsId' => $imsDocumentModel->id]),
                        ]);
                        ?>
                    </div>
                </div>
                <?php
                $css = "
    .pagination{
        margin: 0px;
    }
    ";
                $this->registerCss($css);
                $js = "
    function addItemIms(obj)
    {
        ajaxProgressLoader('show', '#ims-document-keyword-form-pjax');
        var keys = $('#ims-document-keyword-list-main-grid').yiiGridView('getSelectedRows');
        var url=$(obj).attr('data-url');

        $.ajax({
            url: url,
            type:'POST',
            data:{
                addIMS:keys
            }
        })
        .done(function(response){
        
            ajaxProgressLoader('hide', '#ims-document-keyword-form-pjax');
            resp = JSON.parse(response);
            if(resp.result){
                $.pjax.reload('#ims-document-keyword-form-pjax', {
                    url:'" . Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/keyword-list', "id" => $imsDocumentModel->id]) . "',
                    push: false,
                    replace: false,
                    timeout: false,  
                });
                
            }
        })
        .error(function(res){
            if(!res.respopnseText){
            showMsgBoxInBottomRight('data not saved', 'warning');
            ajaxProgressLoader('hide', '#ims-document-keyword-form-pjax');
            }
        });
        return false;
    }
    
    function deleteItemIms(obj)
    {
        ajaxProgressLoader('show', '#ims-document-keyword-form-pjax');
        var keys = $('#ims-document-keyword-list-grid').yiiGridView('getSelectedRows');
        var url=$(obj).attr('data-url');
        $.ajax({
            url: url,
            type:'POST',
            data:{
                deleteIMS:keys
            }
        })
        .done(function(response){
            $.pjax.reload('#ims-document-keyword-form-pjax', {
                url:'" . Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/keyword-list', "id" => $imsDocumentModel->id]) . "',
                push: false,
                replace: false,
                timeout: false,
            });
            ajaxProgressLoader('hide', '#ims-document-keyword-form-pjax');
        });
        return false;
    }
    
        function messageImsDocumentCallbackPjax(data) {
            if (data.result)
            {
                $('#ims-document-edit-keyword-form-modal').modal('hide');
                var dt = data.data;
                swal({
                    title: 'Success',
                    type: 'success',
                    text: data.message,
                    timer: 1000,
                    showConfirmButton: false,
                });

                $.pjax.reload('#ims-document-keyword-form-pjax', {
                    url:'" . Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/keyword-list', "id" => $imsDocumentModel->id]) . "',
                    push: false,
                    replace: false,
                    timeout: false,
                });

            } else
            {
                swal('Error', data.message, 'error');
            }
        }
    ";

                $this->registerJs($js, \yii\web\View::POS_READY);
                ?>
<style>
    table thead {
        background-color: #56d6d6;
    }

</style>
