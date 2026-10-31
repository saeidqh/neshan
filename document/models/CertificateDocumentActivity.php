<?php

namespace app\modules\document\models;

use Yii;

/**
 * This is the model class for table "cando_table_certificate_activity_document".
 *
 * @property integer $id
 * @property integer $activityIdRef
 * @property integer $certificateIdRef
 * @property integer $base
 */
class CertificateDocumentActivity extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
            return 'cando_table_certificate_activity_document';
    }

    public static function primaryKey() {
        return ['id'];
    }

    public static function getDb() {
        return \app\components\Utility::centralDb();
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['activityIdRef', 'certificateIdRef'], 'required'],
            [['activityIdRef', 'certificateIdRef', 'base', 'id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'id',
            'activityIdRef' => 'activity',
            'certificateIdRef' => 'certificate',
            'base' => Yii::t("document", 'Base'),
            'authorizedWork' => Yii::t("document", 'Authorized Work'),
            'authorizedCapacity' => Yii::t("document", 'Authorized Capacity'),
            'approvedContract' => Yii::t("document", 'Approved Contract Point'),
        ];
    }

    public function getActivity(){
        return $this->hasOne(\app\modules\document\models\CertificateActivity::className(),['backgroundActivityTypeId'=>'activityIdRef']);
    }

    public function getDocumentBase(){
        return $this->hasMany(\app\modules\document\models\CertificateDocumentBaseType::className(),['activityDocumentIdRef'=>'id']);
    }


    public static function deleteActivityDocument($certificateId,$row){
        $db = Yii::$app->dbTehran;
        $maimDbTransaction = $db->beginTransaction();
        try {
            $documentBaseTypeTable = \app\modules\document\models\CertificateDocumentBaseType::tableName();
            $activityDocumentTable = \app\modules\document\models\CertificateDocumentActivity::tableName();
            $db = Yii::$app->dbTehran;
            $db->createCommand("delete From $documentBaseTypeTable  Where activityDocumentIdRef in (select id From $activityDocumentTable Where certificateIdRef=:certificateId AND activityIdRef=$row)")
                ->bindParam(':certificateId', $certificateId)
                ->execute();
            \app\modules\document\models\CertificateDocumentActivity::deleteAll(['certificateIdRef'=>$certificateId,'activityIdRef'=>$row]);

            $maimDbTransaction->commit();
            return true;
        } catch (Exception $ex) {
            $maimDbTransaction->rollBack();
            return false;
        }
    }

}
