<?php
require_once('dao.php');
/**
 * This class is used to create a module that can have fields can support active...
 * @author Xydac
 *
 */
abstract class xydac_cms_module{
	private $VALID_OPTION_LIST = array('main','active','field');
	//String: Name of the module
	private $module_name;
	//String: Label of the module
	private $module_label;
	//String: path
	private $path;
	//bool: does it uses custom field
	private $has_custom_fields;
	//bool: provides an option of active and inactive objects
	private $uses_active;
	//array: Name of the option used for registering
	private $registered_option;
	//xydac_options_dao: the Data Access Layer
	private $dao;
	//Base Path for module page
	private $base_path;
	//Default Tabs
	private $tabs;
	//Position of menu on plugin page valid option : top/sub
	private $menu_position;
	//XydacSync Module
	private $xydac_sync;

	/*----------------------------Constructor---------------------*/
	//array('module_label'=>'','has_custom_fields'=>,'uses_active'=>,'registered_option'=>array('main'=>'','active'=>'','field'=>''),'base_path'=>'','menu_position'=>'top/sub')
	function __construct($module_name,$args=null){
			xydac()->modules->$module_name =  $this;
			
		//initialise variables
		$this->module_name = $module_name;
		$this->module_label = (!empty($args) && is_array($args) && isset($args['module_label']) && !empty($args['module_label'])) ? $args['module_label'] : $module_name;
		$this->has_custom_fields = (!empty($args) && is_array($args) && isset($args['has_custom_fields']) && !empty($args['has_custom_fields']) && true==$args['has_custom_fields']) ? true : false;
		$this->uses_active = (!empty($args) && is_array($args) && isset($args['uses_active']) && !empty($args['uses_active']) && true==$args['uses_active']) ? true : false;
		$this->registered_option = (!empty($args) && is_array($args) && isset($args['registered_option']) && !empty($args['registered_option'])) ? $args['registered_option'] : null;
		$this->base_path = (!empty($args) && is_array($args) && isset($args['base_path']) && !empty($args['base_path'])) ? $args['base_path'] : null;
		$this->menu_position = (!empty($args) && is_array($args) && isset($args['menu_position']) && !empty($args['menu_position'])) ? $args['menu_position'] : null;
		$this->xydac_sync = (!empty($args) && is_array($args) && isset($args['xydac_sync']) && !empty($args['xydac_sync'])) ? true : false;
		$this->xydac_sync = true;
		// @todo: path has not been defined.
		$this->dao = xydac()->dao;
		//Create an array to hold dao option
		$dao_opt_arr = array();
		if(is_array($this->registered_option)){
			foreach($this->registered_option as $opt=>$val)
				$dao_opt_arr[$val]=$module_name." ".$opt;
			//Register options to be used by DAO.
			$this->dao->register_option($dao_opt_arr);
		}
		if($this->has_custom_fields){
			$active_opt_arr = array();
			$active_names = $this->get_main_names();
			if(is_array($active_names))
				foreach($active_names as $opt=>$val)
				$active_opt_arr[$this->get_registered_option('field').'_'.$val]=$module_name." ".$val;
			$this->dao->register_option($active_opt_arr);
		}
		if(!xydac()->is_xydac_ucms_pro())
			$this->xydac_sync = false;
		//Creating default View Component Tabs
		if(!empty($this->base_path) && !isset($args["tabs"])){

			$this->tabs = array('module'=>array('name'=>$this->module_name,
					'href'=>$this->base_path.'&sub='.$this->module_name,
					'label'=>$this->module_label,
					'default'=>true));
			if($this->has_custom_fields){
				$this->tabs['fields']=array('name'=>$this->module_name.'_fields',
						'href'=>$this->base_path.'&sub='.$this->module_name.'_fields',
						'label'=>$this->module_label.' Fields',
						'default'=>false) ;
			}
			if(xydac()->is_xydac_ucms_pro() && $this->xydac_sync){
				$this->tabs['xydac_sync']=array('name'=>$this->module_name.'_xydac_sync',
						'href'=>$this->base_path.'&sub='.$this->module_name.'_xydac_sync',
						'label'=>$this->module_label.' Sync',
						'default'=>false) ;
			}
		}else if(isset($args["tabs"]) && !empty($args["tabs"]) && is_array($args["tabs"]))
			$this->tabs = $args["tabs"];

		//Creating Menu
		if('top'==$this->menu_position)
			add_action('admin_menu', array($this,'handle_menu'),100);
		add_action( 'init', array($this,'init') , 1);
	}

