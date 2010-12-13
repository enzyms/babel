<?php
/**
 * @package babel
 */
 
 
// 

class Babel {


    // private $id = 'null';
    
    
    function __construct ()
    {
    	global $modx;	
    	
    	$modx->regClientCSS($modx->getOption('site_url').'core/components/babel/babel.css'); 

    }
    
   
   
   
   
    public function isPropertyConfigured($property)
    {	
    	global $modx;
    	  	
    	if ($property == 'needs configuration'){
    		
    		$output = '<script type="text/javascript">';
	    	$output .= ' Ext.MessageBox.alert("Plugin Babel need configuration", "Babel properties \'contexts\' and \'contexts_names\' must be configured.<br />Go to the Babel plugin, select the properties tab and configure the properties. <br />Don\'t forget to clear the site cache.  ");';
	    	$output .= '</script>';
    		
    		$modx->event->output($output);
    		
    	}else{
    		return 'yes';
    	}
    	
    }

   
   
   
    
    
    
    public function showLangLinks($currentid, $context_names)
    {	
    	global $modx;
    	    	
    	$tv_babel_ids = $modx->getObject('modTemplateVar',array('name'=>'babel_ids'));
    	$data = $tv_babel_ids->getValue($currentid);
		
		$babel_ids = explode(":", $data);
    	
    	$output = '';
    	
    	$i = 0;
    	foreach($babel_ids as $babel_id){
    		($babel_id == $currentid)? $class = 'class="selected"' : $class = ''; 
    		$output .= '<a href="'.$modx->config['base_url'].'manager/?a=30&id='.$babel_id.'" '.$class.' >'.$context_names[$i].'</a>';
    		$i++;
    	}

    	return $output;	
    }
    
    
    
   
    
     public function showTranslateButton($currentid,$text)
    {
    	global $modx;
    	$output = '<a href="'.$modx->config['base_url'].'manager/?a=30&id='.$currentid.'&initBabel=yes" class="create">'.$text.'</a><br />';
        return $output;	
    }
    
   
   
   
   
  
   public function createBabelTV($doc, $template_id)
    {
    	global $modx;
    	
    	$tv = $modx->getObject('modTemplateVar',array('name'=>'babel_ids'));
   		
    	if ($tv==null) {
    		$fields = array(
    			'name'=>'babel_ids',
			    'type'=>'text',
			    'default_text'=>'',
			    'caption'=>'babel_ids',
			    'description'=>'id\'s of the linked pages via the babel plugin. Please don\'t edit'
			 );
			$tv = $modx->newObject('modTemplateVar', $fields); 
			$tv->save();				
    	}
    	
    	// to do checker si $tv est liée à l'actuel template au lieu de tout rebouriner….
    	
    	$templates = $modx->getCollection('modTemplate');
				
		foreach($templates as $template) {
		    $intersect = $modx->newObject('modTemplateVarTemplate');
		    $intersect->addOne($tv);
		    $intersect->addOne($template);
		    $intersect->save();
		}
		
    }





	
  	
  	public function duplicateTVs($base_doc_id, $doc_id, $tv_ids)
    {	
    	global $modx;
    	
    	if ($tv_ids[0] == 'none') return;	
    	
		foreach($tv_ids as $tv_id){
			$tv = $modx->getObject('modTemplateVar',$tv_id); 
			$tv_value = $tv->getValue($base_doc_id);			
			$tv->setValue($doc_id, $tv_value);
			$tv->save();					
		}	
    }
  
  
  
  
  
  
    public function duplicateDocument($base_doc, $context_target, $context_list)
    {
      	global $modx;
      	
      	$base_doc_id 		= $base_doc->get('id');
      	$base_doc_title     = $base_doc->get('pagetitle'). ' [needs translation]';
      	$base_doc_parent_id = $base_doc->get('parent');
      	  	  	
     	$doc = $modx->newObject('modDocument');
		$doc->fromArray($base_doc->toArray()); //duplicate all values but not the TV's! 		
		
		$doc->set('context_key',$context_target);
		
		
		if ($base_doc_parent_id!=null){
			
			$tv_babel_ids = $modx->getObject('modTemplateVar',array('name'=>'babel_ids'));
    		
    		$data = $tv_babel_ids->getValue($base_doc_parent_id);		
			
			$parent_babel_ids = explode(":", $data);
			
			for($i=0;$i<sizeof($context_list);$i++){
		    	if ($context_list[$i]==$context_target) {
		    		$parent_target = $parent_babel_ids[$i];	

					// set parent as folder:
					$base_doc_parent = $modx->getObject('modResource', $parent_babel_ids[$i]);
	    			if ($base_doc_parent==null)return;
	    			$base_doc_parent->set('isfolder',1);
	    			$base_doc_parent->save();    		
		    	}	    	
		    }
						
			$doc->set('parent',$parent_target);
		}
		
		
		//$doc->set('alias',$alias);
		//$doc->set('published',true);
		
		$doc->set('pagetitle',$base_doc_title);
		$doc->save();
		$modx->cacheManager->clearCache();
		
		$doc_id = $doc->get('id');
        
     	return $doc_id;
			
    }
    
 
  
}



