<?php
/*
Plugin Name: CrossPress 2
Version: 0.2
Description: Gracias a CrossPress 2 podremos publicar automáticamente las entradas que publiquemos en nuestro sitio web bajo WordPress en otros servicios. Creado a partir del plugin de <a href="http://www.atthakorn.com/project/crosspress/">Atthakorn Chanthong</a> <a href="http://wordpress.org/plugins/crosspress/"><strong>CrossPress</strong></a>.
Plugin URI: http://wordpress.org/plugins/crosspress-2/
Author: Art Project Group
Author URI: http://www.artprojectgroup.es/
*/

/**
 * Initializes the plugin
 */
class CrossPress
{
	var $saved = false;

	function CrossPress() {
		if($_POST['pin'] || $_POST['signature'] || $_POST['summarytext']) {

			if (get_option('crosspress_pin') || get_option('crosspress_pin') == NULL) update_option('crosspress_pin',  $_POST['pin']);
			else add_option('crosspress_pin',  $_POST['pin']);

			if (get_option('crosspress_signature') || get_option('crosspress_signature') == NULL) update_option('crosspress_signature',  $_POST['signature']);
			else add_option('crosspress_signature',  $_POST['signature']);
			
			if (get_option('crosspress_summary') || get_option('crosspress_summary') == NULL) update_option('crosspress_summary', $_POST['summarytext']);
			else add_option('crosspress_summary',  $_POST['summarytext']);

			$this->saved = true;
		}
		
		add_action('admin_menu', array(&$this, 'admin_menu'));
	}

	function admin_menu () {
		add_options_page('Opciones de CrossPress', 'CrossPress', 8, __FILE__, array(&$this, 'plugin_options'));
	}

	function plugin_options () {
		if($this->saved) {
			print "<div id=\"message\" class=\"updated fade\"><p><strong>Opciones guardadas.</strong></p></div>\n\n";
		}
		
		print '<div class="wrap">';
		print '<h2>Opciones de CrossPress</h2>';
		print '<hr />';
		print'<form style="padding-left:25px;" method="post" action="">';
		
		print '<div>';
		print '<b>PIN secreto:</b><br />';
		print 'Código PIN (correo electrónico) que le permite publicar automáticamente en su sitio/blog por correo electrónico.<br />';
		print 'Cada PIN debe intoducirse en una nueva línea.<br />';
		print 'Funciona en WordPress.com, Blogspot.com, Tumblr.com y LiveJounal.com, por ejemplo.<br />';
		print '<textarea name="pin"  cols="50" rows="5">'.stripcslashes(get_option('crosspress_pin')).'</textarea><br /><br />';
		print '<b>Firma :</b><br />';
		print 'Puede añadir una firma si lo desea. Las URLs introducidas serán convertidas en enlaces automáticamente.<br />';
		print '<textarea name="signature" cols="50" rows="5">'.stripcslashes(get_option('crosspress_signature')).'</textarea><br /><br />';
		print '<input name="summarytext" type="checkbox" value="1" '.(get_option('crosspress_summary') == "1" ? "checked":  "").'> Mostrar resumen.';
		print '</div>';
		print '<div><input type="submit" value="Guardar &raquo;"></div>';
		
		print'</form></div>';
		print '<br />';
		print '<br />';
	}

	function add_action() {
		add_action('publish_post', array(&$this, 'post_2_blog'));
	}

 	function post_2_blog($postid) {
		$post = get_post($postid);
		setup_postdata($post);
		$excerpt = get_the_excerpt();
		$content = get_the_content();
		
		//If post time is not equally to modified time, skip sending mail
		if ($post->post_date == $post->post_modified)
		{
			//Partes del correo	
			$to = htmlspecialchars($this->getValidAddress(get_option("crosspress_pin")));
			$subject = "=?UTF-8?B?".base64_encode($post->post_title)."?=";
			$headers = "Content-Type: text/html;charset=utf8";
			
			if (get_option('crosspress_summary') == "1")
			{
				if (!empty($post->post_excerpt)) $msg = the_excerpt();
				else $msg = $excerpt;
				$msg .= '<br /><br />Sigue leyendo en <a href="'.get_permalink($postid).'" title="'.$post->post_title.' en Art Project Group">'.$post->post_title.'</a>.';   
			}
			else 
			{
				if (!empty($post->post_content)) $msg = the_content();
				else $msg = $content;
			}
			
			$msg .= ' '.'<br />';
			$msg .= ' '.'<br />';
			$msg .= make_clickable(stripcslashes(get_option("crosspress_signature")));

			//Añade etiquetas y categorías exclusivamente para WordPress.com
			if (strpos($to,'wordpress.com') !== false) {
				$categorias = '';
				foreach(get_the_category($post->ID) as $category) $categorias .= $category->cat_name . ", ";
				trim($categorias, ", ");
				
				$category = "[category ".$categorias."]";
				$tags = "[tags ".get_the_tag_list('', ', ','')."]";
				
				$mensaje = $msg .'<br /><br />';
				$mensaje .= $category.'<br />';
				$mensaje .= $tags.'<br />';
				
				preg_match_all('/[\w\.=-]+@[a-zA-Z0-9_\-\.]+wordpress.com/', $to, $wordpress);
				
				if (isset($wordpress[0][0])) $to = htmlspecialchars($this->getValidAddress(get_option("crosspress_pin"), $wordpress[0][0])); //Quitamos del primer correo la cuenta de WordPress.com
			}
			
			//sending mail
			mail($to, $subject, $msg, $headers);
			
			if (isset($wordpress[0][0])) mail($wordpress[0][0], $subject, $mensaje, $headers); //A WordPress.com
		}
	
		return $postid;
	}
	
	function getValidAddress($list, $email = '') {
		$list = nl2br($list);
		if ($email) $list = str_replace($email,'', $list); //Elimina de la lista la cuenta de WordPress.com
		$order = array('<br>', '<br/>', '<br />');
		$replace = ',';
		//remove new line
		$list = str_replace($order,$replace,$list);
		//clear white space
		$list = str_replace(' ','', $list);
		return $list;
	}

}

$post2blog =& new CrossPress;
$post2blog->add_action();

?>
