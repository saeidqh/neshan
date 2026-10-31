<style>
    .tabs-above > .nav-tabs > .active > a,
    .tabs-above > .nav-tabs > li.active > a:hover,
    .tabs-above > .nav-tabs > li.active > a:focus {
        border-color: #51dd05 #ddd #51dd05 #ddd !important;
        border-width: 2px 2px 2px 2px;
        background-color: #27ff001c;
    }

    .nav-tabs > li.active > a,
    .nav-tabs > li.active > a:focus,
    .nav-tabs > li.active > a:hover {
        color: #1600ca;
        font-weight: 600;
        border-color: #51dd05 #ddd #51dd05 #ddd !important;
        border-width: 2px 2px 2px 2px;
        background-color: #27ff001c;
    }

    .nav-tabs > li a {
        border-color: #1f48dc5c;
        border-width: 2px 1px 2px 1px;
        background: #d9edf7;
        color: #414f67;
        border-radius: 5px 5px 0 0;
        font-weight: 400;
    }

    .nav-tabs > li:hover a:hover {
        border-color: #51dd05 #51dd05 #51dd05 #51dd05 !important;
        border-width: 2px 2px 2px 2px;
        font-weight: 500;
        background-color: #27ff001c;
    }

    .stamp-box {
        background: #e4e9eb;
        font-size: 12px;
        direction: rtl;
        padding: 10px;
        border-radius: 6px;
    }

    .stamp-section {
        background: #fff;
        border: 1px solid #d8d8d8;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 15px;
        min-height: 260px;
    }

    .stamp-section h4 {
        margin-top: 0;
        margin-bottom: 15px;
        font-weight: 600;
        color: #1f3b73;
    }

    .stamp-label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #333;
    }

    .stamp-value {
        margin-bottom: 10px;
    }

    .stamp-badge {
        display: inline-block;
        padding: 4px 8px;
        margin: 2px 0;
        border-radius: 4px;
        background: #8e44ad;
        color: #fff;
        font-size: 12px;
    }

    .stamp-badge-gray {
        background: #6c757d;
    }

    .stamp-badge-green {
        background: #28a745;
    }

    .stamp-badge-brown {
        background: #8b5e3c;
    }

    .stamp-img {
        border-radius: 10px;
        border: 1px solid #bbb;
        max-width: 100%;
        height: auto;
        background: #fff;
        padding: 4px;
    }

    .stamp-img-block {
        text-align: center;
        margin-top: 10px;
        margin-bottom: 15px;
    }

    .stamp-list {
        padding-right: 18px;
        margin-bottom: 10px;
    }

    .stamp-list li {
        margin-bottom: 5px;
    }
</style>

<?php

use yii\helpers\Html;
use yii\helpers\Url; // اصلاح ۱: اضافه کردن کلاس Url

$oldStampUrl = $stampModel->getStampDocumentUrl();
$finalStampUrl = $stampModel->getFinalStampDocumentUrl();

$hasOldStamp = $stampModel->hasOldStampImage();
$hasFinalStamp = $stampModel->hasFinalStampImage();
?>

