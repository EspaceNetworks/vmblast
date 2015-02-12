<?php
namespace FreePBX\modules;
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015 Sangoma Technologies.
//
class Vmblast implements \BMO {
	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
		}
		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;
	}
	public function install() {}
	public function uninstall() {}
	public function backup() {}
	public function restore($backup) {}
	public function doConfigPageInit($page) {
		$request = $_REQUEST;
		$dispnum = 'vmblast'; //used for switch on config.php
		$action         = isset($request['action'])        ? $request['action']      : '';

		//the extension we are currently displaying
		$account        = isset($request['account'])       ? $request['account']     : '';
		$extdisplay     = isset($request['extdisplay'])    ? ltrim($request['extdisplay'],'GRP-')  : (($account != '')?$account:'');
		$description    = isset($request['description'])   ? $request['description'] : '';
		$audio_label    = isset($request['audio_label'])   ? $request['audio_label'] : -1;
		$password       = isset($request['password'])      ? $request['password']    : '';
		$default_group  = isset($request['default_group']) ? $request['default_group'] : '0';
		$vmblast_list   = isset($request['vmblast_list'])  ? $request['vmblast_list']  : '';
		$view			= isset($request['view'])			? $request['view']			: 'form';

		// do if we are submitting a form
		if(isset($request['action'])){
			//check if the extension is within range for this user
			if (isset($account) && !checkRange($account)){
				echo "<script>javascript:alert('". _("Warning! Extension")." ".$account." "._("is not allowed for your account").".');</script>";
			} else {
				//add group
				if ($action == 'addGRP') {

					$conflict_url = array();
					$usage_arr = framework_check_extension_usage($account);
					if (!empty($usage_arr)) {
						$conflict_url = framework_display_extension_usage_alert($usage_arr);
					} else if (vmblast_add($account,$vmblast_list,$description,$audio_label,$password,$default_group)) {
						$request['action'] = 'delGRP';
						needreload();
						redirect_standard('extdisplay', 'view');
					}
				}

				//del group
				if ($action == 'delGRP') {
					vmblast_del($account);
					needreload();
					redirect_standard();
				}

				//edit group - just delete and then re-add the extension
				if ($action == 'editGRP') {
					vmblast_del($account);
					vmblast_add($account,$vmblast_list,$description,$audio_label,$password,$default_group);
					needreload();
					redirect_standard('extdisplay','view');
				}
			}
		}
	}
		public function getActionBar($request){
		switch($request['display']){
			case 'vmblast':
				$buttons = array(
					'delete' => array(
						'name' => 'delete',
						'id' => 'delete',
						'value' => _('Delete')
					),
					'submit' => array(
						'name' => 'submit',
						'id' => 'submit',
						'value' => _('Submit')
					),
					'reset' => array(
						'name' => 'reset',
						'id' => 'reset',
						'value' => _('Reset')
					)
    			);
    		break;
    	}
    	if (empty($request['extdisplay'])) {
    		unset($buttons['delete']);
    	}
    	if($request['view'] != 'form'){
    		unset($buttons);
    	}
    	return $buttons;
    } 
}