	/*-----------Getters--------------*/
	public function get_module_name(){
		return $this->module_name;
	}
	public function get_field_name(){
		return $this->module_name.'_field';
	}
	public function get_module_label(){
		return $this->module_label;
	}
	public function get_field_label(){
		return $this->module_label.' Fields';
	}
	public function get_path(){
		return $this->path;
	}
	public function has_custom_fields(){
		return $this->has_custom_fields;
	}
	public function uses_active($type=false){
		if($type && $type=='main')
			return $this->uses_active;
		return false;
	}
	public function get_registered_option($type=false){
		if(!$type || !in_array($type,$this->VALID_OPTION_LIST))
			return $this->registered_option;
		else
			return $this->registered_option[$type];
	}
	public function get_base_path($tabname=null){
		if(isset($this->tabs[$tabname]['href']) && !empty($this->tabs[$tabname]['href']))
			return $this->tabs[$tabname]['href'];
		return $this->base_path;
	}
	public function get_tabs(){
		return $this->tabs;
	}
	public function init(){
	}//function to be overrided in child class.
	/*-----------Getters--------------*/

	/*This Function is used to register an option used by Data Access Layer.
	 */
	public  function register_options($option_name,$option_type,$args=null){
		if(!in_array($option_type,$this->VALID_OPTION_LIST))
			return;
		$this->registered_option[$option_type] = $option_name;
	}
	/**
	 * This function creates a database call to fetch value
	 * @param String $type The type of option to be used viz main/active/field...
	 * @param Array $args The parameters to be supplied to get_option method of DAO
	 * @param String $name The name of Post Type/Page type or whatever
	 * @return NULL of value returned.
	 */
	private function _get_option($type,$args=null,$name=null){
		$backtrace = debug_backtrace();
		xydac()->log("_get_option name: ".$this->registered_option[$type].'_'.$name.", called by :". $backtrace[1]['function']." type:".$type." args:",$args);

		if('field'!=$type && isset($this->registered_option[$type])){
			return $this->dao->get_options($this->registered_option[$type],$args);
		}
		else if('field'==$type && null!=$name && isset($this->registered_option[$type]))
			return $this->dao->get_options($this->registered_option[$type].'_'.$name,$args);
		else
			return null;
	}
	public function _set_option($type,$args=null){
		return $this->dao->set_options($this->registered_option[$type],$args);
	}
	/*----------------------------End FInal Functions---------------------*/
	
