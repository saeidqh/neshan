<?php
namespace app\modules\document\models;

use Yii;

/**
 * This is the model class for table "cando_table_ims_document_related".
 * @author <mail:mortezakml@gmail.com & Tel:09366263397>
 *
 * @property id
 * @property from
 * @property to
 */
class ImsDocumentRelated extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
    */
    public static function tableName() {
        return 'cando_table_ims_document_related';
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
            [['from','to'], 'integer'],
            [['from','to'], 'required'],
            [['from','to'], 'unique', 'targetAttribute' => ['from','to']],
        ];
    }

//    public static function find() {
////        return new ImsDocumentQuery(get_called_class());
//    }

    public function scenarios() {
        $scenario = parent::scenarios();
        $scenario['create'] = ['from','to'];
        return $scenario;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('document', 'ID'),
            'from' => Yii::t('document', 'from'),
            'to' => Yii::t('document', 'to'),
            
        ];
    }


    public function getfrom() {
        return $this->hasOne(ImsDocument::className(), ['id' => 'from']);
    }
    
    public function getTo() {
        return $this->hasOne(ImsDocument::className(), ['id' => 'to']);
    }

}
