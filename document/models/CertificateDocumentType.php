<?php
namespace app\modules\document\models;

use Yii;

/**
 * @author <mail:mortezakml@gmail.com & Tel:09366263397>
 *
 * @property integer certificateTypeId
 * @property string certificateTitle
 * @property string certificateTypeParentIds
 */
class CertificateDocumentType extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
    */

    public static function tableName() {
        return 'cando_table_certificate_document_type';
    }

    public static function getDb() {
        return \app\components\Utility::centralDb();
    }

    public static function primaryKey() {
        return['certificateTypeId'];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['certificateTitle'], 'string'],
            [['certificateTypeId', 'certificateTypeParentId'], 'integer'],
        ];
    }



    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'certificateTypeId' => Yii::t('document', 'certificate Type Id'),
            'certificateTitle' => Yii::t('document', 'certificate Title'),
            'certificateTypeParentId' => Yii::t('document', 'certificate Type Parent Id'),
        ];
    }

}
