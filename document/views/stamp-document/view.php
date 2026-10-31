<?php

use yii\widgets\Pjax;
use app\components\PageHeader;
use app\components\Utility;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use omnilight\assets\SweetAlertAsset;

/* @var $this yii\web\View */
/* @var $stampModel app\modules\document\models\StampDocument */
/* @var $sid int */
/* @var $internalScope array */
/* @var $externalScope array */
/* @var $stampFlowDataProvider yii\data\ActiveDataProvider */
/* @var $stampFlowSearchModel app\modules\document\models\StampFlowDocumentSearch */
SweetAlertAsset::register($this);
$canStampFlowCreate = \app\access\AccessControl::can("document/stamp-flow-document/create");
$canStampFlowUpdate = \app\access\AccessControl::can("document/stamp-flow-document/update");
$canStampFlowRefund = \app\access\AccessControl::can("document/stamp-document/refund");
$isCandoAdmin = \app\components\Security::isCandoAdmin();
$userCompanyId = Yii::$app->session->get('CompanyID');
$isReadOnlyView = (int) Yii::$app->request->get('readonly', 0) === 1;
$viewOnlyStatuses = ['destroyed', 'rejected', 'in_process'];
$isViewOnlyStatus = in_array($stampModel->Status, $viewOnlyStatuses, true);
$canVerifiedStamp = \app\access\AccessControl::can("document/stamp-document/verified", []);

$this->title = 'مدیریت نشان سازمانی  ' ;
$this->params['breadcrumbs'] = [
    'label' => 'index',
];

echo PageHeader::widget([
    'title' => $this->title,
    'icon' => 'im-contract',
    'help' => $this->render('_helpStampView'),
]);
?>

<div style="border:solid 1px #dddddd; border-radius: 5px">
    <?=
    $this->render('_viewStamp', [
        'stampModel' => $stampModel,
        'sid' => $sid,
        'internalScope' => $internalScope,
        'externalScope' => $externalScope,
    ]);
    ?>
</div>

<div class="row">
    <div style="padding:5px" class="pull-right text-right">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12 pull-right text-right">
                        <?php if (!$isReadOnlyView && !$isViewOnlyStatus && ($isCandoAdmin || $canStampFlowCreate)): ?>
                <button
                        class="btn btn-sm btn-primary pull-right"
data-url="<?= Yii::$app->urlManager->createAbsoluteUrl([
    "document/stamp-flow-document/create",
    'sid' => $stampModel->StampId,
    'from' => 'view'
]) ?>"                        onclick="showFlowModal(this)">
                        <i class="fa-plus"></i> <?= Yii::t('document', 'New Stamp Flow') ?>
                    </button>
                <?php endif; ?>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-12 text-right">
