<?php
namespace app\modules\document\models;

use Yii;

/**
 * @author <mail:mortezakml@gmail.com & Tel:09366263397>
 *
 * @property integer backgroundActivityTypeId
 * @property string backgroundActivityTitle
 * @property string maxBase
 */
class CertificateActivity extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
    */

    public static function tableName() {
        return 'cando_table_certificate_activity';
    }

    public static function getDb() {
        return \app\components\Utility::centralDb();
    }

    public static function primaryKey() {
        return['backgroundActivityTypeId'];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['backgroundActivityTitle'], 'string'],
            [['backgroundActivityTypeId', 'maxBase'], 'integer'],
        ];
    }



    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'backgroundActivityTypeId' => Yii::t('document', 'background Activity Type Id'),
            'backgroundActivityTitle' => Yii::t('document', 'background Activity Title'),
            'maxBase' => Yii::t('document', 'max base'),
        ];
    }

}
