<?php

use app\components\jDateTime;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\PageHeader;
use yii\helpers\Url;
use app\components\Utility;
use omnilight\assets\SweetAlertAsset;

SweetAlertAsset::register($this);

$userCompanyId = yii::$app->session->get('CompanyID');
$canStampUpdate = \app\access\AccessControl::can("document/stamp-document/create");
$canStampExport = \app\access\AccessControl::can("document/export/stamp");
$canVerifiedStamp = \app\access\AccessControl::can("document/stamp-document/verified", []);
$isCandoAdmin = \app\components\Security::isCandoAdmin();
$canCreateStampDocument = \app\access\AccessControl::isAccess([
    'processAction',
    'aid' => 1195,
    'errorException' => false,
]);
$this->title = Yii::t('document', 'Stamp List');
?>
<?=
\app\components\PageHeader::widget([
    'title' => $this->title,
    'icon' => 'im-screen',
    'help' => $this->render('_HelpStamp')
]);
?>
<div class="row">

    <div class="col-md-4 pull-right text-right mb5 ">
        <div class="row">
            <div class="">
                            <?php
            // ✅ دکمه جدید برای رفتن به فرایند نشان

echo Html::a(
    '<i class="fa fa-arrow-left" style="margin-left:5px;"></i> ' .Yii::t("document", 'stamp workflow'),
    Url::to(['/workflow/workflow/stamp-document', 'type' => 'ToDo']),
    [
        'class' => 'btn btn-sm btn-warning pull-left ml3',
'style' => '
    background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
    border: none;
    border-radius: 30px;
    padding: 6px 22px;
    font-weight: bold;
    color: #2d2d2d;
    box-shadow: 0 4px 20px rgba(255, 210, 0, 0.5);
    transition: all 0.3s ease;
    letter-spacing: 0.5px;
',
'onmouseover' => 'this.style.boxShadow="0 6px 35px rgba(255, 210, 0, 0.8)"; this.style.transform="scale(1.06)";',
'onmouseout' => 'this.style.boxShadow="0 4px 20px rgba(255, 210, 0, 0.5)"; this.style.transform="scale(1)";',
        'data-toggle' => 'tooltip',
        'data-placement' => 'top',
        'title' => Yii::t("document", 'مشاهده  فرایند نشان‌ها')
    ]
);
?>
<?php if ($canStampUpdate) { ?>
                    <button class="btn btn-sm btn-primary pull-right ml5"
                            data-url="<?= Yii::$app->urlManager->createAbsoluteUrl(["document/stamp-document/create"]) ?>"
                            onclick="showStampModal(this)"><i
                            class="fa-plus"></i> <?= Yii::t('document', 'ثبت مستقیم نشان سازمانی') ?> </button>
<?php } ?>

                <?php if ($canStampExport) { ?>
    <?php
    echo Html::a('<i class="im-file-excel"></i> Excel',
            Yii::$app->urlManager->createAbsoluteUrl(["document/export/stamp"]), [
        'target' => '_blank',
        'data-toggle' => 'tooltip',
        'data-placement' => 'left',
        'class' => 'btn btn-sm btn-success',
    ]);
    ?>
                <?php } ?>

            </div>
        </div>
    </div>
</div>


<div class='col-md-12 col-lg-12'>

