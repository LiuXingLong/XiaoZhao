<?php
//多线程实现
if(empty($_POST)){
	header("Location: index.html");
	exit;
}
$school = $_POST['school'];
$vocation = $_POST['vocation'];
$time = $_POST['time'];
$page = $_POST['page'];
$flag = $_POST['flag'];
if(empty($flag)) $flag = 0;
$flag = (int)$flag;
if(false!==stripos($vocation,'&')&&false!==stripos($vocation,'|'))exit;
switch ($school)
{
	case "hnust":
		require_once('./Controller/Hnust.class.php');
		$hnust = new Hnust();
		echo $hnust->index($vocation,$school,$time,$page,$flag);
		break;
	case "xtu":
		require_once('./Controller/Xtu.class.php');
		$xtu = new Xtu();
		echo $xtu->index($vocation,$school,$time,$page,$flag);
		break;
	case "csu_1":
		require_once('./Controller/Csu.class.php');
		$csu = new Csu();
		echo $csu->index($vocation,$school,$time,$page,$flag);
		break;
	case "csu_2":
		require_once('./Controller/Csu.class.php');
		$csu = new Csu();
		echo $csu->index($vocation,$school,$time,$page,$flag);
		break;
	case "csu_3":
		require_once('./Controller/Csu.class.php');
		$csu = new Csu();
		echo $csu->index($vocation,$school,$time,$page,$flag);
		break;
	case "hnu":
		require_once('./Controller/Hnu.class.php');
		$hnu = new Hnu();
		echo $hnu->index($vocation,$school,$time,$page,$flag);
		break;
	case "hunnu":
		require_once('./Controller/Hunnu.class.php');
		$hunnu = new Hunnu();
		echo $hunnu->index($vocation,$school,$time,$page,$flag);
		break;
	case "csust":
		require_once('./Controller/Csust.class.php');
		$csust = new Csust();
		echo $csust->index($vocation,$school,$time,$page,$flag);
		break;
}