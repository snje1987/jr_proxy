<?php

namespace App\Model;

class Calculator {

    const POINTS_ATTRS = ['atk', 'torpedo', 'def', 'air_def'];
    const VALUES_ATTRS = ['2', '3', '4', '9'];

    protected $values = [];
    protected $left_point;
    protected $used_ship;
    protected $material;
    protected $target;

    public function __construct() {
        $values = \App\Config::get('main', 'values', []);
        for ($i = 0; $i < 4; $i++) {
            $this->values[$i] = isset($values[$i]) ? intval($values[$i]) : 1;
        }
    }

    public function cal($target_ship, $material) {
        foreach (self::POINTS_ATTRS as $k) {
            $this->left_point[$k] = $target_ship['strengthenTop'][$k] - $target_ship['strengthenAttribute'][$k];
            if ($this->left_point[$k] < 0) {
                $this->left_point[$k] = 0;
            }
        }

        $this->target = $target_ship;

        //计算每个素材的拆解价值
        foreach ($material as $cid => $info) {
            $material_value = 0;
            foreach (self::VALUES_ATTRS as $i => $name) {
                $material_value += $info['dismantle'][$name] * $this->values[$i];
            }
            $material[$cid]['value'] = $material_value;

            $point_sum = 0;
            foreach (self::POINTS_ATTRS as $k) {
                $point_sum += $info['strengthenSupplyExp'][$k];
            }
            //这是素材的绝对口感值，最终会按照这个来剔除多余的素材
            $material[$cid]['score'] = $point_sum / $material_value;
        }

        $this->material = $material;
        $this->used_ship = [];

        $this->do_calc();

        return $this->get_used();
    }

    protected function do_calc() {
        while (true) {
            if ($this->is_full()) {//已经强化完成
                break;
            }
            if (empty($this->material)) {//已经没有素材
                break;
            }
            $cid = $this->get_best(); //对素材评分，计算出性价比最高的

            if ($cid === -1) {
                break;
            }

            $this->use_ship($cid); //使用这些素材
        }

        $this->remove_exceed();
    }

    protected function get_best() {
        $best_ship = -1;
        $best_score = -1;
        foreach ($this->material as $cid => $info) {
            $point_sum = 0;
            foreach (self::POINTS_ATTRS as $k) {
                //计算素材能给当前目标提供的强化点数之和
                if ($info['strengthenSupplyExp'][$k] <= $this->left_point[$k]) {
                    $point_sum += $info['strengthenSupplyExp'][$k];
                }
                else {
                    $point_sum += ($this->left_point[$k] > 0 ? $this->left_point[$k] : 0);
                }
            }
            //这是素材的相对口感值，找出最高的来使用
            $score = $point_sum / $info['value'];
            if ($score > 0 && $score > $best_score) {
                $best_score = $score;
                $best_ship = $cid;
            }
        }

        return $best_ship;
    }

    protected function use_ship($cid) {
        while (true) {
            $info = $this->material[$cid];

            if (!isset($this->used_ship[$cid])) {
                $this->used_ship[$cid] = $info;
                $this->used_ship[$cid]['count'] = 1;
            }
            else {
                $this->used_ship[$cid]['count'] ++;
            }

            $score_change = false;

            if ($this->material[$cid]['count'] > 1) {
                $this->material[$cid]['count'] --;
            }
            else {
                unset($this->material[$cid]);
                $score_change = true;
            }

            foreach (self::POINTS_ATTRS as $k) {
                //确定素材口感是否变化，如变化了就需要重新进行评分
                if ($this->left_point[$k] > 0 &&
                        $this->left_point[$k] < $info['strengthenSupplyExp'][$k] * 2) {
                    $score_change = true;
                }

                //更新剩余强化点数
                $this->left_point[$k] -= $info['strengthenSupplyExp'][$k];
            }

            if ($score_change) {
                break;
            }
        }
    }

    protected function remove_exceed() {
        while (true) {
            $best_cid = -1;
            $best_score = -1;

            //找出能移除的评分最高的船
            foreach ($this->used_ship as $cid => $info) {
                $can_remove = true;
                foreach (self::POINTS_ATTRS as $k) {
                    if ($this->left_point[$k] + $info['strengthenSupplyExp'][$k] > 0) {
                        $can_remove = false;
                        break;
                    }
                }

                if ($can_remove) {
                    if ($info['score'] > $best_score) {
                        $best_score = $info['score'];
                        $best_cid = $cid;
                    }
                }
            }

            //找不到就停止
            if ($best_cid === -1) {
                break;
            }

            foreach (self::POINTS_ATTRS as $k) {
                $this->left_point[$k] += $this->used_ship[$best_cid]['strengthenSupplyExp'][$k];
            }

            if ($this->used_ship[$best_cid]['count'] > 1) {
                $this->used_ship[$best_cid]['count'] --;
            }
            else {
                unset($this->used_ship[$best_cid]);
            }
        }
    }

    protected function get_used() {
        $list = [];
        $sum_point = [];
        $sum_dismantle = [];
        $exceed_point = [];

        foreach (self::POINTS_ATTRS as $k) {
            $exceed_point[$k] = $this->left_point[$k] * -1;
            $sum_point[$k] = 0;
        }

        foreach (self::VALUES_ATTRS as $name) {
            $sum_dismantle[$name] = 0;
        }

        foreach ($this->used_ship as $cid => $info) {
            $list[$cid] = [
                'title' => $info['title'],
                'count' => $info['count'],
                'strengthenSupplyExp' => $info['strengthenSupplyExp'],
                'dismantle' => $info['dismantle'],
            ];

            foreach (self::POINTS_ATTRS as $k) {
                $sum_point[$k] += $info['strengthenSupplyExp'][$k] * $info['count'];
            }

            foreach (self::VALUES_ATTRS as $name) {
                $sum_dismantle[$name] += $info['dismantle'][$name] * $info['count'];
            }
        }

        ksort($list);

        return [
            'sum_point' => $sum_point,
            'sum_dismantle' => $sum_dismantle,
            'exceed_point' => $exceed_point,
            'list' => $list,
        ];
    }

    protected function is_full() {
        foreach (self::POINTS_ATTRS as $k) {
            if ($this->left_point[$k] > 0) {
                return false;
            }
        }
        return true;
    }

}