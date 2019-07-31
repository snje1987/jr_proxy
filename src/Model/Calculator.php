<?php

namespace App\Model;

class Calculator {

    const POINTS_ATTRS = ['atk', 'torpedo', 'def', 'air_def'];
    const VALUES_ATTRS = ['2', '3', '4', '9'];

    protected $values = [];
    protected $points = [];
    protected $left_point;
    protected $used_ship;
    protected $material;
    protected $target;

    public function __construct() {
        $values = \App\Config::get('main', 'values', []);
        for ($i = 0; $i < 4; $i++) {
            $this->values[$i] = isset($values[$i]) ? intval($values[$i]) : 1;
            if ($this->values[$i] <= 0) {
                $this->values[$i] = 1;
            }
        }

        $points = \App\Config::get('main', 'points', []);
        for ($i = 0; $i < 4; $i++) {
            $this->points[$i] = isset($points[$i]) ? intval($points[$i]) : 1;
            if ($this->points[$i] < 0) {
                $this->points[$i] = 0;
            }
        }
    }

    public function cal($target_ship, $material) {
        foreach (self::POINTS_ATTRS as $k) {
            $this->left_point[$k] = $target_ship->strengthen_top[$k] - $target_ship->strengthen[$k];
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

            $point_sum = 0;
            $point_value = 0;
            foreach (self::POINTS_ATTRS as $i => $k) {
                $point_sum += $info['strengthen_supply'][$k];
                $point_value += $info['strengthen_supply'][$k] * $this->points[$i];
            }

            $material_value += $point_value;
            $material[$cid]['value'] = $material_value;

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

            if ($cid === null) {
                break;
            }

            $this->use_ship($cid); //使用这些素材
        }

        $this->remove_exceed();
    }

    protected function get_best() {
        $best_ship = null;
        $best_score = null;
        foreach ($this->material as $cid => $info) {
            $point_sum = 0;
            foreach (self::POINTS_ATTRS as $i => $k) {
                //计算素材能给当前目标提供的强化点数之和
                if ($info['strengthen_supply'][$k] <= $this->left_point[$k]) {
                    $point_sum += $info['strengthen_supply'][$k];
                }
                else {
                    $point_sum += ($this->left_point[$k] > 0 ? $this->left_point[$k] : 0);
                }
            }
            //这是素材的相对口感值，找出最高的来使用
            $score = $point_sum / $info['value'];

            if ($point_sum > 0 && ($best_score === null || $score > $best_score)) {
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
                        $this->left_point[$k] < $info['strengthen_supply'][$k] * 2) {
                    $score_change = true;
                }

                //更新剩余强化点数
                $this->left_point[$k] -= $info['strengthen_supply'][$k];
            }

            if ($score_change) {
                break;
            }
        }
    }

    protected function remove_exceed() {
        while (true) {
            $worst_cid = null;
            $worst_cid_score = null;

            //找出能移除的评分最低的船
            foreach ($this->used_ship as $cid => $info) {
                $can_remove = true;
                foreach (self::POINTS_ATTRS as $k) {
                    if ($this->left_point[$k] + $info['strengthen_supply'][$k] > 0) {
                        $can_remove = false;
                        break;
                    }
                }

                if ($can_remove) {
                    if ($worst_cid_score === null || $info['score'] < $worst_cid_score) {
                        $worst_cid_score = $info['score'];
                        $worst_cid = $cid;
                    }
                }
            }

            //找不到就停止
            if ($worst_cid === null) {
                break;
            }

            foreach (self::POINTS_ATTRS as $k) {
                $this->left_point[$k] += $this->used_ship[$worst_cid]['strengthen_supply'][$k];
            }

            if ($this->used_ship[$worst_cid]['count'] > 1) {
                $this->used_ship[$worst_cid]['count'] --;
            }
            else {
                unset($this->used_ship[$worst_cid]);
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
                'strengthen_supply' => $info['strengthen_supply'],
                'dismantle' => $info['dismantle'],
            ];

            foreach (self::POINTS_ATTRS as $k) {
                $sum_point[$k] += $info['strengthen_supply'][$k] * $info['count'];
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
