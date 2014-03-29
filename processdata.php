<?php

define("MAX_COMMANDS_PER_DEVICE",3);

global $users, $devices;

$devices = array();
$obj = new StdClass;
$obj->type = "tablet";
$obj->id = "2530d3c52a3c0cbb";
$obj->owner = "alex";
$obj->state = "active";
$devices[] = $obj;
$obj = new StdClass;
$obj->type = "phone";
$obj->owner = "alex";
$obj->state = "active";
$obj->id = "1187b2f0e5dfe51d";
$devices[] = $obj;



$users = array();
$obj = new StdClass;
$obj->username = "alex";
$obj->password = "20alex14";
$obj->name = "Alexander Cruz";
$obj->email = "a.cruzcontreras@hotmail.com";
$obj->country = "Colombia";
$obj->type = "Premium";
$obj->country = "Colombia";
$obj->devices = $devices;
$users[] = $obj;


switch($_REQUEST["task"])
{
	case "loginuser":
	
		$username = $_REQUEST["username"];
		$password = $_REQUEST["password"];
		$obj = new stdClass;
		$obj->result = "ERROR";	
		foreach($users as $u)
		{
			$user = $u->username;
			$pass = $u->password;
			if($username == $user and $password == $pass)
			{
				$obj->result = "OK";	
				$obj->user = $u;
				$obj->devices = array();
				foreach($devices as $d)
				{
					if($d->owner == $user)
					{
						$obj->devices[] = $d;
					}
				}
				break;
			}
		}
		header('Content-type: application/json; charset=UTF-8');
		echo json_encode($obj);
		exit;
	break;
	case "calldevice":
	
		$device_source = $_REQUEST["device_source"];
		$device_dest = $_REQUEST["device_dest"];
		$command = $_REQUEST["command"];
		$obj = new stdClass;
		$obj->result = "ERROR";	
		foreach($devices as $d)
		{
			$id = $d->id;
			if($device_source == $id)
			{
				$obj->result = "OK";	
				file_put_contents($device_source.".command",date("YmdHis")."|".$device_source."|".$device_dest."|".$command.";",FILE_APPEND);
				break;
			}
		}
		header('Content-type: application/json; charset=UTF-8');
		echo json_encode($obj);
		exit;
	break;
	case "getcommand":
		global $device_source;
		global $commands;
		global $devices;
		global $thedevices;
		global $thelines;
		$commands = array();
		$thedevices = array();
		$thelines = array();
		$device_source = $_REQUEST["device_source"];
		$obj = new stdClass;
		$obj->result = "ERROR";	
		$obj->command = "";
		function search_device($item)
		{
			global $device_source;
			if($item->id == $device_source)
			{
				return true;	
			}
			else
			{
				return false;
			}
		}
		
		
		function search_command($item)
		{
			$there = false;
			global $device_source;//me
			global $commands;
			global $devices;
			global $thedevices;
			global $thelines;
			if(file_exists($device_source.".command"))
			{
				$contentfileme = file_get_contents($device_source.".command");
			}
			else
			{
				$contentfileme = "";
			}
			$device_dest = $item->id;
			if($device_dest != $device_source and file_exists($device_dest.".command"))
			{
				$contentfile = file_get_contents($device_dest.".command");
				$lines = explode(";",$contentfile);
				foreach($lines as $line)
				{
					if(count($commands) <= MAX_COMMANDS_PER_DEVICE)
					{
						$command = explode("|",$line);
						if($command[2] == $device_source or $command[2] == "any")
						{
							if(in_array($line,explode(";",$contentfileme)) == false)
							{
								$commands[] = $command[3];
								$thedevices[] = $command[1];
								$thelines[] = $line;
								file_put_contents($device_source.".command",$line.";",FILE_APPEND);
								$there = true;
							}
						}
					}
					else
					{
						break;
					}
				}
			}
			return $there;
		}

		
		
		$result = array_filter($devices,"search_device");
		
		if(count($result) > 0)
		{
			$obj->result = "OK";
			$result = array_filter($devices,"search_command");
			if(count($result) > 0)
			{
				$obj->command = implode(";",$commands);
				$obj->device  = implode(";",$thedevices);
				$obj->line    = implode(";",$thelines);
			}
		}
		header('Content-type: application/json; charset=UTF-8');
		echo json_encode($obj);
		exit;
	break;
	case "get_file":
		$obj 	= new stdClass;
		if(is_uploaded_file($_FILES['file']['tmp_name'])) 
		{
			$docsfolder = "files/";
			@mkdir($docsfolder,0777,true);
			$fn = basename($_FILES['file'] ['name']);
			$res = copy($_FILES['file'] ['tmp_name'],$docsfolder.$fn);
			if($res)
			{
				$obj->result = "OK";
			}
			else
			{
				$obj->result = "ERROR"; 
			}
		} 
		else 
		{
			$obj->result = "ERROR";
		}		
		
		$obj->filename = "http://intranet.seven.com.co/".$docsfolder.$fn;
		header('Content-type: application/json; charset=UTF-8');
		echo json_encode($obj);
		exit;
	break;
}



?>
