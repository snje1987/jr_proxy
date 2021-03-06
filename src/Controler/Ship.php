<?php

namespace App\Controler;

use App\Model\PlayerInfo;
use App\Model\Calculator;
use App\Model\Fleet;
use Exception;

class Ship extends BaseControler {

    public function __construct($router) {
        parent::__construct($router);
    }

    public function c_index() {
        $cur_uid = isset($_GET['uid']) ? strval($_GET['uid']) : '';

        $uid_list = \App\Model\LoginInfo::get()->get_all_uids();
        if ($cur_uid == '') {
            $cur_uid = current($uid_list);
            $this->router->redirect('/ship/index?uid=' . $cur_uid);
            return;
        }

        $play_info = new PlayerInfo($cur_uid);

        $material = $play_info->get_material_ships();
        $target = $play_info->get_need_strengthen_ships();

        $values = \App\Config::get('main', 'values');
        $points = \App\Config::get('main', 'points');

        $this->display_tpl('ship/index', [
            'material' => $material,
            'target' => $target,
            'values' => $values,
            'points' => $points,
            'uid_list' => $uid_list,
            'cur_uid' => $cur_uid,
        ]);
    }

    public function c_calc() {
        try {
            $uid = isset($_POST['uid']) ? strval($_POST['uid']) : '';
            $target = isset($_POST['target']) ? strval($_POST['target']) : '';
            $material_cid = isset($_POST['material']) ? strval($_POST['material']) : '';

            $material_cid = json_decode($material_cid, true);

            if (empty($target) || empty($material_cid) || empty($uid)) {
                throw new Exception('参数不合法');
            }

            $player_info = new PlayerInfo($uid);

            $target_ship = $player_info->get_ship($target);
            if ($target_ship === null || !$target_ship->need_strengthen()) {
                throw new Exception('强化目标不存在');
            }

            $material_ships = $player_info->get_material_ships($material_cid);

            if (empty($material_ships)) {
                throw new Exception('强化素在不存在');
            }

            $calculator = new Calculator();

            $result = $calculator->cal($target_ship, $material_ships);
        }
        catch (Exception $ex) {
            $result = false;
            $msg = $ex->getMessage();
        }

        if ($result === false) {
            $this->display_tpl('msg_dlg', [
                'title' => '发生错误',
                'msg' => $msg,
            ]);
        }
        else {
            $this->display_tpl('ship/calc', [
                'title' => '计算结果',
                'result' => $result,
            ]);
        }
    }

    public function c_fleet() {
        $cur_uid = isset($_GET['uid']) ? strval($_GET['uid']) : '';

        $uid_list = \App\Model\LoginInfo::get()->get_all_uids();
        if ($cur_uid == '') {
            $cur_uid = current($uid_list);
            $this->router->redirect('/ship/fleet?uid=' . $cur_uid);
            return;
        }

        $cur_fleet = isset($_GET['fleet']) ? intval($_GET['fleet']) : 0;

        $play_info = new PlayerInfo($cur_uid);

        $fleet_list = $play_info->get_fleet_list();

        $fleet = null;
        if ($cur_fleet > 0) {
            $fleet = $play_info->get_fleet($cur_fleet);
            $fleet->apply_skill();
            $fleet->apply_tactic();
        }

        $this->display_tpl('ship/fleet', [
            'cur_fleet' => $cur_fleet,
            'fleet_list' => $fleet_list,
            'uid_list' => $uid_list,
            'cur_uid' => $cur_uid,
            'fleet' => $fleet,
        ]);
    }

}