<?=
kartik\grid\GridView::widget([
    'id' => 'stamp-grid',
    'floatHeader' => true,
    'options' => [
        'style' => 'font-family:tahoma; font-size:12px;',
    ],
    'tableOptions' => ['class' => 'table table-striped table-hover non-responsive table table-bordered'],
    'rowOptions' => function ($model, $key, $index, $grid) {
        $class = $index % 2 ? 'odd' : 'even';
        return array('key' => $key, 'index' => $index, 'class' => $class);
    },
    'dataProvider' => $stampDataProvider,
    'filterModel' => $stampSearchModel,
    'hover' => true,
    'pjax' => true,
    'pjaxSettings' => [
        'options' => [
            'id' => 'stamp-grid-pjax',
        ],
    ],
    'columns' => [
        [
            'attribute' => 'StampId',
            'value' => 'StampId',
            'options' => [
                'width' => '10px'
            ],
        ],
[
    'format' => 'raw',
    'label' => \Yii::t("document", 'Organizational Logo'),
    'width' => '80px',
    'value' => function ($model) {
        $imageUrl = !empty($model->FinalStampDocument)
            ? $model->getFinalStampDocumentUrl()
            : Yii::$app->urlManager->createAbsoluteUrl([
                'document/stamp-document/logo-image',
                'id' => $model->StampId
            ]);

        return \yii\bootstrap\Html::img($imageUrl, [
            'style' => 'border-radius:10px',
            'width' => '50px;',
            'height' => '30px'
        ]);
    },
],
        [
            'attribute' => 'Company',
            'label' => Yii::t('document', 'Company'),
            'value' => function ($model) {
                return empty($model->obs->company) ? '' : $model->obs->company->companyName;
            },
            'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
            'filterWidgetOptions' => [
                'data' => $companyList,
                'options' => ['placeholder' => 'انتخاب شرکت'],
                'pluginOptions' => [
                    'allowClear' => true,
                    'width' => '200px'
                ]
            ],
        ],
        [
            'attribute' => 'ParentObs',
            'format' => 'raw',
            'value' => function ($model) {
                return Utility::generateTextShorterSpan(yii\helpers\Html::encode($model->assistanceTitle), 30, 30);
            },
            'label' => Yii::t('automation', 'Parent Obs'),
            'options' => [
                'width' => '200px',
            ],
        ],
        [
            'attribute' => 'Position',
            'label' => \Yii::t("document", 'Position'),
            'value' => function ($model) {
                return !empty($model->ObsIDRef) ? $model->obs->fullTitle : null;
            },
        ],

[
    'attribute' => 'OrganizationalMaker',
    'label' => Yii::t('document', 'Organizational Maker'),
    'value' => function($model){
        return $model->getOrganizationalMakerListLabel();
    },
    'filter' => $stampSearchModel::getOrganizationalMakerList(),
],
        [
            'attribute' => 'StampTitle',
            'label' => \Yii::t("document", 'Stamp Title'),
            'value' => 'StampTitle',
        ],
        [
            'attribute' => 'StampTypeIDRef',
            'value' => function ($model) {
                return $model->stampType ? $model->stampType->TypeName : null;
            },
            'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
            'filterWidgetOptions' => [
                'data' => \yii\helpers\ArrayHelper::map(\app\modules\document\models\StampTypeDocument::find()
                ->all(), 'StampTypeId', 'TypeName'),
                'options' => ['placeholder' => 'نوع نشان'],
                'pluginOptions' => [
                    'allowClear' => true,
                    'width' => '100px'
                ]
            ],
        ],
//        [
//            'attribute' => 'StampCode',
//        ],
        [
            'attribute' => 'RequestDate',
        ],
        [
            'attribute' => 'SerialNumber',
        ],
        [
            'attribute' => 'NationalCode',
            'label' => 'صاحب امضا/نام',
            'format' => 'raw',
            'value' => function ($model) {
                if(!empty($model->HumanResourceIDRef)) {
                    $humanResource = $model->humanResource;
                    return '<table class="inner-table">'
                    . '<tr><td width=\'200\'>'
                    . $humanResource->fullName
                    . '<tr><td >'
                    . $humanResource->NationalCode
                    . '</table>';
                }
            },
        ],
        [
            'attribute' => 'LastFlowIDRef',
            'label' => Yii::t('document', 'Last Holder'),
            'format' => 'raw',
            'value' => function ($model) {
                $firstName = $model->lastFlow->human->FirstName ?? ' ';
                $lastName = $model->lastFlow->human->LastName ?? ' ';
                $nationalCode = $model->lastFlow->human->NationalCode ?? ' ';
                return '<table class="inner-table">'
                . '<tr><td width=\'200\'>'
                . $firstName . ' ' . $lastName
                . '<tr><td >'
                . $nationalCode
                . '</table>';
            },
        ],
        [
            'attribute' => 'lastUser',
            'label' => Yii::t('automation', 'Last Update'),
            'format' => 'raw',
            'value' => function ($model) {
                return '<table class="inner-table">'
                . '<tr><td width=\'200\'>'
                . ($model->user ? ($model->user->fullName) : 'N/A')
                . '<tr><td >'
                . $model->LastUpdate
                . '</table>';
            },
        ],
        [
            'label' => \Yii::t("document", 'Status'),
            'attribute' => 'Status',
            'value' => function ($model) {
                return $model->getStatusLabal();
            },
            'filter' => $stampSearchModel::getStatusList(),
        ],
        [
            'class' => kartik\grid\ActionColumn::className(),
            'template' => '{update}{detail}{verified}{refund}',
            'options' => [
                'width' => '60px',
            ],
'buttons' => [
'update' => function ($key, $model, $index) use ($canStampUpdate, $isCandoAdmin) {

    if ($canStampUpdate || $isCandoAdmin) {

        // اگر امحا، رد درخواست، فرآیندی یا تایید شده بود، برای کاربر عادی ویرایش نمایش داده نشود
        if (
            !$isCandoAdmin &&
            in_array($model->Status, ['destroyed', 'rejected', 'in_process', 'approved'], true)
        ) {
            return '';
        }

        return \yii\helpers\Html::a('<i class="fa-pencil s16"></i>', '#', [
            'data-toggle' => 'tooltip',
            'class' => 'btn btn-xs tip',
            'data-placement' => 'top',
            'data-original-title' => 'Edit Stamp',
            'data-url' => Yii::$app->urlManager->createAbsoluteUrl([
                'document/stamp-document/update',
                'sid' => $model->StampId
            ]),
            'onclick' => 'showStampModal(this)'
        ]);
    }

    return '';
},
    'detail' => function ($url, $model, $key) {
        return Html::a('<i class="ec-search s16"></i>', Url::to([
            '/document/stamp-document/view',
            'sid' => $model->StampId
        ]), [
            'data-original-title' => 'Stamp Detail',
            'class' => 'btn btn-xs tip',
            'data-pjax' => '0',
            'target' => '_blank',
            'data-toggle' => 'tooltip'
        ]);
    },

    'verified' => function ($key, $model, $index) use ($canVerifiedStamp, $isCandoAdmin, $userCompanyId) {

        if (
            $isCandoAdmin ||
            (
                $canVerifiedStamp &&
                !in_array($model->Status, ['destroyed', 'rejected', 'in_process'], true) &&
                ($userCompanyId == 248 || (int)$model->OrganizationalMaker === 2)
            )
        ) {
            $iconClass = $model['Status'] == 'approved'
                ? 'im-checkbox-checked'
                : 'im-checkbox-unchecked';

            return kartik\helpers\Html::a('<i class="' . $iconClass . ' s15"></i>', '#', [
                'data-pjax' => '0',
                'title' => 'Verified Stamp document',
                'data-toggle' => 'tooltip',
                'data-placement' => 'top',
                'data-original-title' => 'Edit Stamp Document',
                'data-url' => Yii::$app->urlManager->createAbsoluteUrl([
                    "document/stamp-document/verified",
                    'Id' => $model->StampId
                ]),
                'onclick' => 'confirmBox("Are you sure to change Verified?!", "Yes!", "changeVerified(' . $model->StampId . ')");',
            ]);
        }

        return '';
    },
            'refund' => function ($url, $model, $key) use ($canCreateStampDocument) {
                            if (!$canCreateStampDocument) {
                                return '';
                            }
                            return Html::a('<i class="fa fa-arrow-left s16"></i>', '#', [
                                'class' => 'btn btn-xs tip btn-warning',
                                'data-pjax' => '0',
                                'data-toggle' => 'tooltip',
                                'data-placement' => 'top',
                                'data-original-title' => 'درخواست امحاء',
                                'title' => 'درخواست امحاء',
                                'data-url' => Yii::$app->urlManager->createAbsoluteUrl([
                                    'document/stamp-document/create-refund',
                                    'sid' => $model->StampId,
                                ]),
                                'onclick' => 'showStampModal(this); return false;',
                            ]);
                                    }
]
        ],
    ],
]);
?>
</div>
</div>

    <?php
    yii\bootstrap\Modal::begin([
        'id' => 'stamp-modal',
        'header' => Yii::t('document', 'create or edit Stamp'),
        'size' => yii\bootstrap\Modal::SIZE_LARGE,
        'options' => [
            'tabindex' => '',
        ]
    ]);
    ?>
    <?php Pjax::begin(['id' => 'stamp-form-pjax']); ?>

    <?php Pjax::end(); ?>
    <?php yii\bootstrap\Modal::end(); ?>

    <?php
    yii\bootstrap\Modal::begin([
        'id' => 'activity-document-base-type-modal',
        'header' => Yii::t('document', 'create or edit Stamp'),
        'size' => yii\bootstrap\Modal::SIZE_LARGE,
        'options' => [
            'tabindex' => '',
        ]
    ]);
    ?>
