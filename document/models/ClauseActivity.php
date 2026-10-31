<?php

namespace app\modules\document\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "dbo.tblClauseActivity".
 *
 * @property integer $id
 * @property string $title
 * @property string $extention
 * @property string $similarActivity
 * @property integer $clauseIdRef
 * @property integer $maxSize
 * @property string $fileName
 * @property string $completeMethod
 */
class ClauseActivity extends \yii\db\ActiveRecord {
    use \app\components\DocumentTraitPro;
    
    public function getConfigDoc() {
        return [
            'folder' => 'clauseActivity',
            'files'=>[
                'file'=>null,
            ],
        ];
    }

    public $file;
    public $standard;
    public $extentionArray;
    public $similarActivityArray;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        if (Yii::$app->params['dmbs'] == 'mysql') {
            return 'cando_table_ims_clause_activity';
        } else {
            return 'dbo.tblClauseActivity';
        }
    }

    public static function primaryKey() {
        return ['id'];
    }

    public static function getDb() {
        return \app\components\Utility::centralDb();
    }

    public function rules() {
        return [
            [['title', 'clauseIdRef'], 'required'],
            [['clauseIdRef', 'maxSize'], 'integer'],
            [['title', 'extention', 'similarActivity', 'completeMethod'], 'string'],
            [['fileName'], 'string', 'max' => 255],
            [['extentionArray', 'similarActivityArray'], 'each', 'rule' => ['safe']],
            ['file', 'file', 'skipOnEmpty' => true]
        ];
    }

    public function scenarios() {
        $scenario = parent::scenarios();
        return $scenario;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('document', 'ID'),
            'title' => Yii::t('document', 'Clause Activity Title'),
            'clauseIdRef' => Yii::t('document', 'Clause'),
            'extentionArray' => Yii::t('document', 'Extention'),
            'similarActivityArray' => Yii::t('document', 'Similar Activity'),
            'maxSize' => Yii::t('document', 'Max Size'),
            'file' => Yii::t('document' , 'Sample File'),
            'completeMethod' => Yii::t('document' , 'Complete Method'),
        ];
    }

    public function beforeValidate() {
        $this->extention = !empty($this->extentionArray) ? implode(',', $this->extentionArray) : null;
        $this->similarActivity = !empty($this->similarActivityArray) ? implode(',', $this->similarActivityArray) : null;
        return parent::beforeValidate($this);
    }

    public static function getExtensionList() {
        return ['xls' => 'xls', 'xlsx' => 'xlsx', 'xlsb' => 'xlsb', 'zip' => 'zip', 'rar' => 'rar', 'doc' => 'doc', 'docx' => 'docx', 'ppt' => 'ppt', 'pptx' => 'pptx', 'pps' => 'pps', 'ppsx' => 'ppsx', 'pdf' => 'pdf', 'png' => 'png', 'jpg' => 'jpg', 'jpeg' => 'jpeg'];
    }

    public function getClause() {
        return $this->hasOne(Clause::className(), ['id' => 'clauseIdRef']);
    }

    public function saveActivity($oldSimilarActivity = null) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->file = \yii\web\UploadedFile::getInstance($this, "file");
            if ($this->file instanceof \yii\web\UploadedFile) {
                $this->fileName = $this->file->name;
            }
            if (empty($this->similarActivity) && empty($oldSimilarActivity)) {
                if ($this->save()) {
                    if ($this->file instanceof \yii\web\UploadedFile) {
                        $this->hardDeleteFile();
                        $this->upload();
                    }
                    $transaction->commit();
                    return true;
                }
            }
            
            $oldSimilarActivityArray = !empty($oldSimilarActivity) ? explode(',', $oldSimilarActivity) : [];
            $newSimilarActivityArray = !empty($this->similarActivityArray) ? $this->similarActivityArray : [];

            $similarActivityToAdd = array_diff($newSimilarActivityArray, $oldSimilarActivityArray);
            $similarActivityToDelete = array_diff($oldSimilarActivityArray, $newSimilarActivityArray);

            if(count($similarActivityToDelete) == 1) {
                $clauseActivity = self::find()->where(['id' => $similarActivityToDelete[0]])->one();
                $clauseActivity->similarActivity = null;
                $clauseActivity->save(false);
            } else if(count($similarActivityToDelete) > 1) {
                foreach ($similarActivityToDelete as $similarActivity) {
                    $clauseActivity = self::find()->where(['id' => $similarActivity])->one();
                    $clauseActivity->similarActivity = implode(',', array_diff($similarActivityToDelete, [$similarActivity]));
                    $clauseActivity->save(false);
                }
            }

            if(count($newSimilarActivityArray) == 0) {
                $this->similarActivity = null;
                if ($this->save()) {
                    if ($this->file instanceof \yii\web\UploadedFile) {
                        $this->hardDeleteFile();
                        $this->upload();
                    }
                    $transaction->commit();
                    return true;
                } else {
                    $transaction->rollBack();
                    return false;
                }
            } else {
                $res = $this->save();
                if (!$res) {
                    $transaction->rollBack();
                    return false;
                }
                if ($this->file instanceof \yii\web\UploadedFile) {
                    $this->hardDeleteFile();
                    $this->upload();
                }
                array_push($newSimilarActivityArray, $this->id);
                foreach ($similarActivityToAdd as $similarActivity) {
                    $clauseActivity = self::find()->where(['id' => $similarActivity])->one();
                    if(!empty($clauseActivity->similarActivity)) {
                        $newSimilarActivityArray = array_unique(array_merge($newSimilarActivityArray, explode(',', $clauseActivity->similarActivity)));
                    }
                }
                
                foreach ($newSimilarActivityArray as $similarActivity) {
                    $clauseActivity = self::find()->where(['id' => $similarActivity])->one();
                    $clauseActivity->similarActivity = implode(',', array_diff($newSimilarActivityArray, [$similarActivity]));
                    $clauseActivity->maxSize = !empty($this->maxSize) ? $this->maxSize : $clauseActivity->maxSize;
                    $clauseActivity->extention = !empty($this->extention) ? $this->extention : $clauseActivity->extention;
                    $clauseActivity->save(false);
                }
            }

            $transaction->commit();
            return true;
        } catch (Exception $exc) {
            $transaction->rollBack();
            echo $exc->getTraceAsString();
            return false;
        }
    }
}
