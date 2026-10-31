<?php
namespace app\modules\document\models;

use Yii;

/**
 * This is the model class for table "cando_table_ims_document_keyword_related".
 * @author <mail:mortezakml@gmail.com & Tel:09366263397>
 *
 * @property id
 * @property imsIdRef
 * @property keywordIdRef
 */
class ImsDocumentKeywordRelated extends \yii\db\ActiveRecord {

    
    /**
     * @inheritdoc
    */
    public static function tableName() {
        return 'cando_table_ims_document_keyword_related';
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
            [['imsIdRef','keywordIdRef'], 'integer'],
            [['imsIdRef','keywordIdRef'], 'required'],
            [['imsIdRef','keywordIdRef'], 'unique', 'targetAttribute' => ['imsIdRef','keywordIdRef']],
        ];
    }

//    public static function find() {
////        return new ImsDocumentQuery(get_called_class());
//    }

    public function scenarios() {
        $scenario = parent::scenarios();
        $scenario['create'] = ['imsIdRef','keywordIdRef'];
        return $scenario;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('document', 'ID'),
            'imsIdRef' => Yii::t('document', 'imsIdRef'),
            'keywordIdRef' => Yii::t('document', 'keywordIdRef'),
        ];
    }

    public function getIms() {
        return $this->hasOne(ImsDocument::className(), ['id' => 'imsIdRef']);
    }

    public function getObs() {
        return $this->hasOne(ImsDocument::className(), ['id' => 'keywordIdRef']);
    }

}
