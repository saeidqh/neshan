<?php

namespace app\modules\document\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
* @see ImsDocumentKeywordRelatedSearch
 * @author <mail:mortezakml@gmail.com & Tel:09366263397>
*
* @author <mail:mortezakml@gmail.com & Tel:09366263397>
*/

class ImsDocumentKeywordRelatedSearch extends ImsDocumentKeywordRelated {

    public function rules() {
        return [
            [['id','imsIdRef','keywordIdRef'], 'safe', 'on' => 'search'],
        ];
    }

    public function scenarios() {
        $scenario = parent::scenarios();
        $scenario['search'] = ['id','imsIdRef','keywordIdRef'];
        return $scenario;
    }

  
}
