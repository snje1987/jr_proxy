<?php

namespace App\Controler;

use App\Model\ShipList;
use App\Model\ShipCard;

class Boat extends BaseControler {

    public function __construct($router) {
        parent::__construct($router);
    }

    public function c_index() {
        $use = [];
        $target = [];

        $ship_list = new ShipList();

        $all_ship = $ship_list->get_list();

        $ship_card = new ShipCard();

        foreach ($all_ship as $id => $v) {
            if ($v['isLocked'] == 0) {
                if (!isset($use[$v['shipCid']])) {
                    $card = $ship_card->get_ship($v['shipCid']);

                    if ($card === null) {
                        continue;
                    }

                    $use[$v['shipCid']] = [
                        'count' => 1,
                        'title' => $v['title'],
                        'strengthenSupplyExp' => $card['strengthenSupplyExp'],
                        'dismantle' => $card['dismantle'],
                    ];
                }
                else {
                    $use[$v['shipCid']]['count'] ++;
                }
            }
            else {
                $card = $ship_card->get_ship($v['shipCid']);
                if ($card !== null) {
                    $cur_strengthen = $v['strengthenAttribute'];
                    $full_strengthen = $card['strengthenTop'];

                    foreach ($cur_strengthen as $k1 => $v1) {
                        if ($v1 < $full_strengthen[$k1]) {
                            $v['strengthenTop'] = $full_strengthen;
                            $target[$id] = $v;
                            break;
                        }
                    }
                }
            }
        }

        ksort($use);
        ksort($target);

        $values = \App\Config::get('main', 'values');

        $this->display_tpl('ship/index', [
            'use' => $use,
            'target' => $target,
            'values' => $values,
        ]);
    }

}
