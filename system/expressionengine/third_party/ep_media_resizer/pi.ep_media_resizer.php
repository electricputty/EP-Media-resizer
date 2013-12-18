<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Include config file
require PATH_THIRD.'ep_media_resizer/config.php';

$plugin_info = array(
	'pi_name'	=>	$config['name'],
	'pi_version'	=>	$config['version'],
	'pi_author'	=>	'Malcolm Elsworth',
	'pi_author_url'	=>	'http://labs.electricputty.co.uk/',
	'pi_description'=>	'Resizes any embedded media element to a predefined width / height',
	'pi_usage'	=>	Ep_media_resizer::usage()
);



class Ep_media_resizer {

	/**
	* Plugin return data
	*
	* @var	string
	*/
	var $return_data;
	
	var $ee_tags;

	var $the_width;
	var $the_height;
	var $obj_width;
	var $obj_height;
	var $new_width;
	var $new_height;
	
	var $debug;
	var $debug_str;

	
	// --------------------------------------------------------------------

	/**
	* PHP4 Constructor
	*
	* @see	__construct()
	*/
	function Ep_media_resizer()
	{

		/* ---------------------------------------------------------------------
		  Get global instance
		--------------------------------------------------------------------- */
		$this->EE =& get_instance();



		/* ---------------------------------------------------------------------
		  Get some params
		--------------------------------------------------------------------- */
		$the_text = $this->EE->TMPL->tagdata;
		
		$this->the_width = $this->EE->TMPL->fetch_param('width') ? $this->EE->TMPL->fetch_param('width') : '';
		$this->the_height = $this->EE->TMPL->fetch_param('height') ? $this->EE->TMPL->fetch_param('height') : '';
		$this->debug = $this->EE->TMPL->fetch_param('debug') ? $this->EE->TMPL->fetch_param('debug') : '';
		
		$this->debug_str = "";
		$this->debug_str .= "THE_WIDTH: ".$this->the_width."<br />";
		$this->debug_str .= "THE_HEITGH: ".$this->the_height."<br />";
		
		$this->tag_counter = 0;



		/* ---------------------------------------------------------------------
		  Remove any curly brackets as these may get mashed up by dom parsing
		--------------------------------------------------------------------- */
		$tag_pattern = "/{(.*)}/";		
		preg_match_all($tag_pattern, $the_text, $this->ee_tags, PREG_SET_ORDER);
		foreach ($this->ee_tags as $tag)
		{
			$the_text = str_replace($tag[0], '[['.$this->tag_counter.']]', $the_text);
			$this->tag_counter = $this->tag_counter + 1;
		}



		/* ---------------------------------------------------------------------
		  Create a new DomDoc object and load in the HTML
		--------------------------------------------------------------------- */
		$the_html = new DomDocument();
		@$the_html->loadHTML($the_text);



		/* ---------------------------------------------------------------------
		  Update the width and height on all "object" elements
		--------------------------------------------------------------------- */
		foreach($the_html->getElementsByTagName('object') as $element)
		{
			$this->obj_width = $element->getAttribute('width');
			$this->obj_height = $element->getAttribute('height');
			
			$this->debug_str .= "OBJECT OBJ_WIDTH: ".$this->obj_width."<br />";
			$this->debug_str .= "OBJECT OBJ_HEITGH: ".$this->obj_height."<br />";
			
			$this->new_width = $this->get_new_width();
			$this->new_height = $this->get_new_height();
		
			$this->debug_str .= "OBJECT NEW_WIDTH: ".$this->new_width."<br />";
			$this->debug_str .= "OBJECT NEW_HEIGHT: ".$this->new_height."<br />";
			
			$element->setAttribute('width', $this->new_width);
			$element->setAttribute('height', $this->new_height);
		}



		/* ---------------------------------------------------------------------
		  Update the width and height on all "embed" elements
		--------------------------------------------------------------------- */
		foreach($the_html->getElementsByTagName('embed') as $element)
		{
			$this->obj_width = $element->getAttribute('width');
			$this->obj_height = $element->getAttribute('height');
			
			$this->debug_str .= "EMBED OBJ_WIDTH: ".$this->obj_width."<br />";
			$this->debug_str .= "EMBED OBJ_HEITGH: ".$this->obj_height."<br />";
			
			$this->new_width = $this->get_new_width();
			$this->new_height = $this->get_new_height();
		
			$this->debug_str .= "EMBED NEW_WIDTH: ".$this->new_width."<br />";
			$this->debug_str .= "EMBED NEW_HEIGHT: ".$this->new_height."<br />";
			
			$element->setAttribute('width', $this->new_width);
			$element->setAttribute('height', $this->new_height);
		}



		/* ---------------------------------------------------------------------
		  Update the width and height on all "iframe" elements
		  Vimeo have just started using an iframe to embed videos
		--------------------------------------------------------------------- */
		foreach($the_html->getElementsByTagName('iframe') as $element)
		{
			$this->obj_width = $element->getAttribute('width');
			$this->obj_height = $element->getAttribute('height');
			
			$this->debug_str .= "IFRAME OBJ_WIDTH: ".$this->obj_width."<br />";
			$this->debug_str .= "IFRAME OBJ_HEITGH: ".$this->obj_height."<br />";
			
			$this->new_width = $this->get_new_width();
			$this->new_height = $this->get_new_height();
		
			$this->debug_str .= "IFRAME NEW_WIDTH: ".$this->new_width."<br />";
			$this->debug_str .= "IFRAME NEW_HEIGHT: ".$this->new_height."<br />";
			
			$element->setAttribute('width', $this->new_width);
			$element->setAttribute('height', $this->new_height);
		}



		/* ---------------------------------------------------------------------
		  Turn the Dom object back into a string (We only want the contents of the body element)
		--------------------------------------------------------------------- */
		$the_text = $the_html->saveHTML();
		preg_match_all("/<body([^>]*)>(.*)<\/body>/s", $the_text, $body_html, PREG_SET_ORDER);
		if(count($body_html) > 0) 
		{
			$the_text = $body_html[0][2];
		}


		/* ---------------------------------------------------------------------
		  Re-instate the EE tags
		--------------------------------------------------------------------- */
		$this->tag_counter = 0;
		foreach ($this->ee_tags as $tag)
		{
			$the_text = str_replace('[['.$this->tag_counter.']]', $tag[0], $the_text);
			$this->tag_counter = $this->tag_counter + 1;
		}



		/* ---------------------------------------------------------------------
		  If the secret 'debug' param is set to true, return the dimension calculations
		--------------------------------------------------------------------- */
		if($this->debug == 'true')
		{
			$this->return_data = $this->debug_str.$the_text;
		} 
		else 
		{
			$this->return_data = $the_text;
		}

	}



