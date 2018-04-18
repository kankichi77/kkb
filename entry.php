<?php
Class Entry {
  public $id = "";
  public $item = "";
  public $amount = "";
  public $date = "";
  public $category = "";
  public $method = "";
  public $op = "";

  public function init() {
    $id = "";
    $item = "";
    $amount = "";
    $date = "";
    $category = "";
    $method = "";
    $op = "";
  }

  public function set($i, $t, $a, $d, $c, $m, $o) {
    $id = $i;
    $item = $t;
    $amount = $a;
    $date = $d;
    $category = $c;
    $method = $m;
    $op = $o;
  }
}
?>
