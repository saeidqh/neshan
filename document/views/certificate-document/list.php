<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use omnilight\assets\SweetAlertAsset;

SweetAlertAsset::register($this);

$canCertificateUpdate = \app\access\AccessControl::can("document/certificate-document/update");
$canCertificateDelete = \app\access\AccessControl::can("document/certificate-document/delete");
$canCertificateDownload = \app\access\AccessControl::can("document/certificate-document/download");

$this->title = Yii::t('document', 'Certificate List');
?>
<?=
\app\components\PageHeader::widget([
    'title' => $this->title,
    'icon' => 'im-screen',
]);
?>
<div class="row">

    <div class="col-md-4 pull-right text-right mb5 ">
        <div class="row">
            <div class="">
                <?php if ($canCertificateUpdate) { ?>
                    <button class="btn btn-sm btn-primary pull-right ml5"
                            data-url="<?= Yii::$app->urlManager->createAbsoluteUrl(["document/certificate-document/create"]) ?>"
                            onclick="showCertificateModal(this)"><i
                            class="fa-plus"></i> <?= Yii::t('document', 'New Certificate') ?> </button> 
                <?php
                } if ($canCertificateUpdate) {
                    echo Html::a(\Yii::t("document", "Standard Process"), Yii::$app->urlManager->createAbsoluteUrl(['document/standard-request/list']), [
                        'class' => 'btn btn-sm btn-mini btn-primary',
                        'data-pjax' => '0',
                            ]
                    );
                }
                ?>

            </div>
        </div>
    </div>
</div>


<div class='col-md-12 col-lg-12'>