<?php Pjax::begin(['id' => 'activity-document-base-type-form-pjax']); ?>

<?php Pjax::end(); ?>
<?php yii\bootstrap\Modal::end(); ?>

<?php
$js = "
      $('.modal-content').draggable();
    ";
$this->registerJs($js);
?>


<script>
    function showStampModal(obj) {
    ajaxProgressLoader('show', '.modal-content');

    var url = $(obj).attr('data-url');

    $.ajax({
        url: url,
        dataType: 'html'
    }).done(function (data) {
        $('#stamp-form-pjax').html(data);
        $('#stamp-modal').modal('show');
    }).fail(function () {
        showMsgBoxInBottomRight('خطا در باز کردن فرم نشان', 'error');
    }).always(function () {
        ajaxProgressLoader('hide', '.modal-content');
    });

    return false;
}


   function stampCallbackPjax(data) {
    if (data.result) {
        $('#stamp-modal').modal('hide');

        if (typeof showMsgBoxInBottomRight !== 'undefined') {
            showMsgBoxInBottomRight(data.message, 'success');
        } else {
            swal('Success', data.message, 'success');
        }

        setTimeout(function () {
            reloadForm();
        }, 500);

    } else {
        if (typeof showMsgBoxInBottomRight !== 'undefined') {
            showMsgBoxInBottomRight(data.message, 'error');
        } else {
            swal('Error', data.message, 'error');
        }
    }
}

    function changeVerified(Id) {
        ajaxProgressLoader('show', '.obs-list .row');
        var url = '<?= Yii::$app->urlManager->createAbsoluteUrl(['document/stamp-document/verified']) ?>' + "&Id=" + Id;
        $.ajax({
            url: url,
            dataType: "json"
        }).done(function (data) {
            if (data.result == 1) {
                swal('success', data.message, 'success');
                reloadForm();
            } else {
                swal('Error', data.message, 'error');
            }
        }).error(function () {
            showMsgBoxInBottomRight('Error on connecting to server. <br />Plase login again and try later!', 'error');
            return false;
        });
        ajaxProgressLoader('hide', '.obs-list .row');
        return false;

    }


    function reloadForm() {
        $.pjax.reload({
            container: '#stamp-grid-pjax',
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
$(document).off('beforeSubmit', '#stamp-form').on('beforeSubmit', '#stamp-form', function (e) {
    e.preventDefault();

    var form = $(this);
    var formData = new FormData(this);

    if (typeof ajaxProgressLoader !== 'undefined') {
        ajaxProgressLoader('show', '.modal-content');
    }

    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: formData,
        dataType: 'json',
        processData: false,
        contentType: false
    }).done(function (data) {

        if (data.result) {
            $('#stamp-modal').modal('hide');

            if (typeof showMsgBoxInBottomRight !== 'undefined') {
                showMsgBoxInBottomRight(data.message, 'success');
            } else {
                swal('Success', data.message, 'success');
            }

            setTimeout(function () {
                reloadForm();
            }, 500);

        } else {
            if (typeof showMsgBoxInBottomRight !== 'undefined') {
                showMsgBoxInBottomRight(data.message || 'خطا در ثبت اطلاعات', 'error');
            } else {
                swal('Error', data.message || 'خطا در ثبت اطلاعات', 'error');
            }
        }

    }).fail(function (xhr, status, error) {

        console.log('Stamp create ajax error:', status, error);
        console.log(xhr.responseText);

        if (typeof showMsgBoxInBottomRight !== 'undefined') {
            showMsgBoxInBottomRight('خطا در ارتباط با سرور', 'error');
        } else {
            swal('Error', 'خطا در ارتباط با سرور', 'error');
        }

    }).always(function () {
        if (typeof ajaxProgressLoader !== 'undefined') {
            ajaxProgressLoader('hide', '.modal-content');
        }
    });

    return false;
});




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
        #stamp-grid-container .table.table-bordered thead tr{
            background:#f8e777;
            height:35px;
            color: #000000;
        }
       
        #stamp-grid-container .table.table-bordered thead tr a{
            background:#f8e777;
            height:35px;
            color: #000000;
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
            color:#000000 !important;
        }*/
        ");
?>