<?php if (
    !$isReadOnlyView &&
    !$isViewOnlyStatus &&
    (
        $isCandoAdmin ||
        (
            $canStampFlowRefund &&
            ($userCompanyId == 248 || (int)$stampModel['OrganizationalMaker'] === 2)
        )
    )
): ?>                  
                <button
                        class="btn btn-sm btn-primary pull-right"
                        data-url="<?= Yii::$app->urlManager->createAbsoluteUrl([ "document/stamp-document/refund",'id' => $stampModel->StampId]) ?>"
                        onclick="showRefundModal(this)">
                        <i class="fa-plus"></i> <?= Yii::t('document', 'Refund Stamp') ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div style="border:solid 1px #dddddd; border-radius: 5px">
    <?=
    kartik\grid\GridView::widget([
        'id' => 'flow-grid',
        'floatHeader' => true,
        'options' => [
            'style' => 'font-family:tahoma; font-size:12px;',
        ],
        'tableOptions' => [
            'class' => 'table table-striped table-hover non-responsive table table-bordered'
        ],
        'rowOptions' => function ($model, $key, $index, $grid) {
            return [
                'key' => $key,
                'index' => $index,
                'class' => $index % 2 ? 'odd' : 'even',
                'style' => 'border-bottom: 2px solid #b5b5b5;',
            ];
        },
        'dataProvider' => $stampFlowDataProvider,
        'filterModel' => $stampFlowSearchModel,
        'hover' => true,
        'pjax' => true,
        'pjaxSettings' => [
            'options' => [
                'id' => 'flow-grid-pjax',
            ],
        ],
        'columns' => [
            [
                'attribute' => 'FlowId',
                'value' => 'FlowId',
                'options' => ['width' => '10px'],
            ],
            [
                'attribute' => 'NationalCode',
                'label' => Yii::t('automation', 'NationalCode'),
                'value' => function ($model) {
                    return $model->human ? $model->human->NationalCode : '';
                },
                'options' => ['width' => '200px'],
            ],
            [
                'attribute' => 'FullName',
                'label' => Yii::t('document', 'FullName'),
                'value' => function ($model) {
                    return $model->human ? trim($model->human->FirstName . ' ' . $model->human->LastName) : '';
                },
                'options' => ['width' => '200px'],
            ],
            [
                'attribute' => 'ParentObs',
                'label' => Yii::t('automation', 'Parent Obs'),
                'format' => 'raw',
                'value' => function ($model) {
                    return Utility::generateTextShorterSpan(Html::encode($model->assistanceTitle), 30, 30);
                },
                'options' => ['width' => '200px'],
            ],
            [
                'attribute' => 'Position',
                'label' => Yii::t('automation', 'Position2'),
                'value' => function ($model) {
                    $position = $model->position;
                    return $position
                        ? ((isset($position->positionType) ? $position->positionType->Title . ' ' : '') . $position->obsTitle)
                        : '';
                },
                'options' => ['width' => '200px'],
            ],
            [
                'attribute' => 'Mobile',
                'label' => Yii::t('automation', 'Mobile'),
                'value' => function ($model) {
                    return $model->human ? $model->human->Mobile : '';
                },
                'options' => ['width' => '200px'],
            ],
            [
                'attribute' => 'ReceivedDate',
                'label' => Yii::t("document", 'Received Date'),
                'value' => 'ReceivedDate',
            ],
            [
                'attribute' => 'RefundDate',
                'value' => 'RefundDate',
            ],
            [
                'attribute' => 'Create_by',
                'label' => Yii::t('automation', 'Last Update'),
                'format' => 'raw',
                'value' => function ($model) {
                    return '<table class="inner-table">'
                        . '<tr><td width="200">' . ($model->user ? $model->user->fullName : 'N/A') . '</td></tr>'
                        . '<tr><td>' . Html::encode($model->Created_at) . '</td></tr>'
                        . '</table>';
                },
            ],

                [
    'class' => kartik\grid\ActionColumn::className(),
    'template' => '{update}{verified}',
    'options' => ['width' => '80px'],
    'buttons' => [
        'update' => function ($url, $model, $key) use ($isCandoAdmin, $canStampFlowUpdate, $stampModel, $isReadOnlyView, $isViewOnlyStatus) {

            if ($isReadOnlyView || $isViewOnlyStatus) {
                return '';
            }

            if ($isCandoAdmin || $canStampFlowUpdate) {
                return Html::a('<i class="fa-pencil s16"></i>', '#', [
                    'data-toggle' => 'tooltip',
                    'class' => 'btn btn-xs tip',
                    'data-placement' => 'top',
                    'data-original-title' => 'Edit Certificate',
                    'data-url' => Yii::$app->urlManager->createAbsoluteUrl([
                        'document/stamp-flow-document/update',
                        'sid' => $model->FlowId
                    ]),
                    'onclick' => 'showFlowModal(this)'
                ]);
            }

            return '';
        },

        'verified' => function ($url, $model, $key) use (
            $canVerifiedStamp,
            $isCandoAdmin,
            $userCompanyId,
            $stampModel,
            $isReadOnlyView,
            $isViewOnlyStatus
        ) {

            if ($isReadOnlyView || $isViewOnlyStatus) {
                return '';
            }

            if (
                $isCandoAdmin ||
                (
                    $canVerifiedStamp &&
                    !in_array($stampModel->Status, ['destroyed', 'rejected', 'in_process'], true) &&
                    ($userCompanyId == 248 || (int)$stampModel->OrganizationalMaker === 2)
                )
            ) {
                $iconClass = $stampModel->Status == 'approved'
                    ? 'im-checkbox-checked'
                    : 'im-checkbox-unchecked';

                return Html::a('<i class="' . $iconClass . ' s15"></i>', '#', [
                    'data-pjax' => '0',
                    'title' => 'Verified Stamp document',
                    'data-toggle' => 'tooltip',
                    'data-placement' => 'top',
                    'data-original-title' => 'Edit Stamp Document',
                    'data-url' => Yii::$app->urlManager->createAbsoluteUrl([
                        "document/stamp-document/verified",
                        'Id' => $stampModel->StampId
                    ]),
                    'onclick' => 'confirmBox("Are you sure to change Verified?!", "Yes!", "changeVerified(' . $stampModel->StampId . ')");',
                ]);
            }

            return '';
        },
    ]
],
                                        
        ],
    ]);
    ?>
    
    <?php if (!empty($stampModel->DestroyAttachment)): ?>
    <?= Html::a(
        'دانلود مستندات امحا',
        Url::to([
            '/document/stamp-document/download-destroy-attachment',
            'sid' => $stampModel->StampId,
        ]),
        [
            'class' => 'btn btn-primary btn-sm',
            'data-pjax' => 0,
        ]
    ) ?>
<?php endif; ?>
</div>

<?php
Modal::begin([
    'id' => 'flow-modal',
    'header' => Yii::t('document', 'Create or Edit Flow Stamp'),
    'size' => Modal::SIZE_LARGE,
    'options' => ['tabindex' => ''],
]);
?>
<?php Pjax::begin(['id' => 'flow-form-pjax']); ?>
<?php Pjax::end(); ?>
<?php Modal::end(); ?>

<?php
Modal::begin([
    'id' => 'refund-modal',
    'header' => Yii::t('document', 'Refund Stamp'),
    'size' => Modal::SIZE_LARGE,
    'options' => ['tabindex' => ''],
]);
?>
<?php Pjax::begin(['id' => 'refund-form-pjax']); ?>
<?php Pjax::end(); ?>
<?php Modal::end(); ?>

