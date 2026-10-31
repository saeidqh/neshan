<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\GridViewBox;

$canCreateAndUpdateActivity = app\access\AccessControl::can('document/clause/create-activity');
?>

<style>
    #clause-activity-grid-pjax thead,
    #clause-activity-grid-pjax .summary {
        background-color: #d61d5bff;
    }
</style>

<?php GridViewBox::begin([]); ?>
<div class="row">
    <div class="col-md-4 pull-right text-right grid-box-buttons">
        <div class="btn-group">
            <?php if ($canCreateAndUpdateActivity) { ?>
                <button class="btn btn-sm btn-primary pull-right ml5"
                        data-url="<?= Yii::$app->urlManager->createAbsoluteUrl(["document/clause/create-activity", 'clauseIdRef' => $clauseIdRef]) ?>"
                        onclick="showClauseActivityModal(this)"><i
                        class=""></i> <?= Yii::t('document', 'New Activity') ?> </button>
                    <?php
                }
                ?>
        </div>
    </div>
</div>
<?=
\kartik\grid\GridView::widget([
    'dataProvider' => $activityArrayDataProvider,
    'pjax' => true,
    'pjaxSettings' => [
        'options' => [
            'id' => 'clause-activity-grid-pjax',
        ],
    ],
    'columns' => [
        [
            'attribute' => 'id',
            'label' => Yii::t('document', 'ID'),
        ],
        [
            'attribute' => 'title',
            'label' => Yii::t('document', 'Clause Activity Title'),
        ],
        [
            'attribute' => 'extention',
            'label' => Yii::t('document', 'Extention'),
        ],
        [
            'attribute' => 'maxSize',
            'label' => Yii::t('document', 'Max Size'),
            'options' => [
                'width' => '80px'
            ],
        ],
        [
            'attribute' => 'file',
            'label' => Yii::t('document', 'Sample File'),
            'format' => 'raw',
            'value' => function ($data) {
//                return $data['fileName'];
                if (!empty($data['fileName'])) {
                    return Html::a('&nbsp;<i class="fa-circle-arrow-down s16" style="color: #c32bc5;"></i>', Yii::$app->urlManager->createAbsoluteUrl(["document/clause/download-activity", 'id' => $data['id']]), [
                                'data-toggle' => 'tooltip',
                                'data-placement' => 'top',
                                'target' => '_blank',
                                'data-original-title' => 'Download File',
                                'data-pjax' => 0
                    ]);
                }
            },
            'options' => [
                'width' => '60px'
            ],
        ],
        [
            'attribute' => 'completeMethod',
            'label' => Yii::t('document', 'Complete Method'),
            'format' => 'raw',
            'value' => function ($model) {
                return Yii::$app->utility->generateTextShorterSpan($model['completeMethod'], 30, 50);
            },
        ],
        [
            'class' => '\kartik\grid\ActionColumn',
            'template' => '{update}{delete}{delete-file}',
            'buttons' => [
                'update' => function ($key, $model, $index) {
                    return \yii\helpers\Html::a('<i class="fa-pencil s16"></i>', '#', [
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
                        'data-original-title' => 'Edit Activity',
                        'data-url' => Yii::$app->urlManager->createAbsoluteUrl(['document/clause/update-activity', 'id' => $model['id']]),
                        'onclick' => 'showClauseActivityModal(this)'
                            ]
                    );
                },
                'delete' => function ($url, $model, $key) {
                    return \yii\helpers\Html::a('&nbsp;&nbsp;&nbsp;<i class="fa-trash s16"></i>', '#', [
                        'id' => 'btn-del-activity-' . $model['id'],
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
                        'style' => 'color:#EC0000;',
                        'data-id' => $model['id'],
                        'title' => 'حذف فعالیت',
                        'onclick' => 'confirmBox("Are you sure to delete this file?!", "Delete!", "deleteActivity(' . $model['id'] . ')");',
                        'data-url' => Yii::$app->urlManager->createAbsoluteUrl(["document/clause/delete-activity", 'id' => $model['id']]),
                    ]);
                },
                'delete-file' => function ($url, $model, $key) {
                    if (isset($model['fileName'])) {
                        return \yii\helpers\Html::a('&nbsp;&nbsp;&nbsp;<i class="im-close s16"></i>', '#', [
                                    'id' => 'btn-del-activity-file-' . $model['id'],
                                    'data-toggle' => 'tooltip',
                                    'data-placement' => 'top',
                                    'style' => 'color:#EC0000;',
                                    'data-id' => $model['id'],
                                    'title' => 'حذف فایل نمونه',
                                    'onclick' => 'confirmBox("Are you sure to delete this Activity?!", "Delete!", "deleteActivityFile(' . $model['id'] . ')");',
                                    'data-url' => Yii::$app->urlManager->createAbsoluteUrl(["document/clause/delete-activity-file", 'id' => $model['id']]),
                        ]);
                    }
                },
            ],
        ]
    ],
]);
?>

<?php GridViewBox::end(); ?>