<?php

namespace app\modules\document\models;

use Yii;

/**
 * This is the ActiveQuery class for [[ImsDocument]].
 * @author <mail:mortezakml@gmail.com & Tel:09366263397>
 *
 * @see ImsDocumentQuery
 */
class ImsDocumentQuery extends \yii\db\ActiveQuery {

    public function all($db = null) {
        return parent::all($db);
    }

    public function one($db = null) {
        return parent::one($db);
    }

    public function company() {
        $imsDocumentTable = \app\modules\document\models\ImsDocument::tableName();
        $personTable = \app\modules\OBS\models\Person::tableName();
        $userObsFormTable = \app\modules\account\models\UserOBSFormAction::tableName();
        
        $userId = Yii::$app->user->id;
        $obsId = Yii::$app->session->get('ObsPosition');
        $aid = 387; //مشاهده اسناد شرکت های تابعه
        
        $userOBSFormActionQuery = \app\modules\account\models\UserOBSFormAction::find()
                                    ->where(['UserIDRef' => $userId, 'OBSCategory3IDRef' => $obsId, 'FormActionIDRef' => $aid, 'AccessValue' => 1])
                                    ->andWhere("($userObsFormTable.isDeleted is null or $userObsFormTable.isDeleted<>1)")
                                     ->exists();

        $companyChildsString = \app\components\CandoHelper::companyChilds(Yii::$app->session->get('CompanyID'), 'array');
        $companyParentString = \app\components\CandoHelper::companyParents(Yii::$app->session->get('CompanyID'), 'array');

        $this  //->joinWith(['company'])
                        ->innerJoin($personTable, "$personTable.PersonID=$imsDocumentTable.companyIdRef"); 
                
        /*if($userOBSFormActionQuery) {
            $this->andWhere(['or',
                                ['in',"$imsDocumentTable.companyIdRef", $companyChildsString],   
                                ['in',"$imsDocumentTable.companyIdRef" , $companyParentString],
                        ]);
        } else {
            $this->andWhere(['in',"$imsDocumentTable.companyIdRef" , $companyParentString]);
        }*/
        
        return $this->andWhere("$imsDocumentTable.isDeleted is null or $imsDocumentTable.isDeleted<>1");

        /*return $this  //->joinWith(['company'])
                        ->innerJoin($personTable, "$personTable.PersonID=$imsDocumentTable.companyIdRef")
                        ->andWhere(['or',
                            ['and',
                                ['in',"$imsDocumentTable.companyIdRef", $companyChildsString],
                                ($userOBSFormActionQuery > 0),
                            ],    
                            ['in',"$imsDocumentTable.companyIdRef" , $companyParentString],
                        ])
                        ->andWhere("$imsDocumentTable.isDeleted is null or $imsDocumentTable.isDeleted<>1");*/
    }
    
    public function master() {
        $companyTable = \app\modules\OBS\models\Company::tableName();
        $imsDocumentTable = \app\modules\document\models\ImsDocument::tableName();
        $imsDocumentCompanyTable = \app\modules\document\models\ImsDocumentCompany::tableName();
        
        $this->innerJoin($imsDocumentCompanyTable, "$imsDocumentCompanyTable.DocumentIDRef=$imsDocumentTable.id")
                ->innerJoin($companyTable, "$companyTable.companyIdRef=$imsDocumentCompanyTable.CompanyIDRef")
                ->andWhere("$imsDocumentCompanyTable.isDeleted is null or $imsDocumentCompanyTable.isDeleted<>1");
        
        if (!\app\components\Security::isCandoAdmin()) {
            $this->andWhere(["$imsDocumentCompanyTable.CompanyIDRef" => Yii::$app->session->get('CompanyID')]);
        }
        return $this;
    }

}
