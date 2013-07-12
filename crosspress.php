<?php
/*
Plugin Name: CrossPress 2
Version: 0.3
Plugin URI: http://wordpress.org/plugins/crosspress-2/
Description: Gracias a CrossPress 2 podremos publicar automáticamente las entradas que publiquemos en nuestro sitio web bajo WordPress en otros servicios. Creado a partir del plugin de <a href="http://www.atthakorn.com/project/crosspress/">Atthakorn Chanthong</a> <a href="http://wordpress.org/plugins/crosspress/"><strong>CrossPress</strong></a>.
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
		if ($_POST['pin'] || $_POST['signature'] || $_POST['summarytext']) {
			if (get_option('crosspress_pin') || get_option('crosspress_pin') == NULL) update_option('crosspress_pin',  $_POST['pin']);
			else add_option('crosspress_pin',  $_POST['pin']);

			if (get_option('crosspress_signature') || get_option('crosspress_signature') == NULL) update_option('crosspress_signature',  $_POST['signature']);
			else add_option('crosspress_signature',  $_POST['signature']);
			
			if (get_option('crosspress_summary') || get_option('crosspress_summary') == NULL) update_option('crosspress_summary', $_POST['summarytext']);
			else add_option('crosspress_summary',  $_POST['summarytext']);

			if (get_option('crosspress_resena') || get_option('crosspress_resena') == NULL) update_option('crosspress_resena', $_POST['resena']);
			else add_option('crosspress_resena',  $_POST['resena']);

			$this->saved = true;
		}
		
		add_action('admin_menu', array(&$this, 'admin_menu'));
	}

	function admin_menu () {
		add_options_page('Opciones de CrossPress', 'CrossPress', 8, __FILE__, array(&$this, 'plugin_options'));
	}

	function plugin_options () {
		if ($this->saved) echo '<div id="message" class="updated fade"><p><strong>Opciones guardadas.</strong></p></div>'.PHP_EOL;
		
		echo '<div class="wrap">'.PHP_EOL;
		echo '<h2>Opciones de CrossPress</h2>'.PHP_EOL;
		echo '<hr />'.PHP_EOL;
		echo'<form style="padding-left:25px;" method="post" action="">'.PHP_EOL;
		
		echo '<div>'.PHP_EOL;
		echo '<b>PIN secreto:</b><br />'.PHP_EOL;
		echo 'Código PIN (correo electrónico) que le permite publicar automáticamente en su sitio/blog por correo electrónico.<br />'.PHP_EOL;
		echo 'Cada PIN debe intoducirse en una nueva línea.<br />'.PHP_EOL;
		echo 'Funciona en WordPress.com, Blogspot.com, Tumblr.com y LiveJounal.com, por ejemplo.<br />'.PHP_EOL;
		echo '<textarea name="pin"  cols="50" rows="5">'.stripcslashes(get_option('crosspress_pin')).'</textarea><br /><br />'.PHP_EOL;
		
		echo '<b>Firma :</b><br />'.PHP_EOL;
		echo 'Puede añadir una firma si lo desea. Las URLs introducidas serán convertidas en enlaces automáticamente.<br />'.PHP_EOL;
		echo '<textarea name="signature" cols="50" rows="5">'.stripcslashes(get_option('crosspress_signature')).'</textarea><br /><br />'.PHP_EOL;
		
		echo '<input name="summarytext" type="checkbox" value="1" '.(get_option('crosspress_summary') == "1" ? "checked":  "").'> Mostrar resumen.<br /><br />'.PHP_EOL;

		//Inicializamos datos
		if (get_option('crosspress_resena') == NULL) $resena = 'Sigue leyendo en ';
		else $resena = get_option('crosspress_resena');
		$enlace = network_site_url( '/' ) . "/nombre-de-la-entrada";
		$titulo = "Nombre de la entrada";
		
		echo 'Puede personalizar, si lo desea, la reseña que aparecerá tras el resumen. Es muy importante que deje un espacio en blanco tras esta.<br />'.PHP_EOL;
		echo 'La reseña del resumen actualmente tiene esta apariencia:<br />'.$resena.'<a href="'.$enlace.'" title="'.$titulo.' en '.get_bloginfo('name').'">'.$titulo.'</a>.<br />'.PHP_EOL;
		echo '<textarea name="resena" cols="50" rows="5">'.stripcslashes($resena).'</textarea><br /><br />'.PHP_EOL; 
		
		echo '</div>'.PHP_EOL;
		echo '<div><input type="submit" value="Guardar &raquo;"></div>'.PHP_EOL;
		
		echo'</form>'.PHP_EOL;
		echo '</div>'.PHP_EOL;
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
				$msg .= '<br /><br />'.get_option('crosspress_resena').'<a href="'.get_permalink($postid).'" title="'.$post->post_title.' en '.get_bloginfo('name').'">'.$post->post_title.'</a>.';   
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
			mail($to, $subject, $msg, $headers); //A todos los servicios
			
			if (isset($wordpress[0][0])) mail($wordpress[0][0], $subject, $mensaje, $headers); //Y a, si existe, a WordPress.com
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