	/*----------------------------START NEW FUNCTIONS---NOT IMPLEMENTED------------------*/
	public function is_main_active($name){
		$arr = $this->get_active_names();
		if(!empty($arr) && is_array($arr) && in_array($name,$arr))
			return true;
		else
			return false;
	}

	
	public function insert_object($type,$name,$fieldmaster,$args,$namefieldname){
		if($type=='main' || $type=='field'){
			$message='';
			$name = sanitize_title_with_dashes($name);
			if(empty($name))
				$message = new WP_Error('err', $this->module_label.__(" Name is required to create ",XYDAC_CMS_NAME).$this->module_label);
			elseif(($type=='main' && in_array($name,(array)$this->get_main_names())) || ($type=='field' && in_array($name,(array)$this->get_field_names($fieldmaster))))
				$message = new WP_Error('err', $this->module_label.__(" Name already registered !!!",XYDAC_CMS_NAME));
			elseif($name=="active"){
				$message = new WP_Error('err', $this->module_label.__(" Name Not allowed",XYDAC_CMS_NAME));
			}
			else{
				$args[$namefieldname] = sanitize_title_with_dashes($args[$namefieldname]);
				$opt = ($type=='main')?$this->get_registered_option($type):$this->get_registered_option($type).'_'.$fieldmaster;
				if($this->dao->insert_object($opt,$args))
					$message = $this->module_label.__(' Inserted.',XYDAC_CMS_NAME);
				else
					$message = new WP_Error('err', __("Not Insterted",XYDAC_CMS_NAME));
			}
			return $message;
		}else return new WP_Error('err', $this->module_label.__(" Name Not allowed",XYDAC_CMS_NAME));
	}
	//-fieldmaster : the object of whose field has to updated
	public function update_object($type,$name,$fieldmaster,$args,$oldname,$namefieldname){
		if($type=='main' || $type=='field'){
			$message='';
			$name = sanitize_title_with_dashes($name);
			$oldname = sanitize_title_with_dashes($oldname);
			if(empty($name))
				$message = new WP_Error('err', $this->module_label.__(" Name is required to create ",XYDAC_CMS_NAME).$this->module_label);
			elseif(($type=='main' && !in_array($name,$this->get_main_names())) || ($type=='field' && !in_array($name,$this->get_field_names($fieldmaster))))
			$message = new WP_Error('err', $this->module_label.__(" Name not registered !!!",XYDAC_CMS_NAME));
			elseif($name=="active"){
				$message = new WP_Error('err', $this->module_label.__(" Name Not allowed",XYDAC_CMS_NAME));
			}elseif($name!=$oldname){
				$message = new WP_Error('err', __("Changing Name is Not allowed",XYDAC_CMS_NAME));
			}
			else{
				$opt = ($type=='main')?$this->get_registered_option($type):$this->get_registered_option($type).'_'.$fieldmaster;
				if($this->dao->update_object($opt,$args,$oldname,$namefieldname))
				{
					$message = $this->module_label.__(' Updated.',XYDAC_CMS_NAME);
				}else
					$message = new WP_Error('err', __("Not Updated",XYDAC_CMS_NAME));
					
			}
			return $message;
		}else return new WP_Error('err', $this->module_label.__(" Name Not allowed",XYDAC_CMS_NAME));
	}
	public function delete_object($type,$name,$fieldmaster,$namefieldname){
		if($type=='main' || $type=='field'){
			$opt = ($type=='main')?$this->get_registered_option($type):$this->get_registered_option($type).'_'.$fieldmaster;
			if($this->dao->delete_object($opt,$name,$namefieldname))
			{
				$this->deactivate_main($name);
				return $this->module_label.__(' Deleted.',XYDAC_CMS_NAME);
			}
			else
				return new WP_Error('err', $this->module_label.__(" Not Found",XYDAC_CMS_NAME));
		}else return new WP_Error('err', $this->module_label.__(" Name Not allowed",XYDAC_CMS_NAME));
	}

	public function activate_main($name){
		$xydac_active_options = !is_array($this->get_active_names())?array(): $this->get_active_names();
		if(!in_array($name,$xydac_active_options))
			array_push($xydac_active_options,$name);
		$this->_set_option('active',$xydac_active_options );
		$message = $this->module_label.__(' Activated.',XYDAC_CMS_NAME);
		return $message;
	}
	public function deactivate_main($name){
		if(!is_array($this->get_active_names()))
			$message = new WP_Error('err', $this->module_label.__(" Not Found",XYDAC_CMS_NAME));
		$xydac_active_options = $this->get_active_names();
		if(in_array($name,$xydac_active_options))
			foreach($xydac_active_options as $k=>$xydac_option)
				if($xydac_option==$name)
					{unset($xydac_active_options[$k]);break;}
		$this->_set_option('active',$xydac_active_options );
		$message = $this->module_label.__(' Deactivated.',XYDAC_CMS_NAME);
		return $message;
	}
	function sync_main($name){
	}

	
	/*----------------------------END NEW FUNCTIONS---------------------*/
	
