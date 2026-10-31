<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\PageHeader;


use omnilight\assets\SweetAlertAsset;

SweetAlertAsset::register($this);


//$canCertificateUpdate = \app\access\AccessControl::can("document/certificate-document/edit");
//$canCertificateDelete = \app\access\AccessControl::can("document/certificate-document/delete");


$this->title = Yii::t('document', 'Certificate List');


?>




<div class='col-md-12 col-lg-12'>

    <?=
    \kartik\grid\GridView::widget([
        'id' => 'document-base-type-grid',
        'dataProvider' => $certificateDocumentActivityDataProvider,
//                    'filterModel' => $contractRevisionDocumentModel,
        'pjax' => true,
        'pjaxSettings' => [
            'options' => [
                'id' => 'document-base-type-grid-pjax',
            ]
        ],
        'columns' => array(

            [
                'attribute' => 'activityTitle',
                'value' => 'activityTitle',
                'label' => Yii::t("document", 'Activity Title')
            ],
            [
                'attribute' => 'base',
                'value' => 'base',
                'label' => Yii::t("document", 'Base')
            ],
            [
                'attribute' => 'authorizedWork',
                'value' => 'authorizedWork',
                'label' => Yii::t("document", 'Authorized Work')
            ],
            [
                'attribute' => 'authorizedCapacity',
                'value' => 'authorizedCapacity',
                'label' => Yii::t("document", 'Authorized Capacity')

            ],
            [
                'attribute' => 'approvedContract',
                'value' => 'approvedContract',
                'label' => Yii::t("document", 'Approved Contract Point')
            ],
            [
                'class' => kartik\grid\ActionColumn::className(),
                'template' => '{edit} {delete}',
                'options' => [
                    'width' => '100px;',
                ],
                'buttons' => [
                    'edit' => function($url, $model, $key)use( $certificateDocumentActivityDataProvider) {
                            return Html::a('<i class="im-pencil s16"></i>', '#', [
                                    'data-toggle' => 'tooltip',
                                    'data-placement' => 'top',
                                    'data-original-title' => 'Edit Base Type item',
                                    'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/certificate-document/edit-base-type-document', 'adid' => $model->activityDocumentId]),
                                    'onclick' => 'showDocumentActivityBaseTypeModal(this)',
                                    'data-pjax' => '0'
                                ]) . '&nbsp;';
                    },

                    'delete' => function ($key, $model, $index)  {
                        if (true) {
                            return Html::a('<i class="fa-trash s16"></i>', '#', [
                                        'class' => 'delete-certificate-' . $model->activityDocumentId,
                                        'data-toggle' => 'tooltip',
                                        'data-placement' => 'top',
                                        'data-original-title' => 'Remove Base Type item',
                                        'onclick' => 'confirmBox("Are you sure to delete this item?!", "Delete!", "deleteCertificateDocumentBase(\'' . $model->activityDocumentId . '\')");',
                                        'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/certificate-document/delete-base-type-document', 'adid' => $model->activityDocumentId])
                                    ]
                                ) . '&nbsp;';
                        }
                    },


                ]
            ]
        ),
    ]);
    ?>
</div>
</div>


<!-- Load List-->




<script>

    function deleteCertificateDocumentBase(activityDocumentId) {
        var id = $('.delete-certificate-' + activityDocumentId).attr('data-id');
        var url = $('.delete-certificate-' + activityDocumentId).attr('data-url');
        $.ajax({
            url: url,
        }).done(function (data) {
            if (data.result == 1) {
                $.pjax.reload({
                    container: '#document-base-type-grid-pjax',
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
.filter-btn .filter-btn-purple .active{color:rgb(255, 102, 0) !important;}


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
.contract-state-blue{background: rgb(0, 96, 255);}
.contract-state-orange{background: rgb(255, 196, 0);}
.contract-state-purple{background: rgb(255, 0, 255);}
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
        #responsibility-grid-container .table.table-bordered thead tr{
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