<div class="tab-pane fade active in" id="StampInfo">
    <?php \yii\widgets\Pjax::begin([
        'id' => 'stamp-info-pjax',
    ]); ?>

    <div class="stamp-box">
        <div class="row">
            <!-- Left: Images -->
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="stamp-section">

                    <?php if ($hasOldStamp && !$hasFinalStamp): ?>
                        <h4>تصویر نهایی :</h4>
                        <div class="stamp-img-block">
                            <?= Html::img($oldStampUrl, [
                                'class' => 'stamp-img',
                                'style' => 'width:250px; max-height:200px;'
                            ]); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($hasOldStamp && $hasFinalStamp): ?>
                        <div class="stamp-value">
                            <span class="stamp-label">تصویر اولیه:</span>
                            <div class="stamp-img-block">
                                <?= Html::img($oldStampUrl, [
                                    'class' => 'stamp-img',
                                    'style' => 'width:250px; max-height:200px;'
                                ]); ?>
                            </div>
                        </div>

                        <hr>

                        <div class="stamp-value">
                            <span class="stamp-label">تصویر نهایی:</span>
                            <div class="stamp-img-block">
                                <?= Html::img($finalStampUrl, [
                                    'class' => 'stamp-img',
                                    'style' => 'width:250px; max-height:200px;'
                                ]); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!$hasOldStamp && $hasFinalStamp): ?>
                        <div class="stamp-value">
                            <span class="stamp-label">تصویر نهایی:</span>
                            <div class="stamp-img-block">
                                <?= Html::img($finalStampUrl, [
                                    'class' => 'stamp-img',
                                    'style' => 'width:250px; max-height:200px;'
                                ]); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!$hasOldStamp && !$hasFinalStamp): ?>
                        <span class="stamp-badge stamp-badge-gray">هیچ تصویری ثبت نشده است</span>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Right: Details -->
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="stamp-section">
                    <h4><?= Yii::t('document', 'Stamp Details') . ' :' ?></h4>

                    <div class="stamp-value">
                        <?= Yii::t('document', 'شناسه نشان سازمانی') . ':' ?>
                        <i class="stamp-badge"><?= Html::encode($stampModel->StampId) ?></i>
                    </div>

                    <div class="stamp-value">
                        <?= Yii::t('document', 'Company') . ':' ?>
                        <i class="stamp-badge"><?= Html::encode($stampModel->obs->company->companyName ?? '') ?></i>
                    </div>

                    <div class="stamp-value">
                        <?= Yii::t('document', 'Stamp Title') . ':' ?>
                        <i class="stamp-badge"><?= Html::encode($stampModel->StampTitle) ?></i>
                    </div>
                    <div class="stamp-value">
<div class="stamp-value">
    واحد مالک:
    <i class="stamp-badge">
        <?= Html::encode($stampModel->obs->fullTitle ?? '—') ?>
    </i>
</div>
                    <div class="stamp-value">
                        <?= Yii::t('document', 'Stamp Purpose') . ':' ?>
                        <i class="stamp-badge"><?= Html::encode($stampModel->StampPurpose) ?></i>
                    </div>

                    <div class="stamp-value">
                        <?= Yii::t('document', 'Stamp Type') . ':' ?>
                        <i class="stamp-badge"><?= Html::encode($stampModel->stampType->TypeName ?? '') ?></i>
                    </div>

                    <div class="stamp-value">
                        <?= Yii::t('document', 'Stamp Code') . ':' ?>
                        <i class="stamp-badge"><?= Html::encode($stampModel->StampCode) ?></i>
                    </div>

                    <div class="stamp-value">
                        <?= Yii::t('document', 'Internal Scope') . ':' ?>
                        <ul class="stamp-list">
                            <?php if (!empty($internalScope)): ?>
                                <?php foreach ($internalScope as $scope): ?>
                                    <li class="stamp-badge stamp-badge-brown"><?= Html::encode($scope->ScopeTitle) ?></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="stamp-badge stamp-badge-gray">موردی ثبت نشده</li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="stamp-value">
                        <?= Yii::t('document', 'External Scope') . ':' ?>
                        <ul class="stamp-list">
                            <?php if (!empty($externalScope)): ?>
                                <?php foreach ($externalScope as $scope): ?>
                                    <li class="stamp-badge stamp-badge-brown"><?= Html::encode($scope->ScopeTitle) ?></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="stamp-badge stamp-badge-gray">موردی ثبت نشده</li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="stamp-value">
                        <?= Yii::t('document', 'وضعیت نشان سازمانی') . ':' ?>
                        <i class="stamp-badge"><?= Html::encode($stampModel->getStatusLabal()) ?></i>
                    </div>

                    <div class="stamp-value">
                        <?= Yii::t('document', 'توضیحات امحاء نشان سازمانی') . ':' ?>
                        <i class="stamp-badge"><?= Html::encode($stampModel->DescriptionDestroy ?: '—') ?></i>
                    </div>

                    <!-- اصلاح ۲ و ۳: تمیزکاری ساختار HTML و بستن صحیح شرط endif -->
                    <div class="stamp-value">
                        <?= Yii::t('document', 'مستندات امحاء') . ':' ?>
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
                        <?php else: ?>
                            <span class="stamp-badge stamp-badge-gray">فایلی ثبت نشده</span>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <?php \yii\widgets\Pjax::end(); ?>
</div>