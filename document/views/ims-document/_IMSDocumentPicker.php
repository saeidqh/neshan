<?php
use yii\grid\GridView;
use app\components\Utility;
use yii\helpers\Html;
?>
<?= GridView::widget([
        'id' => 'gridview-cp-contract-picker-list',
        'filterModel' => $imsDocumentSearchModel,
        'dataProvider' => $imsDocumentDataProvider,
        'columns' => [
            [
                'attribute'=>'id',
                'options'=>array(
                    'width'=>'30'
                ),
            ],
            [
                'attribute'=>'title',
                'options'=>array(
                    'width'=>'130'
                ),
            ],
            [
                'attribute'=>'code',
                'options'=>array(
                    'width'=>'130'
                ),
            ],
            [
                'attribute'=>'companyTitle',
                'value'=>'company.PersonTitle',
                'options'=>array(
                    'width'=>'130'
                ),
            ],
            [
                'format'=>'raw',
                'value'=>function($data) {
                    return '<a class="btn btn-xs contract-picker-select"'
                            . ' data-subcontractor="'.str_replace("'",'\"',$data['id']).'"'
                            . ' data-contractno="'.str_replace("'",'\"',$data['id']).'"'
                            . ' data-contractsubject="'.str_replace("'",'\"',$data['id']).'"'
                            . ' data-contractprice1="'.str_replace("'",'\"',$data['id']).'"'
                            . ' data-contractcurrency1="'.str_replace("'",'\"',$data['id']).'"'
                            . ' data-contractprice2="'.str_replace("'",'\"',$data['id']).'"'
                            . ' data-contractcurrency2="'.str_replace("'",'\"',$data['id']).'"'
                            . ' data-hasadjustment="'.str_replace("'",'\"',$data['id']).'"'
                            . ' data-contractid="'.str_replace("'",'\"',$data['id']).'"'
                            . ' style="color:#85D149;" pid="'.$data['id'].'" cNo="'.$data['id'].'" key="'.$data['id'].'"'
                            . ' desc=\'<b>Contract No:&nbsp;</b>'.str_replace("'","\"",$data['id']).'<br />'
                            . ' <b>Contract Subject:&nbsp;</b>'.str_replace("'",'\"',$data['id']).'<br />'
                            . ' <b>Sub Contractor:&nbsp;</b>'.str_replace("'",'\"',$data['id']).'<br />'
                            . '\' ><i class="im-checkmark2"></i></a>';
                },
                'options'=>array(
                    'width'=>'30'
                ),
                
            ],
            
          
        ],
    ]);