	/*----------------------------Start Major Getters---------------------*/
	/* This function returns the array of active items */
	function get_active($args=null){
		if(!$this->uses_active || !isset($this->registered_option['active']))
			return $this->_get_option('main',$args);
		else{
			if(null==$args)
				return $this->_get_option('main',array('values'=>$this->_get_option('active'),'is_value_array'=>'true','match_keys'=>'false','final_val_array'=>'true'));
			else
				return $this->_get_option('active',$args);
		}
	}
	/* This function returns the array of active item's name */
	function get_active_names(){
		if(!$this->uses_active || !isset($this->registered_option['active']))
			return $this->get_main_names();
		else
			return $this->_get_option('active');//,array('fields'=>array('name'),'is_value_array'=>'true')
	}
	/* This function returns the array of main items */
	function get_main($args=null){
		if(!isset($this->registered_option['main']))
			return;
		return $this->_get_option('main',$args);
	}
	/* This function returns the array of main items */
	function get_main_by_name($name=null){
		if(!isset($this->registered_option['main']) || $name==null)
			return;
		return $this->_get_option('main',array('is_value_array'=>'true','match_keys'=>'true','values'=>array('name'=>$name)));
	}
	/* This function returns the array of main item's  name */
	function get_main_names($name=null){
		xydac()->log('get_main_names');
		if(!isset($this->registered_option['main']))
			return;
		if(!empty($name))
			return $this->_get_option('main',array('fields'=>array($name),'is_value_array'=>'true'));
		else
			return $this->_get_option('main',array('fields'=>array('name'),'is_value_array'=>'true','filter'=>'value'));
	}
	/* This function returns the array of field items
	 ^ $name: specifies the object name of which field is to be fetched.
	*/
	function get_field($name,$args=null){
		if(!$this->has_custom_fields || !isset($this->registered_option['field']))
			return;
		return $this->_get_option('field',$args,$name);
	}
	function get_field_by_name($name,$field_name,$fieldname_colname=null){
		if(!$this->has_custom_fields || !isset($this->registered_option['field']))
			return;
		if(!empty($fieldname_colname)&&!empty($fieldtype_colname))
			return $this->_get_option('field',array(
					'is_value_array'=>'true','match_keys'=>'true',
					'values'=>array($fieldname_colname=>array($field_name))
			),$name);
		else
			return $this->_get_option('field',array(
					'is_value_array'=>'true','match_keys'=>'true',
					'values'=>array('field_name'=>array($field_name))
			),$name);
	}
	/* This function returns the array of field items which are active
	 ^ $name: specifies the object name of which field is to be fetched.
	*/
	function get_active_fieldtypes($name){
		if(!$this->has_custom_fields || !isset($this->registered_option['field']))
			return;
		$args = array('fields'=>array('field_type'),
				'is_value_array'=>'true',
				'filter'=>array('value')
		);
		return $this->_get_option('field',$args,$name);
	}
	/* This function returns the array of field item's name
	 ^ $name: specifies the object name(post_type) of which field is to be fetched.
	*/

