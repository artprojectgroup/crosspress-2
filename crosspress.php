<?php
/*
Plugin Name: CrossPress 2
Version: 1.3.1
Plugin URI: http://wordpress.org/plugins/crosspress-2/
Description: With CrossPress 2 you can post automatically to other services the publications of your WordPress website. Created from <a href="http://www.atthakorn.com/project/crosspress/" target="_blank">Atthakorn Chanthong</a> <a href="http://wordpress.org/plugins/crosspress/" target="_blank"><strong>CrossPress</strong></a> plugin.
Author: Art Project Group
Author URI: http://www.artprojectgroup.es/

Text Domain: crosspress
Domain Path: /lang
License: GPL2
*/

/*  Copyright 2013  artprojectgroup  (email : info@artprojectgroup.es)

    This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//Definimos las variables
$crosspress = array(	'plugin' => 'CrossPress 2', 
						'plugin_uri' => 'crosspress-2', 
						'plugin_url' => 'http://www.artprojectgroup.es/plugins-para-wordpress/crosspress-2', 
						'paypal' => 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SK3B33K9YA3S4', 
						'ajustes' => 'options-general.php?page=crosspress-2/crosspress.php', 
						'puntuacion' => 'http://wordpress.org/support/view/plugin-reviews/crosspress-2');

//Carga el idioma
load_plugin_textdomain('crosspress', null, dirname(plugin_basename(__FILE__)) . '/lang');

//Enlaces adicionales personalizados
function crosspress_enlaces($enlaces, $archivo) {
	global $crosspress;

	$plugin = plugin_basename(__FILE__);

	if ($archivo == $plugin) 
	{
		$plugin = crosspress_plugin($crosspress['plugin_uri']);
		$enlaces[] = '<a href="' . $crosspress['paypal'] . '" target="_blank" title="' . __('Make a donation by ', 'crosspress') . 'PayPal"><span class="icon-paypal"></span></a>';
		$enlaces[] = '<a href="'. $crosspress['plugin_url'] . '" target="_blank" title="' . $crosspress['plugin'] . '"><strong class="artprojectgroup">APG</strong></a>';
		$enlaces[] = '<a href="https://www.facebook.com/artprojectgroup" title="' . __('Follow us on ', 'crosspress') . 'Facebook" target="_blank"><span class="icon-facebook6"></span></a> <a href="https://twitter.com/artprojectgroup" title="' . __('Follow us on ', 'crosspress') . 'Twitter" target="_blank"><span class="icon-social19"></span></a> <a href="https://plus.google.com/+ArtProjectGroupES" title="' . __('Follow us on ', 'crosspress') . 'Google+" target="_blank"><span class="icon-google16"></span></a> <a href="http://es.linkedin.com/in/artprojectgroup" title="' . __('Follow us on ', 'crosspress') . 'LinkedIn" target="_blank"><span class="icon-logo"></span></a>';
		$enlaces[] = '<a href="http://profiles.wordpress.org/artprojectgroup/" title="' . __('More plugins on ', 'crosspress') . 'WordPress" target="_blank"><span class="icon-wordpress2"></span></a>';
		$enlaces[] = '<a href="mailto:info@artprojectgroup.es" title="' . __('Contact with us by ', 'crosspress') . 'e-mail"><span class="icon-open21"></span></a> <a href="skype:artprojectgroup" title="' . __('Contact with us by ', 'crosspress') . 'Skype"><span class="icon-social6"></span></a>';
		$enlaces[] = '<div class="star-holder rate"><div style="width:' . esc_attr(str_replace(',', '.', $plugin['rating'])) . 'px;" class="star-rating"></div><div class="star-rate"><a title="' . __('***** Fantastic!', 'crosspress') . '" href="' . $crosspress['puntuacion'] . '?rate=5#postform" target="_blank"><span></span></a> <a title="' . __('**** Great', 'crosspress') . '" href="' . $crosspress['puntuacion'] . '?rate=4#postform" target="_blank"><span></span></a> <a title="' . __('*** Good', 'crosspress') . '" href="' . $crosspress['puntuacion'] . '?rate=3#postform" target="_blank"><span></span></a> <a title="' . __('** Works', 'crosspress') . '" href="' . $crosspress['puntuacion'] . '?rate=2#postform" target="_blank"><span></span></a> <a title="' . __('* Poor', 'crosspress') . '" href="' . $crosspress['puntuacion'] . '?rate=1#postform" target="_blank"><span></span></a></div></div>';
	}
	
	return $enlaces;
}
add_filter('plugin_row_meta', 'crosspress_enlaces', 10, 2);

//Añade el botón de configuración
function crosspress_enlace_de_ajustes($enlaces) { 
	global $crosspress;

	$enlace_de_ajustes = '<a href="' . $crosspress['ajustes'] . '" title="' . __('Settings of ', 'crosspress') . $crosspress['plugin'] . '">' . __('Settings', 'crosspress') . '</a>'; 
	array_unshift($enlaces, $enlace_de_ajustes); 
	
	return $enlaces; 
}
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'crosspress_enlace_de_ajustes');

//Clase que hace funcionar el plugin
class CrossPress {
	var $actualizacion = false;
	
	function __construct() {
		crosspress_actualizador();
		$campos = array('cuenta', 'pagina', 'imagen', 'publicacion', 'extracto', 'extracto_enlaces', 'enlace', 'fuente', 'firma');
		foreach ($campos as $campo) if (isset($_POST[$campo])) $this->actualizacion = true;
		
		if ($this->actualizacion) 
		{
			$campos_chequeo = array('pagina', 'imagen', 'publicacion', 'extracto', 'extracto_enlaces');
			foreach ($campos_chequeo as $campo) if (!isset($_POST[$campo])) $_POST[$campo] = 0;
			
			$configuracion = array();
			foreach ($campos as $campo) $configuracion[$campo] = $_POST[$campo];
			
			if (get_option('crosspress') || get_option('crosspress') == NULL) update_option('crosspress', $configuracion);
			else add_option('crosspress', $configuracion);
		}
		
		add_action('admin_menu', array($this, 'crosspress_menu_administrador'));
		add_action('transition_post_status', array($this, 'crosspress_publica'), 10, 3); 
	}
	
	//Inicializa la opción CrosPress en el menú Ajustes
	function crosspress_menu_administrador() {
		add_options_page(__('CrossPress Options.', 'crosspress'), 'CrossPress', 'manage_options', __FILE__, array($this, 'crosspress_formulario_de_configuracion'));
	}
	
	//Publica las actualizaciones
 	function crosspress_publica($nuevo_estado, $estado_anterior, $objeto_entrada) {
		crosspress_actualizador();
		$configuracion = get_option('crosspress');
		
		if (get_post_type($objeto_entrada) == "feedback" || ($configuracion['pagina'] != "1" && get_post_type($objeto_entrada) == "page")) return $objeto_entrada; //Control para no publicar comentarios y/o páginas, en caso de que no se haya seleccionado en las opciones.

		if ($nuevo_estado == "publish" && $estado_anterior != "publish") 
		{
			$mensaje = $mensaje_wordpress = $mensaje_buffer = $asunto_buffer = $imagen = "";
			$cuentas = array();
			$entrada = get_post($objeto_entrada);
			setup_postdata($entrada);
			$extracto = $extracto_original = trim(get_the_excerpt()); //Extracto
			$contenido = get_the_content(); //Contenido
			$contenido = str_replace('\]\]\>', ']]>', $contenido);
			$contenido = preg_replace('@<script[^>]*?>.*?</script>@si', '', $contenido);
			$contenido = preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http)[\/]?([^\"'>]+)([\"'>]+)#", '$1' . home_url('/') . '$2$3', $contenido); //Convertimos en absolutos los enlaces relativos
			$contenido = apply_filters('the_content', $contenido);
			
			//Tratamos la imagen
			if (has_post_thumbnail($entrada->ID) && $configuracion['imagen'] == "1") $imagen = get_post_thumbnail_id($entrada->ID); //Imagen destacada
			else if (crosspress_devuelve_la_imagen($entrada->ID, $contenido)) $imagen = crosspress_devuelve_la_imagen($entrada->ID, $contenido); //Primera imagen de la publicación

			//Creamos el enlace
			$enlace = get_permalink($entrada->ID);
			$enlace_html = '<a href="' . $enlace . '" title="' . $entrada->post_title . __(' in ', 'crosspress') . get_bloginfo('name') . '">';
			if ($imagen)
			{		
				$imagen = crosspress_procesa_la_imagen($imagen);
				$imagen = $enlace_html . $imagen . '</a><br />';
			}
			$enlace_html .= $entrada->post_title . '</a>.';
			
			//Creamos un extracto con los enlaces incluidos
			global $wp_filter;
			
			foreach ($wp_filter['excerpt_more'][10] as $clave => $valor) $finalizacion_de_extracto = trim($clave(''));
			foreach ($wp_filter['excerpt_length'][10] as $clave => $valor) $longitud_de_extracto = trim($clave(''));

			$contenido_filtrado = preg_replace("/\[caption.*\[\/caption\]/", '', $contenido);
			$contenido_filtrado = strip_tags($contenido_filtrado, '<a>');
			$contenido_filtrado = preg_replace("/<[^\/>][^>]*><\/[^>]+>/", '', $contenido_filtrado);
			$palabras = explode(' ', $contenido_filtrado, $longitud_de_extracto + 1);
			$extracto_con_enlaces = rtrim(crosspress_extracto_con_enlaces($palabras, $longitud_de_extracto)); //Extracto inicial
			if (preg_match_all("/<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>/siU", $extracto_con_enlaces, $enlaces, PREG_SET_ORDER)) //Si hay enlaces en el extracto, hay que ampliarlo
			{
				$palabras = explode(' ', $contenido_filtrado, $longitud_de_extracto + 1 + (count($enlaces) * 3));
				$extracto_con_enlaces = crosspress_extracto_con_enlaces($palabras, $longitud_de_extracto);
			}
			if ($configuracion['extracto_enlaces'] == "1") $extracto = crosspress_cierra_enlaces($extracto_con_enlaces) . $finalizacion_de_extracto;

			//Partes del correo	
			$para = htmlspecialchars(crosspress_procesa_cuentas($configuracion['cuenta']));
			$asunto = "=?UTF-8?B?" . base64_encode(html_entity_decode($entrada->post_title, ENT_QUOTES, 'UTF-8')) . "?=";
			$cabeceras_html = "Content-Type: text/html; charset=UTF-8\r\n";
			$cabeceras = "Content-Type: text/plain; charset=UTF-8\r\n";
			
			if ($configuracion['extracto'] == "1" || $configuracion['extracto_enlaces'] == "1") $mensaje = $extracto . "<br /><br />" . $configuracion['enlace'] . $enlace_html; //Muestra sólo el extracto
			else if ($configuracion['publicacion'] == "1") $mensaje = $contenido . "<br /><br />" . $configuracion['fuente'] . $enlace_html; //Muestra el contenido completo de la publicación
		
			if ($configuracion['firma']) $mensaje .= "<br />" . make_clickable(stripcslashes($configuracion['firma'])); //Añade la firma

			//Añade etiquetas y categorías exclusivamente para WordPress.com
			if (strpos($para, 'wordpress.com') !== false) 
			{
				$categorias = '';
				foreach(get_the_category($entrada->ID) as $categoria) $categorias .= $categoria->name . ", ";
				$categorias = "[category ". rtrim($categorias, ", ") . "]";
				
				$etiquetas = '';
				foreach(get_the_tags($entrada->ID) as $etiqueta) $etiquetas .= $etiqueta->name . ", ";
				$etiquetas = "[tags " . rtrim($etiquetas, ", ") . "]";
			
				$mensaje_wordpress = $imagen . $mensaje . "<br />" . $categorias . "<br />" . $etiquetas;
				
				preg_match_all('/[\w\.=-]+@[a-zA-Z0-9_\-\.]+wordpress.com/', $para, $wordpress);
				if (isset($wordpress[0][0])) $cuentas[] = $wordpress[0][0];
			}
			
			//Formato específico para Buffer (No soporta enlaces, sólo el extracto)
			if (strpos($para, 'bufferapp.com') !== false) 
			{
				$asunto_buffer = "=?UTF-8?B?" . base64_encode(html_entity_decode($extracto_original, ENT_QUOTES, 'UTF-8')) . "?=";
				$mensaje_buffer = $enlace . "\n" . "@now";
				
				preg_match_all('/[\w\.=-]+@[a-zA-Z0-9_\-\.]+bufferapp.com/', $para, $buffer);
				if (isset($buffer[0][0])) $cuentas[] = $buffer[0][0];
			}
	
			$para = htmlspecialchars(crosspress_procesa_cuentas($configuracion['cuenta'], $cuentas)); //Quitamos las cuentas de WordPress.com, BufferApp.com y Tumblr.com, que se envían de forma distinta
			
			//Envía correo electrónico			
			if ($para) mail($para, $asunto, html_entity_decode($imagen . $mensaje, ENT_QUOTES, 'UTF-8'), $cabeceras_html); //A todos los servicios disponibles
			
			if (isset($wordpress[0][0])) mail($wordpress[0][0], $asunto, $mensaje_wordpress, $cabeceras_html); //Específico para WordPress.com
			if (isset($buffer[0][0])) mail($buffer[0][0], $asunto_buffer, $mensaje_buffer, $cabeceras); //Específico para BufferApp.com

			//mail('info@artprojectgroup.com', $asunto, html_entity_decode($imagen . "<br />" . $mensaje, ENT_QUOTES, 'UTF-8'), $cabeceras_html); //Control de funcionamiento
		}
	
		return $objeto_entrada;
	}
	
	//Pinta el formulario de configuración
	function crosspress_formulario_de_configuracion() {
		$this->crosspress_publica("publish", "temporal", 2);
		wp_enqueue_style('crosspress_hoja_de_estilo'); //Carga la hoja de estilo
		include('formulario.php');
	}
}

//Procesa las cuentas de correo electrónico
function crosspress_procesa_cuentas($listado, $cuentas = '') {
	$listado = nl2br($listado);
	if ($cuentas) foreach ($cuentas as $cuenta) $listado = str_replace($cuenta, '', $listado); //Elimina de la lista las cuentas enviadas

	$salto_de_linea = array('<br>', '<br/>', '<br />');
	$listado = str_replace($salto_de_linea, ', ', $listado) ; //remove new line
	$listado = str_replace(' ', '', $listado); //clear white space
	$listado = trim(trim(trim($listado), ', ')); //Elimina las comas y espacios en blanco
		
	return $listado;
}

//Actualiza y borra los valores viejos de la base de datos
function crosspress_actualizador() {
	$configuracion = array();
	$campos = array('crosspress_pin' => 'crosspress_cuenta', 'crosspress_signature' => 'crosspress_firma', 'crosspress_summary' => 'crosspress_extracto', 'crosspress_resena' => 'crosspress_enlace', 'crosspress_resumen' => 'crosspress_extracto', 'crosspress_cuenta' => 'cuenta', 'crosspress_pagina' => 'pagina', 'crosspress_imagen' => 'imagen', 'crosspress_publicacion' => 'publicacion', 'crosspress_extracto' => 'extracto', 'crosspress_extracto_enlaces' => 'extracto_enlaces', 'crosspress_enlace' => 'enlace', 'crosspress_fuente' => 'fuente', 'crosspress_firma' => 'firma');
	foreach ($campos as $campo_viejo => $campo_nuevo)
	{
		if (get_option($campo_viejo))
		{
			$configuracion[$campo_nuevo] = get_option($campo_viejo);
			delete_option($campo_viejo);
		}
	}
	
	if (count($configuracion) > 0)
	{
		if (get_option('crosspress') || get_option('crosspress') == NULL) update_option('crosspress', $configuracion);
		else add_option('crosspress', $configuracion);
	}
}

//Crea el extracto con enlaces
function crosspress_extracto_con_enlaces($extracto, $longitud) {
	if (count($extracto) > $longitud) 
	{
		array_pop($extracto);
		$extracto = implode(' ', $extracto);
	}
	
	return $extracto;
}

//Cierra los enlaces abiertos
function crosspress_cierra_enlaces ($html) {
	preg_match_all("#<([a-z]+)(.*)?(?!/)>#iU", $html, $etiquetas);
	$etiquetas_abiertas = $etiquetas[1];

	preg_match_all("#</([a-z]+)>#iU", $html, $etiquetas);
	$etiquetas_cerradas = $etiquetas[1];
	
	$abiertas = count($etiquetas_abiertas);
	if (count($etiquetas_cerradas) == $abiertas) return $html; //Todas las etiquetas están cerradas
	
	$etiquetas_abiertas = array_reverse($etiquetas_abiertas);
	for($i = 0; $i < $abiertas; $i++) //Cerramos las etiquetas abiertas
	{
		if (!in_array ($etiquetas_abiertas[$i], $etiquetas_cerradas)) $html .= "</" . $etiquetas_abiertas[$i] . ">";
		else unset ($etiquetas_cerradas[array_search ($etiquetas_abiertas[$i], $etiquetas_cerradas)]);
	}
	
	return $html;
}

//Buscamos la primera imagen
function crosspress_devuelve_la_imagen($entrada, $contenido) {
	$argumentos = array(
		'numberposts' => 1,
		'order' => 'ASC',
		'post_mime_type' => 'image',
		'post_parent' => $entrada,
		'post_status' => null,
		'post_type' => 'attachment',
	);

	$adjuntos = get_children($argumentos);
	if ($adjuntos) foreach ($adjuntos as $adjunto) return $adjunto->ID;
	
	//Si no encuentra imagen adjunta, la buscamos en el contenido
	preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $contenido, $imagenes);

	if (isset($imagenes[1][0])) 
	{
		global $wpdb;
	
		$adjuntos = $wpdb->get_col($wpdb->prepare("SELECT ID FROM " . $wpdb->prefix . "posts" . " WHERE guid='%s';", preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $imagenes[1][0]))); 

		return $adjuntos[0]; 
	}
	
	return NULL;
}

//Procesa la imagen
function crosspress_procesa_la_imagen($imagen) {
	if (function_exists('mfrh_rename_media_on_publish')) mfrh_rename_media_on_publish($entrada->ID); //Renombra la imagen si existe y está activo Media File Renamer
	$alt = get_post_meta($imagen, '_wp_attachment_image_alt', true);
	$tamano = wp_get_attachment_image_src($imagen, 'large');
	//if ($tamano[2] > $tamano[1]) $tamano = wp_get_attachment_image_src($imagen, 'medium');
	$imagen = $tamano[0];
	$src = "data:image/" . pathinfo($imagen, PATHINFO_EXTENSION) . ";base64," . base64_encode(file_get_contents($imagen));

	return '<img src="' . $imagen . '" alt="' . $alt . '" style="max-width:100%;display:block;text-align:center;" />';
}

//Quita los saltos de línea HTML
function crosspress_salto_de_linea($string) {
	return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

//Iniciamos el plugin
new CrossPress;

//Obtiene toda la información sobre el plugin
function crosspress_plugin($nombre) {
	$argumentos = (object) array('slug' => $nombre);
	$consulta = array('action' => 'plugin_information', 'timeout' => 15, 'request' => serialize($argumentos));
	$url = 'http://api.wordpress.org/plugins/info/1.0/';
	$respuesta = wp_remote_post($url, array('body' => $consulta));
	$plugin = unserialize($respuesta['body']);
	//echo '<pre>' . print_r($plugin, true) . '</pre>';
	
	return get_object_vars($plugin);
}

//Comprueba si hay que mostrar el mensaje de configuración
function crosspress_muestra_mensaje() {
	wp_register_style('crosspress_hoja_de_estilo', plugins_url('style.css', __FILE__)); //Carga la hoja de estilo
	wp_register_style('crosspress_fuentes', plugins_url('fonts/stylesheet.css', __FILE__)); //Carga la hoja de estilo global
	wp_enqueue_style('crosspress_fuentes'); //Carga la hoja de estilo global
}
add_action('admin_init', 'crosspress_muestra_mensaje');
?>
