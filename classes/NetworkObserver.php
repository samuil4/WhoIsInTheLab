<?
/*
* ============================================================================
*  Name         : NetworkObserver.php
*  Part of      : WhoIsInTheLab
*  Description  : scan network and detect devices
*  Author     	: Leon Anavi
*  Email		: leon@anavi.org
* ============================================================================
*/

require_once "DatabaseManager.php";
require_once "User.php";

class NetworkObserver
{

	private static $LINK_TWITTER = 'https://twitter.com/';
	private static $LINK_FACEBOOK = 'https://www.facebook.com/';

	private $m_dbCtrl;

	function __construct()
	{
		$this->m_dbCtrl = new DatabaseManager();
	}
	//------------------------------------------------------------------------------
	
	function __destruct()
	{
		//Nothing to do
	}
	//------------------------------------------------------------------------------
	
	/**
	 * List users and number of active devices
	 *
	 * @return nothing
	 */
	public function listOnlineUsers($sType)
	{
		//make sure that the argument will be process correctly
		$sType = strtoupper($sType);
		
		$devices = $this->m_dbCtrl->listOnlineDevices();
		$nDevicesCount = count($devices);
		$users = $this->extractUsers($devices);
	
		switch($sType)
		{
			case 'JSON':
				echo $this->listJSON($nDevicesCount, $users);
			break;
			
			case 'HTML':
				echo $this->listHTML($nDevicesCount, $users);
			break;
			
			case 'XML':
				echo $this->listXML($nDevicesCount, $users);
			break;
			
			case 'TXT':
			default:
				//plain text
				echo $this->listPlainText($nDevicesCount, $users);
			break;
		}
	}
	//------------------------------------------------------------------------------
	
	private function extractUsers($devices)
	{
		$users = array();
		foreach($devices as $device)
		{
			if ( (false == empty($device['user_name1'])) ||
				 (false == empty($device['user_name2'])) ||
				 (false == empty($device['user_twitter'])) ||
				 (false == empty($device['user_facebook'])) ||
				 (false == empty($device['user_tel'])) )
			{
				$users[] = new User($device['user_name1'], $device['user_name2'], 
									$device['user_facebook'], $device['user_twitter'],
									$device['user_tel']);
			}
		}
		return $users;
	}
	//------------------------------------------------------------------------------
	
	private function listJSON($nDevicesCount, $users)
	{
		$output = array();
		//error status
		$output['error'] = array('ErrCode' => 0, 'ErrMsg' => '');
		//prepare users
		$jsonUsers = array();
		foreach($users as $user)
		{
			$jsonUser = array('name1' => $user->name1, 
								'name2' => $user->name2,
								'twitter' => $user->twitterLink,
								'facebook' => $user->facebookLink,
								'tel' => $user->tel);
			array_push($jsonUsers, $jsonUser);
		}
		//append the total count nad the users to the data
		$output['data'] = array('count' => $nDevicesCount, 'users' => $jsonUsers );
		return json_encode($output);
	}
	//------------------------------------------------------------------------------
	
	private function listHTML($nDevicesCount, $users)
	{
		$sOutput = "<h2>Online: {$nDevicesCount}</h2>\n";
		$sOutput .= "<ul>\n";
		foreach($users as $user)
		{
			$sOutput .= "<li>";
			$sOutput .= $user->name;
			$sTwitter = $user->twitter;
			if (false == empty($sTwitter))
			{
				$sOutput .= " twitter: <a href =\"{$user->twitterLink}\">{$sTwitter}</a>";
			}
			$sFb = $user->facebook;
			if (false == empty($sFb))
			{
				$sOutput .= " facebook: <a href=\"{$user->facebookLink}\">{$sFb}</a>";
			}
			$sTel = $user->tel;
			if (false == empty($sTel))
			{
				$sOutput .= " tel: <a href=\"callto:{$sTel}\">{$sTel}</a>";
			}
			echo "</li>\n";
		}
		$sOutput .= "</ul>\n";
		return $sOutput;
	}
	//------------------------------------------------------------------------------
	
	private function listXML($nDevicesCount, $users)
	{
		$sOutPut = '';
		try
		{
			$xml = new DOMDocument("1.0");
			//root
			$root = $xml->createElement('who');
			$xml->appendChild($root);
			//error
			$error = $xml->createElement('error');
			$root->appendChild($error);
			//error code
			$ErrCode = $xml->createElement('ErrCode');
			$ErrCodeText = $xml->createTextNode('0');
			$ErrCode->appendChild($ErrCodeText);
			$error->appendChild($ErrCode);
			//error message
			$ErrMsg = $xml->createElement('ErrMsg');
			$ErrMsgText = $xml->createTextNode('');
			$ErrMsg->appendChild($ErrMsgText);
			$error->appendChild($ErrMsg);
			//data
			$data = $xml->createElement('data');
			$root->appendChild($data);
			//total number of devices
			$count = $xml->createElement('count');
			$countText = $xml->createTextNode($nDevicesCount);
			$count->appendChild($countText);
			$data->appendChild($count);
			//users
			$xmlUsers = $xml->createElement('users');
			$data->appendChild($xmlUsers);
			//user
			foreach($users as $user)
			{
				$xmlUser = $xml->createElement('user');
				//name1
				$xmlName1 = $xml->createAttribute('name1');
				$xmlName1->value = $user->name1;
				$xmlUser->appendChild($xmlName1);
				//name2
				$xmlName2 = $xml->createAttribute('name2');
				$xmlName2->value = $user->name2;
				$xmlUser->appendChild($xmlName2);
				
				$xmlUsers->appendChild($xmlUser);
			}
			
			$xml->preserveWhiteSpace = false;
			$xml->formatOutput = true;
			$sOutPut = $xml->saveXML();
		}
		catch (Exception $ex)
		{
			//Nothing to do
			print_r($ex);
		}
		return $sOutPut;
	}
	//------------------------------------------------------------------------------
	
	private function listPlainText($nDevicesCount, $users)
	{
		$sOutput = "Online: {$nDevicesCount} \n";
		foreach($users as $user)
		{
			$sOutput .= "Name: {$user->name} ";
			$sTwitter = $user->twitterLink;
			if (false == empty($sTwitter))
			{
				$sOutput .= "Twitter: {$sTwitter} ";
			}
			$sFb = $user->facebookLink;
			if (false == empty($sFb))
			{
				$sOutput .= "Facebook: {$sFb}";
			}
			$sTel = $user->tel;
			if (false == empty($sTel))
			{
				$sOutput .= "tel: {$sTel}";
			}
			$sOutput .= "\n";
		}
		return $sOutput;
	}
	//------------------------------------------------------------------------------
		
}
?>