<?=
kartik\grid\GridView::widget([
    'id' => 'certificate-grid',
    'floatHeader' => true,
    'options' => [
        'style' => 'font-family:tahoma; font-size:12px;',
    ],
    'tableOptions' => ['class' => 'table table-striped table-hover non-responsive table table-bordered'],
    'rowOptions' => function ($model, $key, $index, $grid) {
        $class = $index % 2 ? 'odd' : 'even';
        return array('key' => $key, 'index' => $index, 'class' => $class);
    },
    'dataProvider' => $certificateDataProvider,
    'filterModel' => $certificateSearchModel,
    'hover' => true,
    'pjax' => true,
    'pjaxSettings' => [
        'options' => [
            'id' => 'certificate-grid-pjax',
        ],
    ],
    'columns' => [
        [
            'class' => \kartik\grid\ExpandRowColumn::className(),
            'value' => function ($data) {
//                    return \kartik\grid\GridView::ROW_COLLAPSED;
                if ($data->certificateTypeIdRef == 1 || $data->certificateTypeIdRef == 3) {
                    return \kartik\grid\GridView::ROW_COLLAPSED;
                } else {
                    return '';
                }
            },
            'detailUrl' => Yii::$app->urlManager->createAbsoluteUrl(['document/certificate-document/document-activity-show']),
            'headerOptions' => ['class' => 'kartik-sheet-style'],
            'expandOneOnly' => true,
            'expandIcon' => '<span class="fa-plus"></span>',
            'collapseIcon' => '<span class="fa-minus"></span>',
            'options' => ['width' => '0.65%'],
        ],
        [
            'label' => '',
            'format' => 'raw',
            'filter' => '<div class="filter-btn text-left filter-btn" id="ifilter" style="width:50px">'
            . '<a class="btn btn-xs btn-round filter-btn-yellow btn" value="yellow" data-pjax="1" data-toggle="tooltip" title="دارای اعتبار بین 3 تا 6 ماه" ></a>'
            . '<a class="btn btn-xs  btn-round filter-btn-red" value="red" data-pjax="1" data-toggle="tooltip" title="فاقد اعتبار"  </a>'
            . '<a class="btn btn-xs  btn-round filter-btn-green" value="green" data-pjax="1" data-toggle="tooltip" title="دارای اعتبار بیشتر یک سال" ></a>'
            . '<a class="btn btn-xs  btn-round filter-btn-blue" value="blue" data-pjax="1" data-toggle="tooltip" title="دارای اعتبار بین 9 تا 12 ماه" ></a>'
            . '<a class="btn btn-xs  btn-round filter-btn-orange" value="orange" data-pjax="1" data-toggle="tooltip" title="دارای حداکثر اعتبار 3 ماهه" ></a>'
            . '<a class="btn btn-xs  btn-round filter-btn-purple" value="purple" data-pjax="1" data-toggle="tooltip" title="دارای اعتبار 6 تا 9 ماه " ></a>'
            . '</div>',
            'value' => function ($data) {
                return \app\components\Utility::getCertificateState(
                        ($data["expirationDate"]));
            },
            'options' => [
                'width' => '72',
                'class' => 'td-text-right',
            ],
        ],
        [
            'label' => 'وضعیت',
            'format' => 'raw',
            'value' => function ($data) {
                $certificateStateValue = \app\components\Utility::getCertificateStateValue($data["expirationDate"]);
                return $certificateStateValue['tooltipTitle'];
            },
            'options' => [
                'width' => '72',
                'class' => 'td-text-right',
            ],
        ],
        [
            'attribute' => 'certificateId',
            'value' => 'certificateId',
            'options' => [
                'width' => '10px'
            ],
        ],
        [
            'attribute' => 'company',
            'label' => \Yii::t("document", 'company'),
            'value' => 'company',
        ],
        [
            'attribute' => 'standard',
            'label' => \Yii::t("document", 'Standard/Model'),
            'value' => function ($model) {
                return $model->standard;
            },
        ],
        [
            'attribute' => 'certificateType',
            'label' => \Yii::t("document", 'Certificate Type'),
            'value' => 'certificateType',
        ],
        [
            // 'attribute' => 'company',
            'label' => 'فرایند ممیزی',
            'value' => function ($data) {
                return '';
            },
        ],
        [
            'attribute' => 'activityIdList',
            'label' => \Yii::t("document", 'Activity'),
            'value' => 'activityIdList',
        ],
        [
            'attribute' => 'issuingOrganization',
            'value' => 'issuingOrganization',
        ],
        [
            'attribute' => 'issueDate',
            'value' => 'issueDate',
        ],
        [
            'attribute' => 'expirationDate',
            'value' => 'expirationDate',
        ],
        [
            'class' => kartik\grid\ActionColumn::className(),
            'template' => '{update} {delete} {download}',
            'options' => [
                'width' => '60px',
            ],
            'buttons' => [
                'delete' => function ($key, $model, $index) use ($canCertificateDelete) {
                    if ($canCertificateDelete) {
                        return Html::a('<i class="fa-trash s16"></i>', '#', [
                                    'class' => 'delete-certificate-' . $model->certificateId,
                                    'data-toggle' => 'tooltip',
                                    'data-placement' => 'top',
                                    'data-original-title' => 'Remove Certificate item',
                                    'onclick' => 'confirmBox("Are you sure to delete this item?!", "Delete!", "deleteCertificate(\'' . $model->certificateId . '\')");',
                                    'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/certificate-document/delete', 'certificateId' => $model->certificateId])
                                        ]
                                ) . '&nbsp;';
                    }
                },
                'update' => function ($key, $model, $index) use ($canCertificateUpdate) {
                    if ($canCertificateUpdate) {

                        return \yii\helpers\Html::a('<i class="fa-pencil s16"></i>', '#', [
                                    'data-toggle' => 'tooltip',
                                    'data-placement' => 'top',
                                    'data-original-title' => 'Edit Certificate',
                                    'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/certificate-document/update', 'certificateId' => $model->certificateId]),
                                    'onclick' => 'showCertificateModal(this)'
                                        ]
                        );
                    }
                },
                'download' => function ($url, $model, $key)use ($canCertificateDownload) {
                    if ($canCertificateDownload) {
                        return Html::a('&nbsp;<i class="fa-circle-arrow-down s16"></i>', Yii::$app->urlManager->createAbsoluteUrl(['document/certificate-document/download', 'certificateId' => $model->certificateId]), [
                                    'data-pjax' => '0',
                                    'title' => 'Click to download file',
                                    'target' => '_blank',
                        ]);
                    } else {
                        return "";
                    }
                },
            ]
        ],
    ],
]);
?>
</div>
</div>


