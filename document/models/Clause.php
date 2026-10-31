<?php

namespace app\modules\document\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "dbo.tblClause".
 *
 * @property integer $id
 * @property string $title
 * @property string $description
 * @property integer $type
 * @property integer $mainClause
 * @property integer $parentIdRef
 * @property integer $subClause
 * @property string $fullClauseNo
 * @property integer $codifiedInformation
 * @property integer $isActive
 * @property float $weight
 * @property integer $sort
 */
class Clause extends \yii\db\ActiveRecord {

    public $standard;
    /**
     * @inheritdoc
     */
    public static function tableName() {
        if (Yii::$app->params['dmbs'] == 'mysql') {
            return 'cando_table_ims_clause';
        } else {
            return 'dbo.tblClause';
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
            [['title', 'type', 'mainClause', 'codifiedInformation', 'description', 'standard'], 'required'],
            [['type', 'mainClause', 'parentIdRef', 'subClause', 'codifiedInformation', 'isActive', 'sort'], 'integer'],
            [['title', 'fullClauseNo', 'description'], 'string'],
            // [['mainClause'], 'unique', 'targetAttribute' => ['mainClause', 'parentIdRef'], 'message' => 'شماره بند تکراری است !'],
            [['weight'], 'number', 'min' => 0.0,  'max' => 100],
            [['standard'], 'safe'],
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
            'title' => Yii::t('document', 'Clause Name'),
            'standard' => Yii::t('document', 'Standard/Model'),
            'type' => Yii::t('document', 'Clause Type'),
            'mainClause' => Yii::t('document', 'ClauseNo'),
            'subClause' => Yii::t('document', 'Sub Clause'),
            'fullClauseNo' => Yii::t('document', 'Full ClauseNo'),
            'codifiedInformation' => Yii::t('document', 'Codified Information'),
            'description' => Yii::t('document', 'Description'),
            'isActive' => Yii::t('document', 'Active'),
            'weight' => Yii::t('document', 'Weight'),
            'sort' => Yii::t('document', 'sort'),
        ];
    }

    public function beforeValidate() {
        // $this->standard = ($this->parentIdRef > 0) ? ArrayHelper::getColumn($this->parent->standards, 'id') : $this->standard;
        return parent::beforeValidate($this);
    }

    public static function getTypeList() {
        return [
            1 => 'ایزو',
            2 => 'راهنما'
        ];
    }

    public static function getType($type) {
        switch ($type) {
            case 1:
                return 'ایزو';
            case 2:
                return 'راهنما';
            default:
                return 'invalid Type';
        }
    }

    public static function getCodifiedInformationList() {
        return [
            1 => 'مستند',
            2 => 'سابقه',
            3 => 'الزام'
        ];
    }

    public static function getCodifiedInformation($information) {
        switch ($information) {
            case 1:
                return 'مستند';
            case 2:
                return 'سابقه';
            case 3:
                return 'الزام';    
            default:
                return 'invalid Codified Information';
        }
    }
    
    public static function getIsActiveList()
    {
        return [
            0 => Yii::t('automation', 'In Active'),
            1 => Yii::t('automation', 'Active')
        ];
    }

    public static function getIsActive($status)
    {
        switch ($status){
            case 0:
                return Yii::t('automation', 'In Active');
            case 1:
                return Yii::t('automation', 'Active');
            default:
                return Yii::t('automation', 'All');
        }
    }

    public function validateParent($attribute) {
        if (!$this->isNewRecord && $this->parentIdRef == $this->id && $this->isActive ) {
            $this->addError($attribute, 'Invalid Parent');
        }
    }
    
    public function getStandards()
    {
        return $this->hasMany(Standard::class, ['id' => 'StandardIdRef'])
            ->viaTable('cando_table_ims_standard_clause', ['ClauseIdRef' => 'id']);
    }
    
    public function getParent() {
        return $this->hasOne(Clause::className(), ['id' => 'parentIdRef']);
    }
    
    public function getActivity() {
        return $this->hasOne(ClauseActivity::className(), ['clauseIdRef' => 'id']);
    }
    
    public function saveClause()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->fullClauseNo = $this->clauseNumber;
            if ($this->save()) {
                $newStandards = $this->standard;
                $oldStandards = ArrayHelper::getColumn($this->standards, 'id');
                
                $standardToAdd = array_diff($newStandards, $oldStandards);
                $standardToDelete = array_diff($oldStandards, $newStandards);
                
                if($standardToDelete) {
                    StandardClause::deleteAll([
                            'ClauseIdRef' => $this->id,
                            'StandardIdRef' => $standardToDelete
                        ]);
                }
                
                if ($standardToAdd) {
                    foreach ($standardToAdd as $standard) {
                        $standardClause = new StandardClause();
                        $standardClause->ClauseIdRef = $this->id;
                        $standardClause->StandardIdRef = $standard;
                        $standardClause->save();
                    }
                }
            } else {
                return false;
            }
            $transaction->commit();
            return true;
        } catch (Exception $exc) {
            $transaction->rollBack();
            echo $exc->getTraceAsString();
        }
    }

    public function getClauseNumber() {
        if(empty($this->parentIdRef)) return $this->mainClause;
        $clauseNo = $this->mainClause;
        $parent = $this->parent;
        while (!empty($parent->parentIdRef)) {
            $clauseNo = $parent->mainClause . '-' . $clauseNo;
            $parent = $parent->parent;
        }
        $clauseNo = $parent->mainClause . '-' . $clauseNo;
        
        return $clauseNo;
    }

    public function getStandardTitle() {
        return implode(' | ', ArrayHelper::getColumn($this->standards, 'title'));
    }

    public static function clauseArray() {
        $clauses = Clause::find()->where(['isActive' => 1])->orderBy('sort asc')->all();
        foreach ($clauses as &$clause) {
            $clause->title = $clause->fullClauseNo . ' - ' . $clause->title . ' - ' . implode(' | ', ArrayHelper::getColumn($clause->standards, 'title'));
        }
        $clauseArray = \yii\helpers\ArrayHelper::map($clauses, 'id', 'title');
        return $clauseArray;
    }

}
