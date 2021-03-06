<?php

namespace KayStrobach\Easylogin\Hooks;

use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Resource\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class PreStartPageHook{

	function addLoginNews(&$params, &$reference) {
		global $TYPO3_CONF_VARS;
		// do nothing if not in login form
			if($reference->scriptID != 'typo3/index.php') {
				return;
			}
		// get settings
			$_EXTCONF = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['easylogin']);
			$config   = $_EXTCONF;
			unset($config['usersAndPasswords']);
		
		//access check
			if($_EXTCONF['use_devIPmask'] && !GeneralUtility::cmpIP(GeneralUtility::getIndpEnv('REMOTE_ADDR'), $TYPO3_CONF_VARS['SYS']['devIPmask'])) {
				return;
			}
		//parse users
			$users = explode(',',$_EXTCONF['usersAndPasswords']);
			foreach($users as $key=>$userString) {
				$user = explode('|',$userString);
				$users[$key] = array(
					'xtype'    => 'easylogin-loginbutton',
					'text'     => $user[0],
					'username' => $user[0],
					'password' => $user[1],
					'tooltip'  => 'Login as ' . $user[0] . ' with known Password',
				);
			}
		//get openid users
			if(ExtensionManagementUtility::isLoaded('openid')) {
				$time = time();
				$openIdUsers = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'*',
					'be_users',
					'	tx_openid_openid <> "" AND
						disable = 0 AND
						deleted = 0 AND
						starttime < ' . $time . ' AND
						(endtime = 0 OR endtime > ' . $time . ')',
					'', 'username ASC'
				);
				foreach($openIdUsers as $user) {
					$users[$key] = array(
						'xtype'    => 'easylogin-loginbutton-openid',
						'text'     => $user['username'],
						'username' => $user['username'],
						'password' => $user['tx_openid_openid'],
						'tooltip'  => 'Login as ' . $user['username'] . ' with OpenID',
					);
				}
			}
		// remove labels of settings indicates that!
			if($_EXTCONF['showUsername']=='iconOnly') {
				foreach($users as $key=>$user) {
					unset($users[$key]['text']);
				}
			}
		//apply default values
			//admin|password,kaystrobach|sbs007,piwiktest|piwiktest
			if(!$config['iconPlacement']) {
				$config['iconPlacement'] = 'top';
			}
		//add library and settings
			if(count($users)){
				if(VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 7000000) {
					$this->renderForVersion($reference, '6.2');
				} else {
					$this->renderForVersion($reference, '7.0');
				}
				$reference->getPageRenderer()->addInlineSetting(
					'easylogin',
					'users',
					$users
				);
				$reference->getPageRenderer()->addInlineSetting(
					'easylogin',
					'config',
					$config
				);
			}
	}
	function getIcon($username) {
		$username = $GLOBALS['TYPO3_DB']->fullQuoteStr($username,'be_users');
		$user = BackendUtility::getRecordRaw(
			'be_users',
			'username="'.$username.'"'
		);
		return IconUtility::getIcon(
				'be_users',
				$user
			);
	}

	function renderForVersion($reference, $version) {
		$reference->getPageRenderer()->addJsFile(
			ExtensionManagementUtility::extRelPath('easylogin')
			.'Resources/Public/JavaScript/LoginToolbar/' . $version . '/main.js'
		);
		$reference->getPageRenderer()->addCssFile(
			ExtensionManagementUtility::extRelPath('easylogin')
			.'Resources/Public/JavaScript/LoginToolbar/' . $version . '/main.css'
		);

	}
}