<!-- Load List-->

    <?php
    yii\bootstrap\Modal::begin([
        'id' => 'certificate-modal',
        'header' => Yii::t('document', 'create or edit Certificate'),
        'size' => yii\bootstrap\Modal::SIZE_LARGE,
        'options' => [
            'tabindex' => '',
        ]
    ]);
    ?>
<?php Pjax::begin(['id' => 'certificate-form-pjax']); ?>

<?php Pjax::end(); ?>
<?php yii\bootstrap\Modal::end(); ?>

<?php
yii\bootstrap\Modal::begin([
    'id' => 'activity-document-base-type-modal',
    'header' => Yii::t('document', 'create or edit Certificate'),
    'size' => yii\bootstrap\Modal::SIZE_LARGE,
    'options' => [
        'tabindex' => '',
    ]
]);
?>
<?php Pjax::begin(['id' => 'activity-document-base-type-form-pjax']); ?>

<?php Pjax::end(); ?>
<?php yii\bootstrap\Modal::end(); ?>


<script>


    function showCertificateModal(obj) {
        ajaxProgressLoader('show', '.modal-content');
        var url = $(obj).attr('data-url');
        $.ajax({
            url: url,
            dataType: 'html'
        }).done(function (data) {
            $('#certificate-form-pjax').html(data);
            $('#certificate-modal').modal('show');
            ajaxProgressLoader('hide', '.modal-content');
        }).error(function () {
            showMsgBoxInBottomRight('Certificate in not Unknown. you cannot Edit it', 'error');
            return false;
        });
    }

    // $(document).ready(function () {
    //     $('#certificate-form-pjax').on('pjax:send', function () {
    //         ajaxProgressLoader('show', '#certificate-modal .modal-body');
    //     });
    //     $('#certificate-form-pjax').on('pjax:complete', function (b, d) {
    //         ajaxProgressLoader('hide', '#certificate-modal .modal-body');
    //     });
    //
    // });


    function certificateCallbackPjax(data) {
        if (data['result']) {
            $('#certificate-modal').modal('hide');
            showMsgBoxInBottomRight(data['message'], 'success');

        } else {
            showMsgBoxInBottomRight(data['message'], 'error');
        }
        reloadForm();

    }

    function reloadForm() {
        $.pjax.reload({
            container: '#certificate-grid-pjax',
            timeout: false,
            push: false,
            replace: false,
        });
    }

    function DocumentBaseTypeCallbackPjax(data) {
        if (data['result']) {
            $('#activity-document-base-type-modal').modal('hide');
            showMsgBoxInBottomRight(data['message'], 'success');

        } else {
            showMsgBoxInBottomRight(data['message'], 'error');
        }
        baseTypeReloadForm();

    }

    function baseTypeReloadForm() {
        $.pjax.reload({
            container: '#document-base-type-grid-pjax',
            timeout: false,
            push: false,
            replace: false,
        });
    }

    function deleteCertificate(certificateId) {
        var id = $('.delete-certificate-' + certificateId).attr('data-id');
        var url = $('.delete-certificate-' + certificateId).attr('data-url');
        $.ajax({
            url: url,
        }).done(function (data) {
            if (data.result == 1) {
                $.pjax.reload({
                    container: '#certificate-grid-pjax',
                    timeout: false,
                    push: false,
                    replace: false,
                });
                setTimeout(function () {
                    showMsgBoxInBottomRight(data.message, 'success');
                }, 1000)


            } else {
                showMsgBoxInBottomRight(data.message, 'Danger');
            }
            return false;
        }).error(function () {
            showMessageBox(
                    "Error on connecting to server. <br />Plase login again and try later!",
                    "Error",
                    "err"
                    );
            ajaxProgressLoader('hide', '#tbl-contracts-grid-0-pjax');
            return false;
        });
        return false;
    }

    function showDocumentActivityBaseTypeModal(obj) {
        ajaxProgressLoader('show', '.modal-content');

        var url = $(obj).attr('data-url');
        $.ajax({
            url: url,
            dataType: 'html'
        }).done(function (data) {
            $('#activity-document-base-type-form-pjax').html(data);
            $('#activity-document-base-type-modal').modal('show');
            ajaxProgressLoader('hide', '.modal-content');
        }).error(function () {
            showMsgBoxInBottomRight('Limited Access or error in connecting to server', 'error');
            return false;
        });
    }