<?php
$this->registerJs("$('.modal-content').draggable();");
?>

<script>
    function showFlowModal(obj) {
        ajaxProgressLoader('show', '.modal-content');
        var url = $(obj).attr('data-url');

        $.ajax({
            url: url,
            dataType: 'html'
        }).done(function (data) {
            $('#flow-form-pjax').html(data);
            $('#flow-modal').modal('show');
        }).fail(function () {
            showMsgBoxInBottomRight('خطا در بارگذاری فرم گردش نشان', 'error');
        }).always(function () {
            ajaxProgressLoader('hide', '.modal-content');
        });
    }

    function showRefundModal(obj) {
        ajaxProgressLoader('show', '.modal-content');
        var url = $(obj).attr('data-url');

        $.ajax({
            url: url,
            dataType: 'html'
        }).done(function (data) {
            $('#refund-form-pjax').html(data);
            $('#refund-modal').modal('show');
        }).fail(function () {
            showMsgBoxInBottomRight('خطا در بارگذاری فرم عودت نشان', 'error');
        }).always(function () {
            ajaxProgressLoader('hide', '.modal-content');
        });
    }
    $(document).off('beforeSubmit', '#refund-form').on('beforeSubmit', '#refund-form', function (e) {
    e.preventDefault();

    var form = $(this);
    var formData = new FormData(this);

    ajaxProgressLoader('show', '.modal-content');

    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: formData,
        dataType: 'json',
        processData: false,
        contentType: false
    }).done(function (data) {

        if (data.result) {
            $('#refund-modal').modal('hide');
            $('#refund-form-pjax').html('');

            showMsgBoxInBottomRight(data.message, 'success');

            reloadForm();
        } else {
            showMsgBoxInBottomRight(data.message, 'error');
        }

    }).fail(function () {
        showMsgBoxInBottomRight('خطا در ارتباط با سرور هنگام ثبت امحا', 'error');
    }).always(function () {
        ajaxProgressLoader('hide', '.modal-content');
    });

    return false;
});
    //تیک تایید 
    function changeVerified(Id) {
    ajaxProgressLoader('show', '#flow-grid-pjax');

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
    }).fail(function () {
        showMsgBoxInBottomRight('Error on connecting to server. <br />Plase login again and try later!', 'error');
    }).always(function () {
        ajaxProgressLoader('hide', '#flow-grid-pjax');
    });

    return false;
}
    function flowCallbackPjax(data) {
        if (data['result']) {
            $('#flow-modal').modal('hide');
            showMsgBoxInBottomRight(data['message'], 'success');
        } else {
            showMsgBoxInBottomRight(data['message'], 'error');
        }
        reloadForm();
    }

    function refundCallbackPjax(data) {
        if (data['result']) {
            $('#refund-modal').modal('hide');
            showMsgBoxInBottomRight(data['message'], 'success');
        } else {
            showMsgBoxInBottomRight(data['message'], 'error');
        }
        reloadForm();
    }

    function reloadForm() {
        $.pjax.reload({
            container: "#stamp-info-pjax",
            timeout: false,
            push: false,
            replace: false
        }).done(function () {
            $.pjax.reload({
                container: "#flow-grid-pjax",
                timeout: false,
                push: false,
                replace: false
            });
        });
    }
</script>

<?php
$this->registerCss("
.filter-btn{
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
    background:#75b9e6;
    border:solid 1px #777;
    color:rgb(255, 44, 44);
}
.filter-btn .filter-btn-blue .active{color:#fff !important;}
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
.filter-btn .filter-btn-green-light{
    background: #bee6ff;
    border: solid 1px #777;
    color: #af91e1;
    box-shadow: 4px 4px 17px 0px #666;
}
.filter-btn .filter-btn-gold .active{color:#fff !important;}

input[type=text]{height: 30px;}
.table > thead > tr > td{padding:4px;}
.table {font-family: tahoma; font-size:11px; width:100%;}
.table input[type='text']{font-family: tahoma; font-size:12px; width:100%;}
.table tr th{text-align:center;}
.table tr td{text-align: center;}
.table > tbody > tr > td,
.table > tbody > tr > th,
.table > tfoot > tr > td,
.table > tfoot > tr > th,
.table > thead > tr > td,
.table > thead > tr > th {
    padding: 3px !important;
    vertical-align: middle;
}
.box-bg-3{
    background-position: top -47px right 211px;
    background-size: 578px;
}
.table-striped tbody tr.odd {
    background-color: #ffecec;
}
.table .filters td{
    background: #f8e111;
}
.table td{
    max-height: 42px !important;
}
.table .table-responsive1{
    overflow: visible !important;
}
.table tbody > tr > td,
.table tbody > tr > th,
.table tfoot > tr > td,
.table tfoot > tr > th,
.table thead > tr > td,
.table thead > tr > th {
    border-top: 1px solid #E7E7E7;
}
#flow-grid-container .table.table-bordered thead tr{
    background:#f8e777;
    height:35px;
}
#flow-expire-grid .table.table-bordered thead tr{
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
");
?>