	// Do we have a defined width, if not calculate it from the defined height
	function get_new_width()
	{
		if ($this->the_width != '')
		{
			return $this->the_width;
		} 
		else
		{
			// If we have a specified height AND the element's height is not set with a percentage
			if (($this->the_height != '') && (strpos($this->obj_height,'%') === false)) 
			{
				$multiplier = $this->the_height / $this->obj_height;
				return ceil($this->obj_width * $multiplier);
			} 
			else
			{
				return $this->obj_width;
			}
		}
	}



	// Do we have a defined height, if not calculate it from the defined width
	function get_new_height()
	{
		if ($this->the_height != '')
		{
			 return $this->the_height;
		} 
		else 
		{
			// If we have a specified width AND the element's width is not set with a percentage
			if (($this->the_width != '') && (strpos($this->obj_width,'%') === false))
			{
				$multiplier = $this->the_width / $this->obj_width;
				return ceil($this->obj_height * $multiplier);
			} 
			else 
			{
				return $this->obj_height;
			}
		}
	}
	
	
	// ----------------------------------------
	//	Plugin Usage
	// ----------------------------------------

	// This function describes how the plugin is used.
	function usage()
	{
		ob_start(); 
		?>
			The Media Object plugin uses a PHP HTML Parser to find all Object, Embed and Iframe elemets within the submitted code. 
			It then finds their height and width parameters and updates these to the sibmitted value(s).


			Usage:
			-----------------
			If you make a single field for your embed code you just need to wrap the Plugin tags around your field output.
			{exp:ep_media_resizer width="550"}YOUR MEDIA EMBED CODE HERE{/exp:ep_media_resizer}


			Parms:
			-----------------
			~ 'height' The desired height of the media object - if not specified the new height will be calculated automatically from the desired width
			~ 'width' The desired width of the media object - if not specified the new width will be calculated automatically from the desired height

			NOTE: If neither height or width param is set the object will be returned at original size
		<?php
		$buffer = ob_get_contents();
	
		ob_end_clean(); 

		return $buffer;
	}
	// END

}
// END CLASS
?>