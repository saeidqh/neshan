<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use app\assets\JdateTimeAsset;
use app\assets\DatepickerAsset;

JdateTimeAsset::register($this);
DatepickerAsset::register($this);

use omnilight\assets\SweetAlertAsset;

SweetAlertAsset::register($this);

$this->title = $master ? "Master List" : "All";

$isCanViewDocument = \app\access\AccessControl::can('document/ims-document/list', ['master' => 0]);
$isCanCreateDocument = \app\access\AccessControl::can('document/ims-document/create');
$isCanDeleteDocument = \app\access\AccessControl::can('document/ims-document/delete');
$isCanViewDocumentType = \app\access\AccessControl::can('document/ims-document-type/list');
$isCanDownloadNativeDocument = \app\access\AccessControl::can('document/ims-document/native-download');
?>
<?=
\app\components\PageHeader::widget([
    'title' => $this->title,
    'icon' => 'im-screen',
]);
?>


<div class='row box-bg-3'>
    <div class="col-lg-12 col-md-12" >
        <!--        <div class="col-lg-7 col-md-7" >
                    <div class="btn-group" style="padding-bottom: 3px;">
                        <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/list', 'type' => 'All']) ?>" class="btn btn-default <?php echo $type == 'All' ? ' psa-btn-default-active' : '' ?>"><?php echo Yii::t('document', 'All'); ?></a>
                        <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/list', 'type' => 'Regulation']) ?>" class="btn btn-default <?php echo $type == 'Regulation' ? ' psa-btn-default-active' : '' ?>"><?php echo Yii::t('document', 'Regulation'); ?></a>
                        <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/list', 'type' => 'Instruction']) ?>" class="btn btn-default <?php echo $type == 'Instruction' ? ' psa-btn-default-active' : '' ?>"><?php echo Yii::t('document', 'Instruction'); ?></a>
                        <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/list', 'type' => 'Procedure']) ?>" class="btn btn-default <?php echo $type == 'Method statement' ? ' psa-btn-default-active' : '' ?>"><?php echo Yii::t('document', 'Procedure'); ?></a>
                        <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/list', 'type' => 'List/Table/Chart']) ?>" class="btn btn-default <?php echo $type == 'List' ? ' psa-btn-default-active' : '' ?>"><?php echo Yii::t('document', 'List/Table/Chart'); ?></a>
                        <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/list', 'type' => 'Form']) ?>" class="btn btn-default <?php echo $type == 'Form' ? ' psa-btn-default-active' : '' ?>"><?php echo Yii::t('document', 'Form'); ?></a>
                        <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/list', 'type' => 'Table']) ?>" class="btn btn-default <?php echo $type == 'Table' ? ' psa-btn-default-active' : '' ?>"> <?php echo Yii::t('document', 'Table'); ?></a>
                        <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/list', 'type' => 'Communicated']) ?>" class="btn btn-default <?php echo $type == 'Communicated' ? ' psa-btn-default-active' : '' ?>"> <?php echo Yii::t('document', 'Communicated'); ?></a>
                    </div>
                </div>-->
        <div class="col-lg-12 col-md-12" >
            <ul class="pull-right pull-right" id="menu">
                <li style="display:inline;">
                    <?php
                    if ($isCanViewDocument) {
                        echo yii\bootstrap\Html::a($master ? 'همه' : 'مستر لیست', Yii::$app->urlManager->createAbsoluteUrl(["document/ims-document/list", 'master' => !$master]), [
                            'data-toggle' => 'tooltip',
                            'data-placement' => 'left',
                            'class' => 'btn btn-sm btn-success'
                        ]);
                    }
                    ?>
                </li>
                <li style="display:inline;">
                    <?php
                    if ($isCanCreateDocument && $master) {
                        echo Html::a('Delete Doc', '#', [
                            'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/company-ims-document/delete']),
                            'onclick' => 'deleteCompanyImsDocuments(this);return false;',
                            'class' => 'btn btn-sm btn-warning',
                            'id' => 'btn-delete-doc',
                        ]);
                    }
                    ?>
                </li>
                <li style="display:inline;">
                    <?php
                    if ($isCanCreateDocument && $master) {
                        echo Html::a('Add Doc', '#', [
                            'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/company-ims-document/create']),
                            'onclick' => 'showCompanyImsDocumentList(this);return false;',
                            'class' => 'btn btn-sm btn-info',
                            'id' => 'btn-add-doc',
                        ]);
                    }
                    ?>
                </li>
                <li style="display:inline;">
                    <?=
                    yii\bootstrap\Html::a('<i class="im-file-excel"></i> Excel', '#', [
                        'target' => '_blank',
                        'export-url' => Yii::$app->urlManager->createAbsoluteUrl(["document/ims-document/export", 'master' => $master]),
                        'onClick' => 'exportExcelFile(this);',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'left',
                        'class' => 'btn btn-sm btn-danger'
                    ]);
                    ?>
                </li>
                <li style="display:inline;">
                    <?php
                    if ($isCanViewDocumentType && !$master) {
                        echo Html::a(Yii::t('document', 'Document Type List'), Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document-type/list']), [
                            'class' => 'btn btn-sm btn-info'
                        ]);
                    }
                    ?>
                </li>
                <li style="display:inline;">
                    <?php
                    if ($isCanCreateDocument && !$master) {
                        echo Html::a('<i class="fa-plus"></i>&nbsp' . Yii::t('document', 'New'), '#', [
                            'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/create']),
                            'onclick' => 'showImsDocumentModal(this)',
                            'class' => 'btn btn-sm btn-success'
                        ]);
                    }
                    ?>
                </li>
                <li style="display:inline;">
                    <?php
                    if ($isCanCreateDocument) {
                        echo Html::a('<i class="im-filter2"></i>&nbsp' . Yii::t('document', 'Advanced Filter'), '#', [
                            'onclick' => 'toggleBoxFilter(this);return false;',
                            'class' => 'btn btn-sm btn-info',
                        ]);
                    }
                    ?>
                </li>
            </ul> 
        </div>
    </div>
    <!--<div class="row">-->
    <div class="col-lg-12 col-md-12">
        <div class="panel panel-default" style="margin-bottom: 5px;display: none;" id="boxfilter">
            <div class="panel-heading" style="color: darkblue;background-color: #8ec732;">
                <h4 class="panel-title"><i class="im-filter2"></i> <?= Yii::t('document', 'Advanced Filter'); ?></h4>
            </div>
            <div class="panel-body" style="background-color: #fcffe4;padding-bottom: 1px;padding-top: 1px;padding-left: 1px;padding-right: 1px;">
                <?=
                $this->render('filter', [
                    'filterModel' => $imsDocumentSearchModel,
                    'companyArray' => $companyArray,
                    'obsArray' => $obsArray,
                    'relatedIMSArray' => $relatedIMSArray,
                    'titleArray' => $titleArray,
                    'processCodeArray' => $processCodeArray,
                    'codeListArray' => $codeListArray,
                    'keywordArray' => $keywordArray,
                ]);
                ?>
            </div>
        </div>
    </div>

    <div class='col-md-12 col-lg-12'>

        <?=
        kartik\grid\GridView::widget([
            'id' => 'ims-document-grid',
            'floatHeader' => true,
            'options' => [
                'style' => 'font-family:tahoma; font-size:12px;',
            ],
            'tableOptions' => ['class' => 'table table-striped table-hover non-responsive table table-bordered'],
            'rowOptions' => function ($model, $key, $index, $grid) {
                $class = $index % 2 ? 'odd' : 'even';
                return array('key' => $key, 'index' => $index, 'class' => $class);
            },
            'dataProvider' => $imsDocumentDataProvider,
            'filterModel' => $imsDocumentSearchModel,
            'hover' => true,
            'pjax' => true,
            'pjaxSettings' => [
                'options' => [
                    'id' => 'ims-document-grid-pjax',
                ],
            ],
            'columns' => [
                [
                    'class' => '\kartik\grid\CheckboxColumn',
                    'visible' => $master,
                    'options' => [
                        'width' => '10px'
                    ],
                ],
                [
                    'class' => \kartik\grid\ExpandRowColumn::className(),
                    'value' => function ($url, $model, $key) {
                        return \kartik\grid\GridView::ROW_COLLAPSED;
                    },
                    'detailUrl' => Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/child-expand', 'master' => $master]),
                    'headerOptions' => ['class' => 'kartik-sheet-style'],
                    'expandOneOnly' => true,
                    'expandIcon' => '<span class="fa-plus"></span>',
                    'collapseIcon' => '<span class="fa-minus"></span>',
                    'options' => ['width' => '0.65%'],
                ],
                [
                    'attribute' => 'id',
                    'value' => 'id',
                    'options' => [
                        'width' => '10px'
                    ],
                ],
                [
                    'attribute' => 'referenceCompany',
                    'visible' => $master,
                    'options' => [
                        'width' => '60px'
                    ],
                ],
                [
                    'attribute' => 'companyTitle',
                    'value' => 'companyTitle',
                ],
                [
                    'attribute' => 'processCode',
                    'value' => 'imsprocessCode',
                ],
                
                [
                    'attribute' => 'mainUnitObsIdRef',
                    'value' => function ($model) {
                        return isset($model->mainUnitObsIdRef) ? $model->mainUnit->fullTitle : '';
                    },
                ],
                [
                    'attribute' => 'mainDocumentID',
                    'label' => 'اصلی/وابسته',
                    'filter' => Html::activeDropDownList($imsDocumentSearchModel, 'mainDocumentID', [1 => 'وابسته', 0 => 'اصلی'], ['class' => 'form-control', 'prompt' => '']),
                    'value' => function ($model) {
                        return ($model->mainDocumentID == 1) ? 'وابسته' : 'اصلی';
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
                    'visible' => ($type == 'All' || $type == 'همه' ) ? TRUE : FALSE,
                    'filter' => Html::activeDropDownList($imsDocumentSearchModel, 'imsType', $typeArray, ['class' => 'form-control', 'prompt' => 'Select Type']),
                ],
                /* [
                  'attribute' => 'imscode',
                  'value' => 'imscode',
                  ], */
                [
                    'attribute' => 'autoCode',
                ],
                /* [
                  'attribute' => 'preparationDate',
                  'format' => 'raw',
                  'value' => function ($model) {
                  return (isset($model->preparationDate)) ? \app\components\Utility::convertDate($model->preparationDate, true) : '';
                  },
                  'options' => [
                  'width' => '15px'
                  ],
                  ],
                  [
                  'attribute' => 'revisionDate',
                  'format' => 'raw',
                  'value' => function ($model) {
                  return (isset($model->revisionDate)) ? \app\components\Utility::convertDate($model->revisionDate, true) : '';
                  },
                  'options' => [
                  'width' => '15px'
                  ],
                  ], */
                [
                    'attribute' => 'effectiveDate',
                    'value' => function ($model) {
                        return (isset($model->effectiveDate)) ? \app\components\Utility::convertDate($model->effectiveDate, true) : '';
                    },
                ],
                /* [
                    'attribute' => 'standard',
                ],
                [
                    'attribute' => 'clause',
                ],
                [
                  'attribute' => 'revisionNumber',
                  'value' => 'revisionNumber',
                  ],
                  [
                  'attribute' => 'preparatorUnit',
                  'value' => function ($model) {
                  return !empty($model->obs->obsTitle) ? $model->obs->obsTitle : '';
                  }
                  ], */
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
                        if (isset($model->reportfile)) {
                            return Html::a('&nbsp;<i class="fa-circle-arrow-down s16" style="color: #c32bc5;"></i>', Yii::$app->urlManager->createAbsoluteUrl(["document/ims-document/reprot-download", 'id' => $model->id]), [
                                        'data-toggle' => 'tooltip',
                                        'data-placement' => 'top',
                                        'target' => '_blank',
                                        'data-original-title' => 'Download Report Document',
                                        'data-pjax' => 0
                            ]);
                        }
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
//                    'visible' => $master ? FALSE : TRUE,
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a('&nbsp;<i class="glyphicon glyphicon-eye-open s16"></i>', "#", [
                                'data-pjax' => '0',
                                'title' => 'مشاهده',
                                'class' => 'action-button btn btn-xs',
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
                                            'class' => 'action-button btn btn-xs',
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
                                            'class' => 'action-button btn btn-xs delete-ims-document-' . $model->id,
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
                                            'class' => 'action-button',
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
                                            'class' => 'action-button btn btn-xs',
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

<?php
\yii\bootstrap\Modal::begin([
    'header' => '<h4>' . Yii::t('document', 'IMS Document') . '</h4>',
    'id' => 'ims-document-form-modal',
    'clientOptions' => [
        'backdrop' => 'static',
        'keyboard' => false
    ]
]);

yii\widgets\Pjax::begin(['id' => 'ims-document-form-pjax']);

$js = "
      $('#ims-document-form-modal .modal-content').draggable();
    ";
$this->registerJs($js);

yii\widgets\Pjax::end();
\yii\bootstrap\Modal::end();
?>
<!-- Load List-->
<?php
\yii\bootstrap\Modal::begin([
    'header' => '<h4>' . Yii::t('document', 'IMS Document') . '</h4>',
    'id' => 'ims-document-keyword-form-modal',
    'size' => \yii\bootstrap\Modal::SIZE_LARGE,
]);

yii\widgets\Pjax::begin(['id' => 'ims-document-keyword-form-pjax']);

yii\widgets\Pjax::end();
\yii\bootstrap\Modal::end();
?>
<!-- -edit-keyword-->
<?php
\yii\bootstrap\Modal::begin([
    'header' => '<h4>' . Yii::t('document', 'Edit keyword') . '</h4>',
    'id' => 'ims-document-edit-keyword-form-modal',
//                            'size' => \yii\bootstrap\Modal::SIZE_SMALL,
]);

yii\widgets\Pjax::begin(['id' => 'ims-document-edit-keyword-form-pjax']);

yii\widgets\Pjax::end();
\yii\bootstrap\Modal::end();
?>

<?php
yii\bootstrap\Modal::begin([
    'header' => '<h5 class="p0 m0">IMS Document List</h5>',
    'id' => 'company-ims-document-form-modal',
    'size' => 'modal-lg',
    'options' => [
        'tabindex' => false,
    ]
])
?>

<?php Pjax::begin(['id' => 'company-ims-document-form-pjax']); ?>
Loading...
<?php Pjax::end(); ?>

<?php yii\bootstrap\Modal::end() ?>

<?php
// کد نمایش پیغام های کاربر
if (Yii::$app->request->isAjax)
    echo app\components\Utility::showUserMessages(Yii::$app->session->getAllFlashes());
?>


<script>
    $(document).ready(function () {
        $('.datepicker-fa').each(function () {
            $(this).datepicker({dateFormat: 'yy/mm/dd'});
        });
        $(document).on('click', '.action-button', function (e) {
            e.preventDefault(); // برای جلوگیری از اسکرول
        });
    });

    function imsDocumentCallbackPjax(data)
    {
        if (data.result)
        {
            $('#ims-document-form-modal').modal('hide');
            var dt = data.data;
            swal({
                title: data.message,
                type: 'success',
                text: 'Are you want to reload data?',
                confirmButtonText: "Reload",
                showConfirmButton: true,
                showCancelButton: true,
            }, function (isConfirm) {
                if (isConfirm) {
                    $.pjax.reload({
                        container: "#ims-document-grid-pjax",
                        timeout: false,
                        push: false,
                        replace: false,
                    });
                }
            });
        } else {
            swal('Error', data.message, 'error');
            var t = setTimeout(function () {
                $('#ims-document-form-modal').modal('hide');
            }, 100);
        }
    }

    function showImsDocumentDetail(selector) {
        ajaxProgressLoader('show', '.modal-content');
        var url = $(selector).attr('data-url');
        $.ajax({
            url: url,
            dataType: 'html'
        }).done(function (data) {
            $('#ims-document-form-pjax').html(data);
            $('#ims-document-form-modal').modal('show');
            ajaxProgressLoader('hide', '.modal-content');
            $('#ims-document-form-modal').removeAttr('tabindex');
        }).error(function () {
            showMsgBoxInBottomRight('Limited Access or error in connecting to server', 'error');
            ajaxProgressLoader('hide', '.modal-content');
            return false;
        });
    }

    function companyImsDocumentCallbackPjax(data)
    {
        if (data.result)
        {
            $('#company-ims-document-form-modal').modal('hide');
            var dt = data.data;
            $.pjax.reload({
                container: "#ims-document-grid-pjax",
                timeout: false,
                push: false,
                replace: false,
            });
            swal('success', data.message, 'success');
        } else
        {
            swal('Error', data.message, 'error');
        }
    }

    function showCompanyImsDocumentList(obj) {

        ajaxProgressLoader('show', '#company-ims-document-form-modal .modal-body');
        $('#company-ims-document-form-modal').modal('show');
        var url = $(obj).attr('data-url');
        $.pjax.reload({
            container: "#company-ims-document-form-pjax",
            url: url,
            timeout: false,
            push: false,
            replace: false,
            data: {
            }
        });
        ajaxProgressLoader('hide', '.modal-content');
        return false;
    }

    function deleteCompanyImsDocuments(obj) {
        var items = $('#ims-document-grid').yiiGridView('getSelectedRows');

        if (!(items.length > 0)) {
            showMsgBoxInBottomRight('Please select the action(s).', 'warning');
            return;
        }
        ajaxProgressLoader('show', '#ims-document-grid');
        var url = $(obj).attr('data-url');
        $.pjax.reload({
            container: "#ims-document-grid-pjax",
            url: url,
            timeout: false,
            push: false,
            replace: false,
            data: {
                docs: items
            }
        });
        return false;
    }

    function changePrivated(id) {
        ajaxProgressLoader('show', '#ims-document-grid');
        var url = '<?= Yii::$app->urlManager->createAbsoluteUrl(['document/ims-document/change-private']) ?>' + "&id=" + id;
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
                        reloadForm();
                    }
                });
//                swal('success', data.message, 'success');
//                reloadForm();
            } else {
                swal('Error', data.message, 'error');
            }
        }).error(function () {
            showMsgBoxInBottomRight('Error on connecting to server. <br />Plase login again and try later!', 'error');
            return false;
        });
        ajaxProgressLoader('hide', '#ims-document-grid');
        return false;

    }
    function reloadForm() {
        $.pjax.reload({
            container: '#ims-document-grid-pjax',
            timeout: false,
            push: false,
            replace: false,
        });
    }


    function showImsDocumentKeywordDetail(obj) {
        var url = $(obj).attr('data-url');
        $('#ims-document-keyword-form-modal').modal('show');

        $.ajax({
            url: url,
            dataType: 'html'
        }).done(function (data) {
            $('#ims-document-keyword-form-pjax').html(data);
            $('#ims-document-keyword-form-modal').modal('show');
            ajaxProgressLoader('hide', '.modal-content');
            $('#ims-document-keyword-form-modal').removeAttr('tabindex');
        }).error(function () {
            showMsgBoxInBottomRight('Limited Access or error in connecting to server', 'error');
            ajaxProgressLoader('hide', '.modal-content');
            return false;
        });

    }

    function showImsDocumentKeywordEdit(obj) {
        var url = $(obj).attr('data-url');
        $('#ims-document-edit-keyword-form-modal').modal('show');

        $.ajax({
            url: url,
            dataType: 'html'
        }).done(function (data) {
            $('#ims-document-edit-keyword-form-pjax').html(data);
            $('#ims-document-edit-keyword-form-modal').modal('show');
            ajaxProgressLoader('hide', '.modal-content');
            $('#ims-document-edit-keyword-form-modal').removeAttr('tabindex');
        }).error(function () {
            showMsgBoxInBottomRight('Limited Access or error in connecting to server', 'error');
            ajaxProgressLoader('hide', '.modal-content');
            return false;
        });

    }

    function deleteIMSDocument(id) {
        ajaxProgressLoader('show', '.popup-box');
        var url = $('.delete-ims-document-' + id).attr('data-url');
        $.ajax({
            url: url,
        }).done(function (data) {
            if (data.result == 1) {
                $.pjax.reload({
                    container: '#ims-document-grid-pjax',
                    timeout: false,
                    push: false,
                    replace: false,
                });
                swal('success', data.message, 'success');
            } else {
                swal('Error', data.message, 'error');
            }
        }).error(function () {
            showMsgBoxInBottomRight('Error on connecting to server. <br />Plase login again and try later!', 'error');

            ajaxProgressLoader('hide', '.popup-box');
            return false;
        });
        return false;
    }

    function showImsDocumentModal(selector) {
        ajaxProgressLoader('show', '.modal-content');
        var url = $(selector).attr('data-url');
        $.ajax({
            url: url,
            dataType: 'html'
        }).done(function (data) {
            $('#ims-document-form-pjax').html(data);
            $('#ims-document-form-modal').modal('show');
            ajaxProgressLoader('hide', '.modal-content');
            $('#ims-document-form-modal').removeAttr('tabindex');
        }).error(function () {
            showMsgBoxInBottomRight('Limited Access or error in connecting to server', 'error');
            ajaxProgressLoader('hide', '.modal-content');
            return false;
        });
    }
    function showImsDocumentKeywordModal(selector) {
        ajaxProgressLoader('show', '.modal-content');
        var url = $(selector).attr('data-url');
        $.ajax({
            url: url,
            dataType: 'html'
        }).done(function (data) {
            $('#ims-document-form-pjax').html(data);
            $('#ims-document-form-modal').modal('show');
            ajaxProgressLoader('hide', '.modal-content');
            $('#ims-document-form-modal').removeAttr('tabindex');
        }).error(function () {
            showMsgBoxInBottomRight('Limited Access or error in connecting to server', 'error');
            ajaxProgressLoader('hide', '.modal-content');
            return false;
        });
    }
    function toggleBoxFilter(obj) {
        var display = $('#boxfilter').css('display');
        if (display == 'block') {
            $('#boxfilter').css('display', 'none');
        } else {
            $('#boxfilter').css('display', 'block');
        }
    }
    function getContractSearchFilter() {
        var inputFilter = $('#ims-document-grid-pjax input').serialize();
        var dropdownFilter = "ImsDocumentSearch[mainDocumentID]=" + $('#imsdocumentsearch-maindocumentid').val();
        dropdownFilter += '&ImsDocumentSearch[imsType]=' + $('#imsdocumentsearch-imstype').val();
        dropdownFilter += '&ImsDocumentSearch[status]=' + $('#imsdocumentsearch-status').val();

        return '&' + inputFilter + '&' + dropdownFilter;
    }

    function exportExcelFile(selector) {
        var exportUrl = $(selector).attr('export-url') + getContractSearchFilter();
        $(selector).attr('href', exportUrl);
    }

