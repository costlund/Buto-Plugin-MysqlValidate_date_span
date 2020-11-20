<?php
class PluginMysqlValidate_date_span{
  private $settings;
  private $mysql;
  function __construct() {
    wfPlugin::includeonce('wf/yml');
    wfPlugin::includeonce('wf/array');
    wfPlugin::includeonce('wf/mysql');
    $this->mysql = new PluginWfMysql();
    $this->settings = wfPlugin::getPluginSettings('mysql/validate_date_span', true);
    wfPlugin::includeonce('i18n/translate_v1');
  }
  private function db_open(){
    $this->mysql->open($this->settings->get('data/mysql'));
  }
  private function getSql($key){
    return new PluginWfYml(__DIR__.'/mysql/sql.yml', $key);
  }
  private function db_check_conflict($data){
    /**
     * 
     */
    $blank = false;
    if(wfRequest::get($data->get('items/date1')) && wfRequest::get($data->get('items/date2'))){
      $sql = $this->getSql('db_check_conflict_date1_date2');
    }elseif(wfRequest::get($data->get('items/date1'))){
      $sql = $this->getSql('db_check_conflict_date1');
    }elseif(wfRequest::get($data->get('items/date2'))){
      $sql = $this->getSql('db_check_conflict_date2');
    }else{
      $sql = $this->getSql('db_check_conflict_blank');
      $blank = true;
    }
    /**
     * 
     */
    $sql->set('sql', str_replace('[table_name]', $data->get('db/table_name'), $sql->get('sql')));
    $sql->set('sql', str_replace('[date1]', $data->get('db/date1'), $sql->get('sql')));
    $sql->set('sql', str_replace('[date2]', $data->get('db/date2'), $sql->get('sql')));
    /**
     * 
     */
    $sql->setByTag(array('date1' => wfRequest::get($data->get('items/date1'))));
    $sql->setByTag(array('date2' => wfRequest::get($data->get('items/date2'))));
    /**
     * 
     */
    $key_fields_sql = '';
    if($data->get('db/key_fields')){
      foreach($data->get('db/key_fields') as $value){
        $key_fields_sql .= ' and '.$value['db'].'=?';
        $sql->set('params/', array('type' => 's', 'value' => wfRequest::get($value['get'])));
      }
    }
    if($blank && $key_fields_sql){
      $key_fields_sql = substr($key_fields_sql, 4);
    }
    $sql->set('sql', str_replace('[key_fields]', $key_fields_sql, $sql->get('sql')));
    /**
     * 
     */
    $this->db_open();
    $this->mysql->execute($sql->get());
    $rs = $this->mysql->getMany();
    /**
     * Set clean_up_key if not exist.
     */
    if(!$data->get('clean_up_key')){
      $data->set('clean_up_key', 'id');
    }
    /**
     * Clean up key
     */
    if(wfRequest::get($data->get('clean_up_key'))){
      foreach($rs as $k => $v){
        if($v['id']==wfRequest::get($data->get('clean_up_key'))){
          unset($rs[$k]);
        }
      }
    }
    /**
     * 
     */
    return sizeof($rs);
  }
  public function validate_date_span($field, $form, $data = array()){
    $form = new PluginWfArray($form);
    $data = new PluginWfArray($data);
    $i18n = new PluginI18nTranslate_v1();
    $i18n->setPath('/plugin/mysql/validate_date_span/i18n');
    /**
     * Check if dates is in right order.
     */
    if($form->get("items/".$data->get('items/date1')."/is_valid") && $form->get("items/".$data->get('items/date2')."/is_valid")){
      if($form->get("items/".$data->get('items/date1')."/post_value") && $form->get("items/".$data->get('items/date2')."/post_value")){
        if(strtotime($form->get("items/".$data->get('items/date1')."/post_value")) > strtotime($form->get("items/".$data->get('items/date2')."/post_value"))){
          $form->set("items/$field/is_valid", false);
          $form->set("items/$field/errors/", $i18n->translateFromTheme("Date to is before date from."));
        }
      }
    }
    /**
     * Check conflikt.
     */
    if($form->get("items/".$data->get('items/date1')."/is_valid") && $form->get("items/".$data->get('items/date2')."/is_valid")){
      if($this->db_check_conflict($data)){
        $form->set("items/$field/is_valid", false);
        $form->set("items/$field/errors/", $i18n->translateFromTheme("Date overlapping error."));
      }
    }
    return $form->get();    
  }
}
