<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\FailTags;

/**
 * FailTagsSearch represents the model behind the search form about `common\models\FailTags`.
 */
class FailTagsSearch extends FailTags
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'use_times', 'status', 'is_special'], 'integer'],
            [['type', 'name', 'des'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = FailTags::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

//        $this->load($params);
        foreach($params as $k => $v)
        {
            $this->$k = $v;
        }
        

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'use_times' => $this->use_times,
            'status' => $this->status,
            'is_special' => $this->is_special,
        ]);

        $query->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'des', $this->des]);

        return $dataProvider;
    }
}
