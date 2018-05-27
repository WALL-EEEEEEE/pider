<?php
namespace Preprocess\GrapeWine;
use Pider\Prepost\Data\Pore;
use Pider\Prepost\Data\Rule;
use Pider\Prepost\Data\Throttle;
use Pider\Prepost\Data\Reaction;

class GrapeWineCountryPore extends Pore {
    private $ikeywords = [
        ''
    ];
    protected function selfFeatures():array {
        $this->self_datas = [
            '捷克共和国'=>'The Czech Republic',
            '南斯拉夫社会主义联邦共和国'=>'Socialist Federal Republic of Yugoslavia',
            '塞浦路斯共和国'=>'The Republic of Cyprus',
            '立陶宛共和国'=>'The Republic of Lithuania',
            '摩洛哥'=>'Morocco',
            '马其顿'=>'Macedonia',
            '新加坡'=> 'Singapore',
            '阿尔及利亚'=>'Algeria',
            '亚美尼亚共和国'=>'The Republic of Armenia',
            '黑山共和国'=>'The Republic of Montenegro',
            '埃及' =>'The Arab Republic of Egypt',
            '乌克兰共和国'=> 'Ukraine',
            '日本'=>'Japan',
            '越南'=>'Vietnam',
            '土耳其'=>'Turkey',
            '卢森堡'=>'Luxembourg',
            '塞尔维亚'=> 'Serbia',
            '泰国'=> 'Thailand',
            '马来西亚'=> 'Malaysia',
            '阿布哈兹'=> 'Abkhazia',
            '拉脱维亚' => 'Latvia',
            '比利时' => 'Belgium',
            '秘鲁'=>'Peru',
            '波兰共和国' => 'The Republic of Poland',
            '中国'=>'China',
            '古巴'=> 'Cuba',
            '澳大利亚'=>'Australia',
            '智利'=>'Chile',
            '法国'=> 'France',
            '德国'=> 'Germany',
            '意大利'=> 'Italy',
            '新西兰'=> 'New Zealand',
            '南非'=> 'South Africa',
            '美国'=> 'United States',
            '西班牙'=> 'Spain',
            '葡萄牙'=> 'Portugal',
            '匈牙利'=> 'Hungary',
            '阿根廷'=> 'Argentina',
            '丹麦'=> 'Denmark',
            '巴西'=> 'Brazil',
            '奥地利'=> 'Austria',
            '加拿大'=> 'Canada',
            '瑞士'=> 'Switzerland',
            '希腊'=> 'Greece',
            '格鲁吉亚'=> 'Georgia',
            '保加利亚'=> 'Bulgaria',
            '英国'=> 'United Kingdom',
            '爱尔兰'=> 'Ireland',
            '苏格兰'=> 'Scotland',
            '墨西哥'=> 'Mexico',
            '摩尔多瓦'=> 'Moldova',
            '瑞典'=> 'Sweden',
            '罗马尼亚'=> 'Romania',
            '克罗地亚'=> 'Croatia',
            '突尼斯'=> 'Tunisia',
            '俄罗斯'=> 'Russian Federation',
            '印度'=> 'India',
            '荷兰'=> 'Netherlands',
            '以色列'=> 'Israel',
            '斯洛文尼亚'=> 'Slovenia',
            '乌拉圭东岸共和国'=> 'The Oriental Republic of Uruguay',
            '黎巴嫩'=> 'Lebanon',
            '斯洛伐克共和国'=> 'The Slovak Republic',
            '阿塞拜疆共和国'=> 'The Republic of Azerbaijan',
            '纳米比亚'=> 'Namibia',
            '乌兹别克斯坦'=> 'Uzbekistan',
            '波斯尼亚和黑塞哥维那'=> 'Bosnia and Herzegovina',
            '缅甸'=> 'Myanmar',
            '玻利维亚'=> 'Bolivia',
            '马耳他'=> 'Malta',
            '柬埔寨'=> 'The Kingdom of Cambodia',
            '阿尔巴尼亚共和国'=> 'Republic of Albania',
            '韩国'=> 'Korea',
            '埃塞俄比亚'=> 'Ethiopia',
            '蒙古国'=> 'Mongolia',
            '印度尼西亚'=> 'Indonesia',
            '菲律宾'=> 'Republic of the Philippines',
            '法属波利尼西亚'=> 'French Polynesia',
            '约旦'=> 'Jordan',
            '克里米亚'=> 'Crimea',
            '坦桑尼亚'=> 'Tanzania',
            '哈萨克斯坦'=> 'Kazakhstan',
            '叙利亚'=> 'Syria',
            '肯尼亚'=> 'Kenya',
            '圣马力诺共和国'=> 'Serenissima Repubblica di San Marino',
            '塔吉克斯坦'=> 'Tajikistan',
            '科索沃'=> 'Kosovo',
            '吉尔吉斯斯坦'=> 'Kyrgyz Respublikasy',
            '土库曼斯坦'=> 'Turkmenistan'
        ];

        $GrapeWineCountryPropertyExist = new Throttle(function($data) {
            $subdata = [];
            foreach($data as $p_name => $p_value ) {
                $p_name = trim($p_name);
                $p_value = trim($p_value);
                if (in_array($p_value,$this->self_datas) || array_key_exists($p_value,$this->self_datas)){
                    $subdata[$p_name] = $p_value;
                } else {
                    foreach($this->self_datas as $k => $v) {
                        if (preg_match("/($k|$v)/i",$p_value)) {
                            $subdata[$p_name] = $p_value;
                        }
                    }
                }
            }
            return $subdata;
        });

        $GragulateReaction = new class($GrapeWineCountryPropertyExist) extends Reaction {
            public function react(array $data,Pore $pore):array {
                $clean_data = [];
                if (count($data) == 0) {
                    $clean_data['country_ch'] = '';
                    $clean_data['country_en'] = '';
                } else {
                    foreach($data as $key => $value) {
                        $value = trim($value);
                        foreach($pore->self_datas as $k => $v) {
                            if (preg_match("/($k|$v)/i",$value)) {
                                $clean_data['country_ch']  = $k;
                                $clean_data['country_en']  = $v;
                            }
                        }
                    }
                }
                return $clean_data;
            }
        };
        return ['reaction'=>[$GragulateReaction],'filter'=>[],'absorber'=>[]];
    }
}
