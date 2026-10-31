<?php

use app\components\GridViewBox;
use yii\helpers\Html;
use app\assets\DatepickerAsset;
use app\assets\JdateTimeAsset;
use app\modules\tender\assets\TenderAsset;
use yii\widgets\Pjax;
use yii\bootstrap\ButtonDropdown;
use omnilight\assets\SweetAlertAsset;

const IM_USER = 'im-user';
DatepickerAsset::register($this);
SweetAlertAsset::register($this);
JdateTimeAsset::register($this);
TenderAsset::register($this);
/* Yii::$app->view->on(\yii\web\View::EVENT_END_BODY, function () {
  Yii::$app->view->registerAssetBundle(app\modules\tender\assets\InvoiceGridViewAsset::className());
  }); */
$this->title = "Standard Request Activity";

$obsId = Yii::$app->session->get('ObsPosition');
$obsModel = app\modules\OBS\models\Obsnew::find()->where(['obsId' => $obsId])->one();
$isCandoAdmin = \app\components\Security::isCandoAdmin();
$standardRequest = \app\modules\document\models\StandardRequest::find()->where(['id' => $standardRequestIdRef])->one();
$standardArray = \yii\helpers\ArrayHelper::map(\app\modules\document\models\Standard::find()->where(['isActive' => 1])->orderBy(['sort' => SORT_ASC])->asArray()->all(), 'id', 'title');
?>

<style>
    .no-caret .caret {
        display: none;
    }
