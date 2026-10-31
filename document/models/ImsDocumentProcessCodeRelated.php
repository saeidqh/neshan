<?php
namespace app\modules\document\models;

use Yii;

/**
 * This is the model class for table "cando_table_ims_document_process_code__related".
 * @author <mail:mortezakml@gmail.com & Tel:09366263397>
 *
 * @property id
 * @property imsIdRef
 * @property processCodeIdRef
 */
class ImsDocumentProcessCodeRelated extends \yii\db\ActiveRecord {

    
    /**
     * @inheritdoc
    */
    public static function tableName() {
        return 'cando_table_ims_document_process_code_related';
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
            [['imsIdRef','processCodeIdRef'], 'integer'],
            [['imsIdRef','processCodeIdRef'], 'required'],
            [['imsIdRef','processCodeIdRef'], 'unique', 'targetAttribute' => ['imsIdRef','processCodeIdRef']],
        ];
    }

    public function scenarios() {
        $scenario = parent::scenarios();
        $scenario['create'] = ['imsIdRef','processCodeIdRef'];
        return $scenario;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('document', 'ID'),
            'imsIdRef' => Yii::t('document', 'imsIdRef'),
            'processCodeIdRef' => Yii::t('document', 'processCodeIdRef'),
            
        ];
    }

    public function getIms() {
        return $this->hasOne(ImsDocument::className(), ['id' => 'imsIdRef']);
    }
    
    public function getProcessCode() {
        return $this->hasOne(ImsDocumentProcessCode::className(), ['id' => 'processCodeIdRef']);
    }

}
