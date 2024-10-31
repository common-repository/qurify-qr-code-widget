<?php

/*
Plugin Name: QR Code Plugin for Wordpress
Version: 1.2
Plugin URI: http://www.qurify.com/
Description: Qurify and display whatever QR Code you want and (optional) let your visitors qurify their own QR Codes, directly on your blog!
Author: Yocter.com
Author URI: http://yocter.com/
*/

/*  
		Check qurify.com or WordPress.org for new updates.

		Copyright 2009  Yocter.com | Godfried van Loo

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


define('MAGPIE_CACHE_AGE', 120);


$qurify_options['widget_fields']['qurify_url'] = array('label'=>'<b>Your Blog URL:</b>:', 'type'=>'text', 'default'=>'http://');
$qurify_options['widget_fields']['qurify_visitors'] = array('label'=>'<b>Let visitors qurify messages?</b> (yes/no):', 'type'=>'text', 'default'=>'yes');
$qurify_options['widget_fields']['qurify_subtitle'] = array('label'=>'<b>Title:</b> (optional):', 'type'=>'text', 'default'=>'My qurified URL:');
$qurify_options['widget_fields']['qurify_txt'] = array('label'=>'<b>Small Text:</b> (optional):', 'type'=>'text', 'default'=>'My blog URL, qurified into a QR Code.');

$qurify_options['prefix'] = 'qurify';

// Display Qurify  Widget
function qurify_show($qurifySettings) {
	global $qurify_options;

	print '<script type="text/javascript"><!-- //Qurify Gadget by yocter.com
	var qurify_url = "'.$qurifySettings["qurify_url"].'";
	var qurify_visitors = "'.$qurifySettings["qurify_visitors"].'";
	var qurify_txt = "'.$qurifySettings["qurify_txt"].'";
	var qurify_curvesize = "'.$qurifySettings["qurify_curvesize"].'";
	//--></script>
	<div style="text-align:center;margin-bottom:10px;padding:10px;border:0;font-size:10px;line-height:1.2;"><div style="font-size:11px;font-weight:bold;text-align:center;margin-bottom:5px;"><a href="http://www.qurify.com/" title="Qurify.com"><img style="border:0;" src="http://www.qurify.com/favicon.png" title="Qurify.com" /></a> '.$qurifySettings["qurify_subtitle"].'</div><div id="graphimage"></div>
	<div style="margin-top:10px;">'.$qurifySettings["qurify_txt"].'</div>';
	
		if(strtolower($qurifySettings["qurify_visitors"]) != "no") {
			print '<div style="border:1px dotted #999999;padding:4px;margin-top:10px;background-color:#3278BC" id="goqurify"><a href="#" onclick="switchOn();return false;" style="color:#ffffff" title="Qurify your own QR Code here!">Qurify your own QR Code here! &raquo;</a></div>';
		}
	
	print '<div style="margin-top:10px;"><a style="padding:0;margin:0;line-height:1;font-size:9px;font-family:sans-serif;" href="http://www.qurify.com/" title="QR Codes by Qurify.com">QR Codes by Qurify.com</a></div>
	</div><script type="text/javascript" src="http://gadgets.qurify.com/qurify_gadgets/wordpress/qurify_bloggadget_wordpress.js"></script>';
}

// Profile widget stuff
function widget_qurify_init() {

	if ( !function_exists('register_sidebar_widget') )
		return;
	
	$check_options = get_option('widget_qurify');
  if ($check_options['number']=='') {
    $check_options['number'] = 1;
    update_option('widget_qurify', $check_options);
  }
  
	function widget_qurify($args, $number = 1) {

		global $qurify_options;
		
		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);

		// Each widget can store its own options. We keep strings here.
		include_once(ABSPATH . WPINC . '/rss.php');
		$options = get_option('widget_qurify');
		
		// fill options with default values if value is not set
		$item = $options[$number];
		foreach($qurify_options['widget_fields'] as $key => $field) {
			if (! isset($item[$key])) {
				$item[$key] = $field['default'];
			}
		}
		

		// These lines generate our output.
		echo $before_widget;
  	qurify_show($item);
  	echo $after_widget;
	}

	// This is the function that outputs the form to let the users edit
	// the widget's title. It's an optional feature that users cry for.
	function widget_qurify_control($number) {
	
		global $qurify_options;

		// Get our options and see if we're handling a form submission.
		$options = get_option('widget_qurify');
		if ( isset($_POST['qurify-submit']) ) {

			foreach($qurify_options['widget_fields'] as $key => $field) {
				$options[$number][$key] = $field['default'];
				$field_name = sprintf('%s_%s_%s', $qurify_options['prefix'], $key, $number);

				if ($field['type'] == 'text') {
					$options[$number][$key] = strip_tags(stripslashes($_POST[$field_name]));
				} elseif ($field['type'] == 'checkbox') {
					$options[$number][$key] = isset($_POST[$field_name]);
				}
			}

			update_option('widget_qurify', $options);
		}

		foreach($qurify_options['widget_fields'] as $key => $field) {
			
			$field_name = sprintf('%s_%s_%s', $qurify_options['prefix'], $key, $number);
			$field_checked = '';
			if ($field['type'] == 'text') {
				$field_value = htmlspecialchars($options[$number][$key], ENT_QUOTES);
			} elseif ($field['type'] == 'checkbox') {
				$field_value = 1;
				if (! empty($options[$number][$key])) {
					$field_checked = 'checked="checked"';
				}
			}
			
			printf('<p style="text-align:right;" class="qurify_field"><label for="%s">%s <input id="%s" name="%s" type="%s" value="%s" class="%s" %s /></label></p>',
				$field_name, __($field['label']), $field_name, $field_name, $field['type'], $field_value, $field['type'], $field_checked);
		}

		echo '<input type="hidden" id="qurify-submit" name="qurify-submit" value="1" />';
	}
	
	function widget_qurify_setup() {
		$options = $newoptions = get_option('widget_qurify');
		
		if ( isset($_POST['qurify-number-submit']) ) {
			$number = (int) $_POST['qurify-number'];
			$newoptions['number'] = $number;
		}
		
		if ( $options != $newoptions ) {
			update_option('widget_qurify', $newoptions);
			widget_qurify_register();
		}
	}
		
	function widget_qurify_register() {
		
		$options = get_option('widget_qurify');
		$dims = array('width' => 300, 'height' => 300);
		$class = array('classname' => 'widget_qurify');

		for ($i = 1; $i <= 9; $i++) {
			$name = sprintf(__('Qurify Gadget'), $i);
			$id = "qurify-$i"; // Never never never translate an id
			wp_register_sidebar_widget($id, $name, $i <= $options['number'] ? 'widget_qurify' : /* unregister */ '', $class, $i);
			wp_register_widget_control($id, $name, $i <= $options['number'] ? 'widget_qurify_control' : /* unregister */ '', $dims, $i);
		}
		
		add_action('sidebar_admin_setup', 'widget_qurify_setup');
	}

	widget_qurify_register();
}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'widget_qurify_init');
?>