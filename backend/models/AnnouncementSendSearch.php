<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\AnnouncementSend;

/**
 * AnnouncementSendSearch represents the model behind the search form about `common\models\AnnouncementSend`.
 */
class AnnouncementSendSearch extends AnnouncementSend
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'send_person_id', 'send_time', 'is_success'], 'integer'],
            [['addressee_des', 'addressee_id', 'title', 'send_person_name', 'content'], 'safe'],
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
        $query = AnnouncementSend::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'send_person_id' => $this->send_person_id,
            'send_time' => $this->send_time,
            'is_success' => $this->is_success,
        ]);

        $query->andFilterWhere(['like', 'addressee_des', $this->addressee_des])
            ->andFilterWhere(['like', 'addressee_id', $this->addressee_id])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'send_person_name', $this->send_person_name])
            ->andFilterWhere(['like', 'content', $this->content]);

        return $dataProvider;
    }
}
