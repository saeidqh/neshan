<?php

use yii\helpers\Html;
use app\assets\JdateTimeAsset;
use app\assets\DatepickerAsset;

JdateTimeAsset::register($this);
DatepickerAsset::register($this);
$this->title = "IMS Document";

$isCanCreateDocument = \app\access\AccessControl::can('document/ims-document/create');
$isCanDownloadNativeDocument = \app\access\AccessControl::can('document/ims-document/native-download');
$isCanDeleteDocument = \app\access\AccessControl::can('document/ims-document/delete');
?>
<div class="row">
    <div class='col-md-12 col-lg-12'>

        <?=
        kartik\grid\GridView::widget([
            'id' => 'ims-document-expire-grid',
            'options' => [
                'style' => 'font-family:tahoma; font-size:12px;',
            ],
            'tableOptions' => ['class' => 'table table-striped table-hover non-responsive table table-bordered'],
            'rowOptions' => function ($model, $key, $index, $grid) {
                $class = $index % 2 ? 'odd' : 'even';
                return array('key' => $key, 'index' => $index, 'class' => $class);
            },
            'dataProvider' => $imsDocumentDataProvider,
//            'filterModel' => $imsDocumentSearchModel,
            'hover' => true,
            'pjax' => true,
            'pjaxSettings' => [
                'options' => [
                    'id' => 'ims-document-expire-grid-pjax',
                ],
            ],
            'columns' => [
                [
                    'attribute' => 'id',
                    'value' => 'id',
                    'options' => [
                        'width' => '5.23%'
                    ],
                ],
//                [
//                    'attribute' => 'referenceCompany',
//                    'visible' => $master,
//                    'options' => [
//                        'width' => '60px'
//                    ],
//                ],
                [
                    'attribute' => 'companyTitle',
                    'value' => 'companytitle',
                ],
                [
                    'attribute' => 'processCode',
                    'value' => 'imsprocessCode',
                ],
                [
                    'attribute' => 'mainDocumentID',
                    'label' => 'اصلی/وابسته',
                    'value' => function ($model) {
                        return (isset($model->mainDocumentID)) ? 'وابسته' : 'اصلی';
                    },
                    'options' => [
                        'width' => '80px'
                    ],
                ],
                [
                    'attribute' => 'imstitle',
                    'value' => 'imstitle',
                ],
                [
                    'attribute' => 'imsType',
//                    'visible' => ($type == 'All' || $type == 'همه' ) ? TRUE : FALSE,
                    'filter' => Html::activeDropDownList($imsDocumentSearchModel, 'imsType', $typeArray, ['class' => 'form-control', 'prompt' => 'Select Type']),
                ],
                [
                    'attribute' => 'autoCode',
                ],
                [
                    'attribute' => 'effectiveDate',
                    'value' => function ($model) {
                        return (isset($model->effectiveDate)) ? \app\components\Utility::convertDate($model->effectiveDate, true) : '';
                    },
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'filter' => Html::activeDropDownList($imsDocumentSearchModel, 'status', $statusArray, ['class' => 'form-control', 'prompt' => 'Select Type']), /* 'multiple'=>'true' */
                    'value' => function ($model)use ($statusArray) {
                        $class = ($model->status == 0) ? 'badge-danger' : (($model->status == 1) ? 'badge-success' : 'badge-purple');
                        return '<span class="badge ' . $class . '">' . $statusArray[$model->status] . '</span>';
                    },
                ],
                [
                    'attribute' => 'reportfile',
                    'format' => 'raw',
                    'filter' => false,
                    'value' => function ($model) {
                        return Html::a('&nbsp;<i class="fa-circle-arrow-down s16" style="color: #c32bc5;"></i>', Yii::$app->urlManager->createAbsoluteUrl(["document/ims-document/reprot-download", 'id' => $model->id]), [
                            'data-toggle' => 'tooltip',
                            'data-placement' => 'top',
                            'target' => '_blank',
                            'data-original-title' => 'Download Report Document',
                            'data-pjax' => 0
                        ]);
                    },
                ],
                [
                    'attribute' => 'nativefile',
                    'format' => 'raw',
                    'filter' => false,
                    'value' => function ($model) use($isCanDownloadNativeDocument) {

                        if (isset($model->nativefile) && $isCanDownloadNativeDocument) {
                            return Html::a('&nbsp;<i class="fa-circle-arrow-down s16" style="color: #2c5de8;"></i>', Yii::$app->urlManager->createAbsoluteUrl(["document/ims-document/native-download", 'id' => $model->id]), [
                                        'data-toggle' => 'tooltip',
                                        'data-placement' => 'top',
                                        'target' => '_blank',
                                        'data-original-title' => 'Download Native Document(Access Id: 319)',
                                        'data-pjax' => 0
                            ]);
                        }
                    },
                ],
                [
                    'class' => kartik\grid\ActionColumn::className(),
                    'template' => '{view}{edit}{delete}{private}{revision}',
                    'options' => [
                        'width' => '30px',
                    ],
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a('&nbsp;<i class="glyphicon glyphicon-eye-open s16"></i>', "#", [
                                'data-pjax' => '0',
                                'title' => 'View IMS Document Detail',
                                'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/detail', 'id' => $model->id]),
                                'onclick' => "showImsDocumentDetail(this)",
                            ]);
                        },
                        'edit' => function ($key, $model, $index)use ($isCanCreateDocument, $master, $companyAllChildsArray) {
                            if ($isCanCreateDocument && !$master && (in_array($model->companyIdRef, $companyAllChildsArray) || \app\components\Security::isCandoAdmin())) {
                                return Html::a('&nbsp;<i class="fa-pencil s16"></i>', '#', [
                                            'data-pjax' => '0',
                                            'data-toggle' => 'tooltip',
                                            'data-placement' => 'top',
                                            'title' => 'ویرایش',
                                            'class' => 'btn btn-xs',
                                            'data-original-title' => 'Edit IMS Document',
                                            'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/update', 'id' => $model->id]),
                                            'onclick' => 'showImsDocumentModal(this)'
                                                ]
                                );
                            }
                        },
                        'delete' => function ($url, $model, $key)use ($isCanDeleteDocument, $master, $companyAllChildsArray) {
                            if ($isCanDeleteDocument && !$master && (in_array($model->companyIdRef, $companyAllChildsArray) || \app\components\Security::isCandoAdmin())) {
                                return Html::a('&nbsp;<i class="br-trashcan s16"></i>', '#', [
                                            'data-pjax' => '0',
                                            'title' => 'حذف',
                                            'class' => 'btn btn-xs delete-ims-document-' . $model->id,
                                            'onclick' => 'confirmBox("Are you sure to delete this item?!", "Delete!", "deleteIMSDocument(' . $model->id . ')");',
                                            'data-url' => Yii::$app->urlManager->createAbsoluteUrl(["document/ims-document/delete", 'id' => $model->id]),
                                ]);
                            }
                        },
                        'private' => function ($key, $model, $index) use ($isCanCreateDocument, $master, $companyAllChildsArray) {
                            if ($isCanCreateDocument && !$master && (in_array($model->companyIdRef, $companyAllChildsArray) || \app\components\Security::isCandoAdmin())) {
                                $iconClass = $model->private == 1 ? 'im-checkbox-unchecked' : 'im-checkbox-checked';
                                return kartik\helpers\Html::a('<i class="' . $iconClass . ' s15"></i>', '#', [
                                            'data-pjax' => '0',
                                            'title' => 'مشاهده توسط سایر رده ها',
                                            'data-toggle' => 'tooltip',
                                            'data-placement' => 'top',
                                            'onclick' => 'confirmBox("Are you sure to change Private status?!", "Yes!", "changePrivated(' . $model->id . ')");',
                                ]);
                            }
                        },
                        'revision' => function ($key, $model, $index)use ($isCanCreateDocument, $master, $companyAllChildsArray) {
                            if ($isCanCreateDocument && !$master && ($model->status == 1) && ((in_array($model->companyIdRef, $companyAllChildsArray) || \app\components\Security::isCandoAdmin()))) {
                                return Html::a('&nbsp;<i class="ec-refresh s16"></i>', '#', [
                                            'data-pjax' => '0',
                                            'data-toggle' => 'tooltip',
                                            'data-placement' => 'top',
                                            'title' => 'بازنگری',
                                            'class' => 'btn btn-xs',
                                            'data-original-title' => 'Edit IMS Document',
                                            'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/create', 'id' => $model->id]),
                                            'onclick' => 'showImsDocumentModal(this)'
                                                ]
                                );
                            }
                        },
                    ]
                ],
            ],
        ]);
        ?>
    </div>

</div>