</script>

<?php
$this->registerCss("
.filter-btn{
/*    background:rgb(241, 241, 241);
    border:solid 1px rgb(173, 173, 173);*/
    padding:2px;
    width: 41px;
}
.filter-btn .filter-btn-red{
    background:rgb(255, 44, 44);
    border:solid 1px rgb(180, 50, 50);
    color:rgb(255, 44, 44);
}
.filter-btn .filter-btn-red .active{color:#fff !important;}
.filter-btn .filter-btn-green{
    background: rgb(55, 207, 55);
    border: solid 1px rgb(56, 126, 29);
    color: rgb(55, 207, 55);
}
.filter-btn .filter-btn-green .active{color:rgb(255, 255, 255) !important;}
.filter-btn .filter-btn-yellow{
    background:yellow;
    border: solid 1px rgb(214, 183, 16);
    color:yellow;
}
.filter-btn .filter-btn-yellow .active{color:rgb(255, 102, 0) !important;}

.filter-btn .filter-btn-blue{
    background:blue;
    border: solid 1px rgb(214, 183, 16);
    color:blue;
}
.filter-btn .filter-btn-blue .active{color:rgb(255, 102, 0) !important;}

.filter-btn .filter-btn-orange{
    background:orange;
    border: solid 1px rgb(214, 183, 16);
    color:orange;
}
.filter-btn .filter-btn-orange .active{color:rgb(255, 102, 0) !important;}

.filter-btn .filter-btn-purple{
    background:purple;
    border: solid 1px rgb(214, 183, 16);
    color:purple;
}
.filter-btn .filter-btn-purple .active{color:#a102a1 !important;}


.filter-btn a{
    margin:1px !important;
    height:15px !important;
    width:15px !important;
    padding: 0 !important;
    position: relative;
}
.filter-btn i{
    padding: 0;
    margin:0;
    position: absolute;
    left: 0px;
    top: 1px;
}
.contract-state-blue{background: #0000FF;}
.contract-state-orange{background: rgb(255, 196, 0);}
.contract-state-purple{background: #a102a1;}
.contract-state-yellow{background: rgb(255, 235, 0);}
.contract-state-green{background: rgb(55, 207, 55)}
.contract-state-red{background: rgb(255, 44, 44);}
.contract-state{
    width:30px;
    height:20px;
    margin:1px;
    border-radius: 10px !important;
}

.td-text-right .inner-table{
    float:right;
}

.filter-btn .filter-btn-pink{
    background:#af91e1;
    border:solid 1px rgb(85, 85, 85);
    color:rgb(255, 44, 44);
}
.filter-btn .filter-btn-pink .active{color:#fff !important;}



.filter-btn .filter-btn-blue{
    background:#75b9e6;
    border:solid 1px #777;
    color:rgb(255, 44, 44);
}
.filter-btn .filter-btn-blue .active{color:#fff !important;}



.filter-btn .filter-btn-green-light{
    background: #bee6ff;
    border: solid 1px #777;
    color: #af91e1;
    box-shadow: 4px 4px 17px 0px #666;
}
.filter-btn .filter-btn-gold .active{color:#fff !important;}
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
        #certificate-grid-container .table.table-bordered thead tr{
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