</style>
<div class="row">
    <?php GridViewBox::begin(); ?>
    <div class="row">
        <?php Pjax::begin(['id' => 'standard-request-activity-grid-pjax' . $standardRequestIdRef]); ?>
        <?=
        \kartik\grid\GridView::widget([
            // 'id' => 'standard-request-activity-grid',
            'filterModel' => $standardRequestActivitySearchModel,
            'dataProvider' => $standardRequestActivityDataProvider,
            // 'options' => ['style' => 'background-color: #bcf1bfff;'],
            'rowOptions' => function ($model, $key, $index, $grid) {
                $id = $model['id'];
                $class = $index % 2 ? 'odd' : 'even';
                $style = $model->color ? 'background-color: #' . $model->color : '';
                return array('index' => $index, 'class' => $class . ' dblclick', 'style' => $style, 'id' => 'row-' . $id);
            },
            'floatHeader' => true,
            'columns' => [
                [
                    'attribute' => 'id',
                    'label' => Yii::t('automation', 'ID'),
                    'options' => [
                        'width' => '60px',
                    ],
                ],
                /*[
                    'attribute' => 'companyName',
                    'label' => Yii::t('automation', 'Company'),
                    'value' => function ($model) {
                        return $model->companyName;
                    },
                    'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'data' => $companyList,
                        'options' => ['placeholder' => 'انتخاب شرکت'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'width' => 'auto'
                        ]
                    ],
                ],*/
                [
                    'attribute' => 'year',
                    'label' => Yii::t('automation', 'Year'),
                ],
                [
                    'attribute' => 'standard',
                    'label' => Yii::t('document', 'Standard'),
                    'value' => function ($model) {
                        return '';
                        return $model->clause->standardTitle;
                    },
                    'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'data' => $standardArray,
                        'options' => ['placeholder' => Yii::t('document', 'Standard')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ]
                    ],
                ],
                [
                    'attribute' => 'clauseTitle',
                    'label' => Yii::t('document', 'Clause Name'),
                    'headerOptions' => ['style' => 'width: 100px;'],
                    // 'contentOptions' => ['style' => 'width: 40px;']
                ],
                [
                    'attribute' => 'fullClauseNo',
                    'label' => Yii::t('document', 'Full ClauseNo'),
                ],
                [
                    'attribute' => 'clauseActivityTitle',
                    'label' => Yii::t('document', 'Clause Activity Title'),
                    'headerOptions' => ['style' => 'width: 100px;'],
                    // 'contentOptions' => ['style' => 'width: 80px;']
                ],
                [
                    'attribute' => 'radehDescription',
                    'label' => Yii::t('document', 'Radeh Description'),
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Yii::$app->utility->generateTextShorterSpan($model->radehDescription, 30, 50);
                    },
                    'headerOptions' => ['style' => 'width: 100px;'],
                ],
                [
                    'attribute' => 'radehStatus',
                    'label' => Yii::t('document', 'Radeh Status'),
                    'value' => function ($model) {
                        return !empty($model->radehStatus) ? app\modules\document\models\StandardRequestActivity::getRadehStatus($model->radehStatus) : null;
                    },
                    'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'data' => app\modules\document\models\StandardRequestActivity::getRadehStatusList(),
                        'options' => ['placeholder' => Yii::t('document', 'Radeh Status')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ]
                    ],
                ],
                [
                    'attribute' => 'radehUpdateDate',
                    'label' => Yii::t('document', 'Radeh Update Date'),
                    'format' => 'raw',
                    'value' => function ($model) {
                        return '<table class="inner-table">'
                        . '<tr><td width=\'200\'>'
                        . $model->radehUpdateDate
                        . '<tr><td >'
                        . $model->radehUser
                        . '</table>';
                    },
                ],
                [
                    'attribute' => 'holdingDescription',
                    'label' => Yii::t('document', 'Holding Description'),
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Yii::$app->utility->generateTextShorterSpan($model->holdingDescription, 30, 50);
                    },
                    'headerOptions' => ['style' => 'width: 100px;'],
                ],
                [
                    'attribute' => 'holdingStatus',
                    'label' => Yii::t('document', 'Holding Status'),
                    'value' => function ($model) {
                        return !empty($model->holdingStatus) ? app\modules\document\models\StandardRequestActivity::getHoldingStatus($model->holdingStatus) : null;
                    },
                    'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'data' => app\modules\document\models\StandardRequestActivity::getHoldingStatusList(),
                        'options' => ['placeholder' => Yii::t('document', 'Holding Status')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ]
                    ],
                ],
                [
                    'attribute' => 'holdingUpdateDate',
                    'label' => Yii::t('document', 'Holding Update Date'),
                    'format' => 'raw',
                    'value' => function ($model) {
                        return '<table class="inner-table">'
                        . '<tr><td width=\'200\'>'
                        . $model->holdingUpdateDate
                        . '<tr><td >'
                        . $model->holdingUser
                        . '</table>';
                    },
                ],
                [
                    'class' => yii\grid\ActionColumn::className(),
                    'header' => Yii::t('workflow', 'Actions'),
                    'template' => '{update}{hilight}{file}{complete-method}{download}{delete-document}',
                    'options' => [
                        'width' => '100px;',
                    ],
                    'buttons' => [
                        'update' => function ($key, $model, $index) use ($isCandoAdmin, $obsModel, $standardRequest) {
                            if ($isCandoAdmin || ($model->isLock == 0 && ($obsModel->companyIdRef == 248 || $obsModel->companyIdRef == $standardRequest->companyIdRef))) {
                                return \yii\helpers\Html::a('<i class="fa-pencil s16"></i>', '#', [
                                    'class' => 'btn btn-xs ',
                                    'data-toggle' => 'tooltip',
                                    'data-placement' => 'top',
                                    'title' => 'ویرایش',
                                    'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/standard-request-activity/update', 'srId' => $model->standardRequestIdRef, 'caId' => $model->clauseActivityIdRef, 'id' => (!empty($model->id) ? $model->id : null)]),
                                    'onclick' => 'showStandardRequestActivityModal(this)'
                                ]);
                            }
                        },
                        'hilight' => function ($key, $model, $index) use ($obsModel, $isCandoAdmin, $standardRequest) {
                            if ($isCandoAdmin || ($obsModel->companyIdRef == 248 || $obsModel->companyIdRef == $standardRequest->companyIdRef)) {
                            $bColor = "'65a7f3ff'";
                            $rColor = "'eb4c4cff'";
                            $oColor = "'c862f0ff'";
                            $yColor = "'e9d66cff'";
                            return ButtonDropdown::widget([
                                    'label' => '<i class="en-brush s16"></i>',
                                    'encodeLabel' => false,
                                    'options' => [
                                        'title' => 'هایلایت',
                                        'class' => 'no-caret',
                                        'style' => 'background: none; border: none; color: #337ab7;'
                                    ],
                                    'dropdown' => [
                                        'items' => [
                                            ['label' => '<i class="fa-circle s20" style="color: #65a7f3ff;"></i>', 'encode' => false, 'url' => '#', 'linkOptions' => ['onclick' => 'changeHilighted(' . $model->standardRequestIdRef . ','  . $model->clauseActivityIdRef . ',' . $bColor. ','  . $model->id . ');']],
                                            ['label' => '<i class="fa-circle s20" style="color: #e9d66cff;"></i>', 'encode' => false, 'url' => '#', 'linkOptions' => ['onclick' => 'changeHilighted(' . $model->standardRequestIdRef . ','  . $model->clauseActivityIdRef . ',' . $yColor. ','  . $model->id . ');']],
                                            ['label' => '<i class="fa-circle s20" style="color: #eb4c4cff;"></i>', 'encode' => false, 'url' => '#', 'linkOptions' => ['onclick' => 'changeHilighted(' . $model->standardRequestIdRef . ','  . $model->clauseActivityIdRef . ',' . $rColor. ','  . $model->id . ');']],   
                                            ['label' => '<i class="fa-circle s20" style="color: #c862f0ff;"></i>', 'encode' => false, 'url' => '#', 'linkOptions' => ['onclick' => 'changeHilighted(' . $model->standardRequestIdRef . ','  . $model->clauseActivityIdRef . ',' . $oColor. ','  . $model->id . ');']],
                                            ['label' => '<i class="im-cancel-circle s20" style="color: #eb4c4cff;"></i>', 'encode' => false, 'url' => '#', 'linkOptions' => ['onclick' => 'changeHilighted(' . $model->standardRequestIdRef . ','  . $model->clauseActivityIdRef . ',' . "'remove'" . ','  . $model->id . ');']]
                                        ]
                                    ]
                            ]);
                            }
                        },
                        'file' => function ($url, $model, $key) {
                            if (!empty($model->clauseActivity->fileName)) {
                                return Html::a('&nbsp;<i class="im-attachment s16"></i>', Yii::$app->urlManager->createAbsoluteUrl(["document/clause/download-activity", 'id' => $model->clauseActivityIdRef]), [
                                        'data-toggle' => 'tooltip',
                                        'data-placement' => 'top',
                                        'target' => '_blank',
                                        'title' => 'فایل نمونه',
                                        'data-pjax' => 0,
                                ]);
                            }
                        },
                        'complete-method' => function ($url, $model, $key) {
                            if (!empty($model->clauseActivity->completeMethod)) {
                                return Html::a('&nbsp;<i class="im-question s16" style="color: blue;"></i>', '#', [
                                        'data-toggle' => 'tooltip',
                                        'data-placement' => 'top',
                                        'target' => '_blank',
                                        'title' => $model->clauseActivity->completeMethod,
                                        'data-pjax' => 0,
                                        'onclick' => 'return false'
                                ]);
                            }
                        },        
                        'download' => function ($url, $model, $key) {
                            if (!empty($model->activityDocumentIdRef)) {
                                $items = [];
                                $activityDocument = $model->activityDocument;
                                if(!empty($activityDocument->fileName)) {
                                    $items[] = ['label' => '<i class="fa-circle-arrow-down s20" style="color: #08b934ff;"></i>', 'encode' => false, 'linkOptions' => ['title' => 'فایل', 'target' => '_blank', 'data-pjax' => '0'], 'url' => Yii::$app->urlManager->createUrl(["document/standard-request-activity/download", 'id' => $model->activityDocumentIdRef])];
                                }
                                $imsDocument = $activityDocument->imsDocument;
                                if (!empty($activityDocument->imsDocumentIdRef) && !empty($imsDocument->nativefile)) {
                                    $items[] = ['label' => '<i class="fa-circle-arrow-down s20" style="color: #2c5de8;"></i>', 'encode' => false, 'linkOptions' => ['title' => ' قابل ویرایش Ims', 'target' => '_blank', 'data-pjax' => '0'], 'url' => Yii::$app->urlManager->createUrl(["document/ims-document/native-download", 'id' => $activityDocument->imsDocumentIdRef])];
                                }
                                if (!empty($activityDocument->imsDocumentIdRef) && !empty($imsDocument->reportfile)) {
                                    $items[] = ['label' => '<i class="fa-circle-arrow-down s20" style="color: #c32bc5;"></i>', 'encode' => false, 'linkOptions' => ['title' => 'غیر قابل ویرایش Ims', 'target' => '_blank', 'data-pjax' => '0'], 'url' => Yii::$app->urlManager->createUrl(["document/ims-document/reprot-download", 'id' => $activityDocument->imsDocumentIdRef])];
                                }
                                return ButtonDropdown::widget([
                                    'label' => '<i class="fa-arrow-down"></i>',
                                    'encodeLabel' => false,
                                    'options' => ['title' => 'فایل','class' => 'btn btn-primary no-caret', 'style' => 'background: none; border: none; color: #337ab7;'],
                                    'dropdown' => [
                                        'items' => $items
                                    ]
                                ]);
                            }
                        },
                        /*'delete-document' => function ($url, $model, $key) use ($isCandoAdmin, $obsModel) {
                            if (!empty($model->fileName) && ($isCandoAdmin || $obsModel->companyIdRef == 248 || $model->radehStatus != 'verify')) {
                                return Html::a('<i class="fa-trash s16"></i>', '#', [
                                            'class' => 'delete-standard-request-activity-document-' . $model->id,
                                            'data-toggle' => 'tooltip',
                                            'data-placement' => 'top',
                                            'title' => 'حذف فایل',
                                            'onclick' => 'confirmBox("Are you sure to delete this item?!", "Delete!", "deleteStandardRequestActivityDocument(' . $model->id . ')");',
                                            'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/standard-request-activity/delete-document', 'id' => $model->id])
                                ]);
                            }
                        },*/
                    ]
                ]
            ],
        ]);
        ?>
        <?php Pjax::end(); ?>
    </div>
    <?php GridViewBox::end() ?>
