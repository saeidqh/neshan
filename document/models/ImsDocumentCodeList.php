<?php
namespace app\modules\document\models;

use Yii;

/**
 * @author <mail:mortezakml@gmail.com & Tel:09366263397>
 * 
 * @property id
 * @property title
 */
class ImsDocumentCodeList extends \yii\db\ActiveRecord {
    
    /**
     * @inheritdoc
    */
    
    public static function tableName() {
        return 'cando_table_ims_document_code_list';
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
//            [['title'], 'string','min'=>18,'max'=>19],
            [['title'], 'string'],
        ];
    }


    public function scenarios() {
        $scenario = parent::scenarios();
        $scenario['create'] = ['id','title'];
        $scenario['update'] = ['id','title'];
        return $scenario;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('document', 'ID'),
            'title' => Yii::t('document', 'Title'),
        ];
    }

}
