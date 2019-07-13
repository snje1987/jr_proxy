<?php

namespace App\Controler;

use App\Model\ShipList;
use App\Model\Calculator;
use Exception;

class Boat extends BaseControler {

    public function __construct($router) {
        parent::__construct($router);
    }

    public function c_index() {


        $ship_list = new ShipList();

        $material = $ship_list->get_material();
        $target = $ship_list->get_target_list();

        $values = \App\Config::get('main', 'values');

        $this->display_tpl('ship/index', [
            'material' => $material,
            'target' => $target,
            'values' => $values,
        ]);
    }

    public function c_calc() {
        try {
            $msg = [
                'error' => 1,
                'msg' => '操作失败',
            ];

            $target = isset($_POST['target']) ? strval($_POST['target']) : '';
            $material_cid = isset($_POST['material']) ? strval($_POST['material']) : '';

            $material_cid = json_decode($material_cid, true);

            if (empty($target) || empty($material_cid)) {
                throw new Exception('参数不合法');
            }

            $ship_list = new ShipList();

            $target_ship = $ship_list->get_target($target);
            if ($target_ship === null) {
                throw new Exception('强化目标不存在');
            }

            $material_ships = $ship_list->get_material($material_cid);

            if (empty($material_ships)) {
                throw new Exception('强化素在不存在');
            }

            $calculator = new \App\Model\Calculator();

            $result = $calculator->cal($target_ship, $material_ships);

            $msg = [
                'error' => 0,
                'result' => $result,
            ];
        }
        catch (Exception $ex) {
            $msg = [
                'error' => 1,
                'msg' => $ex->getMessage(),
            ];
        }

        $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
        $this->router->send($msg);
    }

}
