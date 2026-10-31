<?php 
use app\components\Utility;
use yii\helpers\Html;
?>
<div class="row">
    <div class="col-md-6">
        <?= kartik\detail\DetailView::widget([
            'id' => 'budget-detail',
            'model' => $imsDocumentModel,
            'attributes' => [
                [
                    'attribute'=>'type',
                    'displayOnly'=>true,
                    'value'=>$imsDocumentModel->typeArray()[$imsDocumentModel->type],
                ],
                [
                    'attribute'=>'title',
                    'displayOnly'=>true,
                    'value'=>Html::encode($imsDocumentModel->title),
                ],
                [
                    'attribute'=>'Company',
                    'displayOnly'=>true,
                    'value'=>Html::encode($imsDocumentModel->company->PersonTitle),
                ],
                [
                    'attribute'=>'status',
                    'displayOnly'=>true,
                    'format'=>'raw',
                    'value'=>$imsDocumentModel->status ? '<span class="badge badge-success">'.$imsDocumentModel->statusArray()[$imsDocumentModel->status].'</span>' : '<span class="badge badge-danger">'.$imsDocumentModel->statusArray()[$imsDocumentModel->status].'</span>',
                ],
                [
                    'attribute'=>'revisionDate',
                    'displayOnly'=>true,
                    'format'=>'raw',
                    'value'=>function()use($imsDocumentModel){
                        if(isset($imsDocumentModel->revisionDate)){
                                    return
                                    '<table class="inner-table">'
                                    . '<tr><td width=\'200\'>'
                                    . $imsDocumentModel->revisionDate
                                    . '<tr class="top-border"><td >'
                                    . Utility::convertDate($imsDocumentModel->revisionDate,true)
                                    . '</table>';

                        }else{
                            return '';
                        }
                    }
                ],
                [
                    'attribute'=>'effectiveDate',
                    'displayOnly'=>true,
                    'format'=>'raw',
                    'value'=> function()use($imsDocumentModel){
                        if(isset($imsDocumentModel->effectiveDate)){
                                    return
                                    '<table class="inner-table">'
                                    . '<tr><td width=\'200\'>'
                                    . $imsDocumentModel->effectiveDate
                                    . '<tr class="top-border"><td >'
                                    . Utility::convertDate($imsDocumentModel->effectiveDate,true)
                                    . '</table>';

                        }else{
                            return '';
                        }
                    }
                ],
                [
                    'attribute'=>'createdBy',
                    'displayOnly'=>true,
//                    'value'=>  isset($imsDocumentModel->imsCreatedBy->UserName)? Html::encode($imsDocumentModel->imsCreatedBy->UserName):'',
                    'value'=>  isset($imsDocumentModel->imsCreatedBy)? Html::encode($imsDocumentModel->imsCreatedBy->fullName):'',
                ],
                [
                    'attribute'=>'createdAt',
                    'displayOnly'=>true,
                    'format'=>'raw',
                    'value'=>function()use($imsDocumentModel){
                        if(isset($imsDocumentModel->createdAt)){
                                    return
                                    '<table class="inner-table">'
                                    . '<tr><td width=\'200\'>'
                                    . $imsDocumentModel->createdAt
                                    . '<tr class="top-border"><td >'
                                    . Utility::convertDate($imsDocumentModel->createdAt,true)
                                    . '</table>';

                        }else{
                            return '';
                        }
                    }
                ],
            ],
        ]);
        ?>
    </div>
    <div class="col-md-6">
        <?= kartik\detail\DetailView::widget([
            'id' => 'budget-detail',
            'model' => $imsDocumentModel,
            'attributes' => [
                [
                    'attribute'=>'code',
                    'displayOnly'=>true,
                    'value'=>Html::encode($imsDocumentModel->code),
                ],
                [
                    'attribute'=>'revisionNumber',
                    'displayOnly'=>true,
                ],
                [
                    'attribute'=>'preparatorUnit',
                    'displayOnly'=>true,
                    'value'=>!empty($imsDocumentModel->obs->obsTitle) ? Html::encode($imsDocumentModel->obs->obsTitle) : '',
                ],
                [
                    'attribute'=>'preparationDate',
                    'displayOnly'=>true,
                    'format'=>'raw',
                    'value'=>function()use($imsDocumentModel){
                        if(isset($imsDocumentModel->preparationDate)){
                                    return
                                    '<table class="inner-table">'
                                    . '<tr><td width=\'200\'>'
                                    . $imsDocumentModel->preparationDate
                                    . '<tr class="top-border"><td >'
                                    . Utility::convertDate($imsDocumentModel->preparationDate,true)
                                    . '</table>';

                        }else{
                            return '';
                        }
                    }
                ],
                [
                    'attribute'=>'reportfile',
                    'displayOnly'=>true,
                ],
                [
                    'attribute'=>'nativefile',
                    'displayOnly'=>true,
                ],
                [
                    'attribute'=>'updatedBy',
                    'displayOnly'=>true,
                    //                    'value'=>  isset($imsDocumentModel->imsCreatedBy->UserName)? Html::encode($imsDocumentModel->imsCreatedBy->UserName):'',
                    'value'=>  isset($imsDocumentModel->imsCreatedBy)? Html::encode($imsDocumentModel->imsCreatedBy->fullName):'',
                ],
                [
                    'attribute'=>'updatedAt',
                    'displayOnly'=>true,
                    'format'=>'raw',
                    'value'=>function()use($imsDocumentModel){
                        if(isset($imsDocumentModel->updatedAt)){
                                    return
                                    '<table class="inner-table">'
                                    . '<tr><td width=\'200\'>'
                                    . $imsDocumentModel->updatedAt
                                    . '<tr class="top-border"><td >'
                                    . Utility::convertDate($imsDocumentModel->updatedAt,true)
                                    . '</table>';

                        }else{
                            return '';
                        }
                    }
                ],
            ],
        ]);
        ?>
    </div>
</div>
<style>
    table tbody tr th{
        width: 40% !important;
    }
</style>

