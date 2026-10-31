<?php
namespace app\modules\document\models;

use Yii;

/**
 * This is the model class for table "cando_table_ims_document_related".
 * @author <mail:mortezakml@gmail.com & Tel:09366263397>
 *
 * @property id
 * @property imsIdRef
 * @property obsIdRef
 */
class ImsDocumentOBSRelated extends \yii\db\ActiveRecord {

    
    /**
     * @inheritdoc
    */
    public static function tableName() {
        return 'cando_table_ims_document_obs_related';
    }

    public static function getDb() {
        return \app\components\Utility::centralDb();
    }
    
    public static function primaryKey() {
        return['id'];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['imsIdRef','obsIdRef'], 'integer'],
            [['imsIdRef','obsIdRef'], 'required'],
            [['imsIdRef','obsIdRef'], 'unique', 'targetAttribute' => ['imsIdRef','obsIdRef']],
        ];
    }

//    public static function find() {
////        return new ImsDocumentQuery(get_called_class());
//    }

    public function scenarios() {
        $scenario = parent::scenarios();
        $scenario['create'] = ['imsIdRef','obsIdRef'];
        return $scenario;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('document', 'ID'),
            'imsIdRef' => Yii::t('document', 'imsIdRef'),
            'obsIdRef' => Yii::t('document', 'obsIdRef'),
            
        ];
    }

    public function getIms() {
        return $this->hasOne(ImsDocument::className(), ['id' => 'imsIdRef']);
    }
    
    public function getObs() {
        return $this->hasOne(ImsDocument::className(), ['id' => 'obsIdRef']);
    }

}