</div>

<script>
    var standardRequestIdRef = '<?= $standardRequestIdRef ?>';

    function changeHilighted(srId, caId, color, id) {
        console.log(srId)
        ajaxProgressLoader('show');
        var url = '<?= Yii::$app->urlManager->createAbsoluteUrl(['document/standard-request-activity/hilight']) ?>' + "&srId=" + srId + "&caId=" + caId + "&color=" + color;
        if(id > 0) {
            url += "&id=" + id;
        }
        $.ajax({
            url: url,
            dataType: "json"
        }).done(function (data) {
            if (data.result == 1) {
                swal({
                title: data.message,
                type: 'success',
                text: 'Are you want to reload data?',
                confirmButtonText: "Reload",
                showConfirmButton: true,
                showCancelButton: true,
            }, function (isConfirm) {
                if (isConfirm) {
                    location.reload();
                }
            });
            } else {
                swal('Error', data.message, 'error');
            }
        }).error(function () {
            showMsgBoxInBottomRight('Error on connecting to server. <br />Plase login again and try later!', 'error');
            return false;
        });
        ajaxProgressLoader('hide');
        return false;

    }   

    function deleteStandardRequestActivityDocument(id) {
        ajaxProgressLoader('show');
        // var id = $('.delete-standard-request-activity-document-' + id).attr('data-id');
        // var url = $('.delete-standard-request-activity-document-' + id).attr('data-url');
        var url = '<?= Yii::$app->urlManager->createAbsoluteUrl(['document/standard-request-activity/delete-document']) ?>' + "&id=" + id;
        $.ajax({
            url: url,
        }).done(function (data) {
            if (data.result == 1) {
                showMsgBoxInBottomRight(data.message, 'success');
            } else {
                showMsgBoxInBottomRight(data.message, 'danger');
            }
            return false;
        }).error(function () {
            showMessageBox(
                    "Error on connecting to server. <br />Plase login again and try later!",
                    "Error",
                    "err"
                    );
            ajaxProgressLoader('hide');
            return false;
        });
        return  false;
    }

    function reloadForm() {
        $.pjax.reload({
            container: '#standard-request-activity-grid-pjax' + standardRequestIdRef,
            timeout: false,
            push: false,
            replace: false,
        });
    }

</script>