	function get_field_names($name,$fieldname_colname=null){
		if(!$this->has_custom_fields || !isset($this->registered_option['field']))
			return;
		if(!empty($fieldname_colname))
			return $this->_get_option('field',array('fields'=>array($fieldname_colname),'is_value_array'=>'true','filter'=>array('value')),$name);
		else
			return $this->_get_option('field',array('fields'=>array('field_name'),'is_value_array'=>'true','filter'=>array('value')),$name);
	}
	/* This function returns the string of field's type
	 ^ $name: specifies the object name(post_type) of which field is to be fetched.
	*/
	function get_field_type($name,$field_name,$fieldname_colname=null,$fieldtype_colname=null){
		if(!$this->has_custom_fields || !isset($this->registered_option['field']))
			return;
		if(!empty($fieldname_colname)&&!empty($fieldtype_colname))
			return $this->_get_option('field',array('fields'=>array($fieldtype_colname),
					'is_value_array'=>'true',
					'values'=>array($fieldname_colname=>array($field_name)),
					'filter'=>array('value','value')
			),$name);
		else
			return $this->_get_option('field',array('fields'=>array('field_type'),
					'is_value_array'=>'true',
					'values'=>array('field_name'=>array($field_name)),
					'filter'=>array('value','value')
			),$name);
	}
	/*----------------------------End Major Getters---------------------*/
	function get_registered(){
		return $this->registered_option;
	}
	function xydac_checkbool($string)
	{
		if($string=='false')
			return false;
		else
			return true;
	}
	function xydac_singular($name)
	{
		return ((substr($name,-1)=='s') ? substr($name,0,-1) : $name);
	}
	//Supporting function used for usort
	private function xy_cmp($a, $b)
	{
		if(isset($a['field_order']) && isset($b['field_order']))
			$k = 'field_order';
		else
			$k = $this->namefield_name;
		if($a[$k]> $b[$k])
			return 1;
		elseif($a[$k]< $b[$k])
		return -1;
		else
			return 0;
	}
	/*----------------------------View Components---------------------*/
	/*Main View Function :  view_main()*/
	/* $tabs = array ('name'=>array('label','href','default'))*/
	function handle_menu()
	{
		//xydac()->$menu_slug
		add_submenu_page( 'xydac_ultimate_cms', $this->module_label, $this->module_label, 'manage_xydac_cms', 'xydac_ultimate_cms_'.$this->module_name, array($this,'view_main'));
	}
	function front_header($tabs = null){
		echo "<div class='wrap'>";
		echo '<div id="icon-options-general" class="icon32"><br></div>';
		if(!empty($tabs)){
			echo '<h2 style="border-bottom: 1px solid #CCC;padding-bottom:0px;">';
			$sub = isset($_GET['sub']) ? $_GET['sub'] : (isset($_GET['edit_xydac_'.$this->module_name])?$this->module_name : (isset($_GET['edit_'.$this->module_name.'_field'])?$this->module_name.'_fields':false));
			foreach($tabs as $tab_name=>$tab){
				?>
<a href="<?php echo $tab['href']; ?>"
	class="nav-tab <?php if(($sub && $sub===$tab['name']) || (!$sub && true ==$tab['default']))  echo 'nav-tab-active' ?>"><?php echo $tab['label']; ?>
</a>
<?php
			}
			echo '</h2> <br class="clear" />';
		}
	}
	function front_footer(){
		echo "</div>";
	}
	/*
	 * For creating view component create a function with function name 'screen_name'_func()
	*
	* $tab : 'href','label','default'
	*/
	public function view_main(){
		$sub = isset($_GET['sub']) ? $_GET['sub'] : (isset($_GET['edit_xydac_'.$this->module_name])?$this->module_name : (isset($_GET['edit_'.$this->module_name.'_field'])?$this->module_name.'_fields':false));
		$this->front_header($this->tabs);
		if($sub)
			foreach($this->tabs as $tab_name=>$tab){
			if($sub===$tab['name']){
				if(method_exists($this, 'view_'.$tab_name.'_func'))
					call_user_func(array($this, 'view_'.$tab_name.'_func'),$tab);
				else 
					do_action("xydac_cms_module_view_main",$tab["name"]);
				break;
			}
		}
		else
			foreach($this->tabs as $tab_name=>$tab)
			{
				if(method_exists($this, 'view_'.$tab_name.'_func'))
					call_user_func(array($this, 'view_'.$tab_name.'_func'),$tab);
				else 
					do_action("xydac_cms_module_view_main",$tab["name"]);
				break;
			}
			$this->front_footer();
	}
	//default tab page
	function view_module_func($tab)
	{
		$method = 'xydac_'.$this->module_name.'_manager';
		new $method();
	}
	//default field page
	function view_fields_func($tab)
	{
		if(!isset($_GET['manage_'.$this->module_name]))
		{
			$formaction = $tab['href'];
			$selectdata = $this->get_main_names();
			xydac()->log('view_fields_func',$selectdata);
			?>
<form name='manage_<?php echo $this->module_name ?>_fields'
	action='<?php echo $formaction ?>' method='get'>
	<h3>
		<?php echo __('Select the ',XYDAC_CMS_NAME).$this->module_label.__(' To manage ',XYDAC_CMS_NAME); ?>
	</h3>
	<select name='manage_<?php echo $this->module_name ?>'
		id='manage_<?php echo $this->module_name ?>' style="margin: 20px;">
		<?php foreach ($selectdata  as $name=>$label) {?>
		<option value="<?php echo $label; ?>">
			<?php echo $label; ?>
		</option>
		<?php } ?>
	</select> <input type="hidden" name="page"
		value="xydac_ultimate_cms_<?php echo $this->module_name ?>" /> <input
		type="hidden" name="sub"
		value="<?php echo $this->module_name ?>_fields" /> <input
		type="submit"
		id="manage_<?php echo $this->module_name ?>_fields_submit"
		class="button" value="Manage">
</form>
<?php }
else
{
	$method = 'xydac_'.$this->module_name.'_fields';
	new $method($_GET['manage_'.$this->module_name]);
}
	
	}
	private function show_sync_page($arr,$formaction)
	{
	$getCustomField = create_function('$resultarr,$var',"foreach({$resultarr['custom_fields']} as {$v})if( {$v['key']} == {$var})return {$v['value']};");
	echo '
	<table class="wp-list-table widefat fixed posts" cellspacing="0">
	<thead>
	<tr>
	<th scope="col" class="manage-column column-cb name-column" style="">Name</th>
	<th scope="col" class="manage-column column-cb desc-column" style="">Description</th>
	<th scope="col" class="manage-column column-cb desc-column" style="">Status</th>
	<th scope="col" class="manage-column column-cb install-column" style="">Install</th>
	</tr>
	</thead>

	<tfoot>
	<tr>
	<th scope="col" class="manage-column column-cb name-column" style="">Name</th>
	<th scope="col" class="manage-column column-cb desc-column" style="">Description</th>
	<th scope="col" class="manage-column column-cb desc-column" style="">Status</th>
	<th scope="col" class="manage-column column-cb install-column" style="">Install</th>
	</tr>
	</tfoot>
	<tbody id="the-list">';
	if(is_array($arr) && !empty($arr))
		foreach($arr as $resultarr){
			echo '<tr id="'.$resultarr['post_id'].'" valign="top">
			<td class="column-name">'.$resultarr['post_title'].'</td>
			<td class="column-desc">'.$resultarr['post_content'].'</td>
			<td class="column-desc">'.(in_array($resultarr['post_title'],$this->get_main_names())?(base64_encode(maybe_serialize($this->get_main_by_name($resultarr['post_title'])))==$getCustomField($resultarr,'actual_code'))? 'Installed (In Sync)': 'Installed (Out Of Sync)':'Not Installed').'</td>
			<td class="column-install"><a href="'.$formaction.'&activate=true&id='.$resultarr['post_id'].'&nonce='.wp_create_nonce(__FILE__).'" title="Activate" class="edit">'.'Install'.'</a></td>
			</tr>';




			//$cont = $resultarr['custom_fields'][0]['id'];
			//echo "Post Code : ".base64_decode($cont).'<br/>';
		}
	else
		echo "<tr><td colspan='4'>No Data Fetched</td></tr>";
	echo '
	</tbody>
	</table>';
	}
	//default sync page
	function view_xydac_sync_func($tab)
	{
		echo '<h1>Xydac Ultimate CMS Cloud</h1>';
		if($_GET['activate']=='true' && wp_verify_nonce($_GET['nonce'], __FILE__) && !empty($_GET['id']))
		{
			//installation process...
			$namearr = $this->get_main_names();
			$result = xydac()->xml_rpc_client('wp.getPost',$_GET['id'],array());
			if(!$result->isError() && is_array($result->getResponse())){
				$resultarr = $result->getResponse();

				if((is_array($namearr) && !in_array($resultarr['post_title'],$namearr)) || !is_array($namearr))
				{
					//can be added directly	
					echo "can be added directly";
					
				}
				else if(is_array($namearr) && in_array($resultarr['post_title'],$namearr))
				{
					//name has to changed before adding.
					echo "name has to changed before adding.";
				}
			}
				
		}else{
			$formaction = $tab['href'];
			if(xydac()->apikey){
				$result = xydac()->xml_rpc_client('wp.getPosts',null,array('post_type'=>'xydac_'.$this->module_name));
				if(!$result->isError() && is_array($result->getResponse())){
					$resultsarr = $result->getResponse();
					$publicposts = array();
					$draftposts = array();
					foreach($resultsarr as $result)
					  if($result['post_status']=='draft')
					    array_push($draftposts,$result);
					  else
					    array_push($publicposts,$result);
					echo '<h3>Private Items</h3>';					
					$this->show_sync_page($draftposts,$formaction);
					echo '<h3>Public Shared</h3>';
					$this->show_sync_page($publicposts,$formaction);
					

				}
					
			}
		}

	}
}
?>