</script>

<?php
$this->registerCss("
        input[type=text]{height: 30px;} 
        .table > thead > tr > td{padding:4px;} 

        .table {font-family: tahoma; font-size:11px; width:100%; } 

        .table input[type='text']{font-family: tahoma; font-size:12px; width:100%;} 
        .table tr{/*height:100px !important;overflow: hidden*/}
        .table tr th{text-align:center;}
        .table tr td{padding:0 important;text-align: center;}
        .table>tbody>tr>td, 
        .table>tbody>tr>th, 
        .table>tfoot>tr>td, 
        .table>tfoot>tr>th, 
        .table>thead>tr>td, 
        .table>thead>tr>th {
            padding: 3px !important;
            vertical-align: middle;
        }

        .box-bg-3{
//            background-position: top center;
            background-position: top -47px right 211px;
            background-size: 578px;
        }
        .table-striped  tbody tr.odd {
                background-color: #ffecec;
        }

        .table .filters td{
        /*    background: rgb(231, 210, 189);*/
//            background: background:#f8e777;
            background: background:#f8e111;
        }
        .table td{
            max-height: 42px !important;

        }
        .table .table-responsive1
        {
            overflow: visible !important;    
        }
        .table tbody>tr>td, 
        .table tbody>tr>th, 
        .table tfoot>tr>td, 
        .table tfoot>tr>th, 
        .table thead>tr>td, 
        .table thead>tr>th {
            border-top: 1px solid #E7E7E7;
        }
        #ims-document-grid-container .table.table-bordered thead tr{
            background:#f8e777;
            height:35px;
        }
        #ims-document-expire-grid .table.table-bordered thead tr{
            background:#fdf4b1;
            height:35px;
        }






        .table .td-indent-popup{
            min-width: 10px;
            background: #eee !important; 
            position:relative;
            border-color: #eee !important;
        }
        .table .td-indent-popup .indent-line{
        /*    height:100%;
            width: 100%;*/
            border-left: solid 1px white;
            position:absolute;
            top:0;
            bottom: 0;
            left:0px;
            right:0;


        }

        .table .td-indent{
            width:10px;
            background: #9543FD; 
        }
        /*.table th{
            line-height: 3 !important;
            background: rgb(0, 144, 175) !important;
            color:#DADADA !important;

        }
        .table th a {
            color:#fff !important;
        }*/
        ");
?>

