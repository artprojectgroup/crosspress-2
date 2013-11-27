<?php
/*
Plugin Name: CrossPress 2
Version: 1.1.1
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

//Carga el idioma
load_plugin_textdomain('crosspress', null, dirname(plugin_basename(__FILE__)) . '/lang');

//Enlaces adicionales personalizados
function crosspress_enlaces($enlaces, $archivo) {
	$plugin = plugin_basename(__FILE__);

	if ($archivo == $plugin) 
	{
		$enlaces[] = '<a href="http://www.artprojectgroup.es/plugins-para-wordpress/crosspress-2" target="_blank" title="Art Project Group">' . __('Visit the official plugin website', 'crosspress') . '</a>';
		$enlaces[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SK3B33K9YA3S4" target="_blank" title="PayPal"><img alt="CrossPress 2" src="' . __('https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif', 'crosspress') . '" width="53" height="15" style="vertical-align:text-bottom;"></a>';
	}
	
	return $enlaces;
}
add_filter('plugin_row_meta', 'crosspress_enlaces', 10, 2);

//Añade el botón de configuración
function crosspress_enlace_de_ajustes($enlaces) { 
	$enlace_de_ajustes = '<a href="options-general.php?page=crosspress-2/crosspress.php" title="' . __('Settings', 'crosspress') . '">' . __('Settings', 'crosspress') . '</a>'; 
	array_unshift($enlaces, $enlace_de_ajustes); 
	
	return $enlaces; 
}
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'crosspress_enlace_de_ajustes');

//Clase que hace funcionar el plugin
class CrossPress {
	var $actualizacion = false;
	
	function __construct() {
		if ($_POST['cuenta'] || $_POST['pagina'] || $_POST['imagen'] || $_POST['publicacion'] || $_POST['extracto'] || $_POST['extracto_enlaces'] || $_POST['enlace'] || $_POST['fuente'] || $_POST['firma']) {
			if (get_option('crosspress_cuenta') || get_option('crosspress_cuenta') == NULL) update_option('crosspress_cuenta',  $_POST['cuenta']);
			else add_option('crosspress_cuenta', $_POST['cuenta']);
			
			if (get_option('crosspress_pagina') || get_option('crosspress_pagina') == NULL) update_option('crosspress_pagina', $_POST['pagina']);
			else add_option('crosspress_pagina', $_POST['pagina']);

			if (get_option('crosspress_imagen') || get_option('crosspress_imagen') == NULL) update_option('crosspress_imagen', $_POST['imagen']);
			else add_option('crosspress_imagen', $_POST['imagen']);

			if (get_option('crosspress_publicacion') || get_option('crosspress_publicacion') == NULL) update_option('crosspress_publicacion', $_POST['publicacion']);
			else add_option('crosspress_publicacion', $_POST['publicacion']);

			if (get_option('crosspress_extracto') || get_option('crosspress_extracto') == NULL) update_option('crosspress_extracto', $_POST['extracto']);
			else add_option('crosspress_extracto', $_POST['extracto']);

			if (get_option('crosspress_extracto_enlaces') || get_option('crosspress_extracto_enlaces') == NULL) update_option('crosspress_extracto_enlaces', $_POST['extracto_enlaces']);
			else add_option('crosspress_extracto_enlaces', $_POST['extracto_enlaces']);

			if (get_option('crosspress_enlace') || get_option('crosspress_enlace') == NULL) update_option('crosspress_enlace', $_POST['enlace']);
			else add_option('crosspress_enlace', $_POST['enlace']);

			if (get_option('crosspress_fuente') || get_option('crosspress_fuente') == NULL) update_option('crosspress_fuente', $_POST['fuente']);
			else add_option('crosspress_fuente', $_POST['fuente']);

			if (get_option('crosspress_firma') || get_option('crosspress_firma') == NULL) update_option('crosspress_firma',  $_POST['firma']);
			else add_option('crosspress_firma', $_POST['firma']);

			$this->actualizacion = true;
		}
		
		add_action('admin_menu', array($this, 'CP_menu_administrador'));
		add_action('transition_post_status', array($this, 'CP_publica'), 10, 3); 
	}
	
	//Inicializa la opción CrosPress en el menú Ajustes
	function CP_menu_administrador() {
		add_options_page(__('CrossPress Options.', 'crosspress'), 'CrossPress', 'manage_options', __FILE__, array($this, 'CP_formulario_de_configuracion'));
	}
	
	//Publica las actualizaciones
 	function CP_publica($nuevo_estado, $estado_anterior, $objeto_entrada) {
		CP_actualizador();
		
		if (get_post_type($objeto_entrada) == "feedback" || (get_option('crosspress_pagina') != "1" && get_post_type($objeto_entrada) == "page")) return $objeto_entrada; //Control para no publicar comentarios y/o páginas, en caso de que no se haya seleccionado en las opciones.
		
		if ($nuevo_estado == "publish" && $estado_anterior != "publish") 
		{
			$mensaje = $mensaje_wordpress = $mensaje_buffer = $mensaje_tumblr = $asunto_buffer = $imagen = "";
			$cuentas = array();
			$entrada = get_post($objeto_entrada);
			setup_postdata($entrada);
			$extracto = $extracto_original = trim(get_the_excerpt()); //Extracto
			$contenido = get_the_content(); //Contenido
			$contenido = str_replace('\]\]\>', ']]>', $contenido);
			$contenido = preg_replace('@<script[^>]*?>.*?</script>@si', '', $contenido);
			$contenido = apply_filters('the_content', $contenido);
			
			//Tratamos la imagen
			if (has_post_thumbnail($entrada->ID) && get_option('crosspress_imagen') == "1") $imagen = get_post_thumbnail_id($entrada->ID); //Imagen destacada
			else if (CP_devuelve_la_imagen($entrada->ID, $contenido)) $imagen = CP_devuelve_la_imagen($entrada->ID, $contenido); //Primera imagen de la publicación
			$imagen = CP_procesa_la_imagen($imagen);

			//Creamos el enlace
			$enlace = get_permalink($entrada->ID);
			$enlace_html = '<a href="' . $enlace . '" title="' . $entrada->post_title . __(' in ', 'crosspress') . get_bloginfo('name') . '">';
			$imagen = $enlace_html . $imagen . '</a>';
			$enlace_html .= $entrada->post_title . '</a>.';
			
			//Creamos un extracto con los enlaces incluidos
			global $wp_filter;
			
			foreach ($wp_filter['excerpt_more'][10] as $clave => $valor) $finalizacion_de_extracto = trim($clave());
			foreach ($wp_filter['excerpt_length'][10] as $clave => $valor) $longitud_de_extracto = trim($clave());

			$contenido_filtrado = preg_replace("/\[caption.*\[\/caption\]/", '', $contenido);
			$contenido_filtrado = strip_tags($contenido_filtrado, '<a>');
			$contenido_filtrado = preg_replace("/<[^\/>][^>]*><\/[^>]+>/", '', $contenido_filtrado);
			$palabras = explode(' ', $contenido_filtrado, $longitud_de_extracto + 1);
			$extracto_con_enlaces = CP_extracto_con_enlaces($palabras, $longitud_de_extracto); //Extracto inicial
			if (preg_match_all("/<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>/siU", $extracto_con_enlaces, $enlaces, PREG_SET_ORDER)) //Si hay enlaces en el extracto, hay que ampliarlo
			{
				$palabras = explode(' ', $contenido_filtrado, $longitud_de_extracto + 1 + (count($enlaces) * 3));
				$extracto_con_enlaces = CP_extracto_con_enlaces($palabras, $longitud_de_extracto);
			}
			if (get_option('crosspress_extracto_enlaces') == "1") $extracto = CP_cierra_enlaces($extracto_con_enlaces) . $finalizacion_de_extracto;

			//Partes del correo	
			$para = htmlspecialchars(CP_procesa_cuentas(get_option("crosspress_cuenta")));
			$asunto = "=?UTF-8?B?" . base64_encode(html_entity_decode($entrada->post_title, ENT_QUOTES, 'UTF-8')) . "?=";
			$cabeceras_html = "Content-Type: text/html; charset=UTF-8\r\n";
			$cabeceras = "Content-Type: text/plain; charset=UTF-8\r\n";
			
			if (get_option('crosspress_extracto') == "1" || get_option('crosspress_extracto_enlaces') == "1") $mensaje = $extracto . "<br /><br />" . get_option('crosspress_enlace') . $enlace_html; //Muestra sólo el extracto
			else if (get_option('crosspress_publicacion') == "1") $mensaje = $contenido . "<br /><br />" . get_option('crosspress_fuente') . $enlace_html; //Muestra el contenido completo de la publicación
		
			if (get_option("crosspress_firma")) $mensaje .= "<br />" . make_clickable(stripcslashes(get_option("crosspress_firma"))); //Añade la firma

			//Añade etiquetas y categorías exclusivamente para WordPress.com
			if (strpos($para, 'wordpress.com') !== false) 
			{
				$categorias = '';
				foreach(get_the_category($entrada->ID) as $categoria) $categorias .= $categoria->name . ", ";
				$categorias = "[category ". rtrim($categorias, ", ") . "]";
				
				$etiquetas = '';
				foreach(get_the_tags($entrada->ID) as $etiqueta) $etiquetas .= $etiqueta->name . ", ";
				$etiquetas = "[tags " . rtrim($etiquetas, ", ") . "]";
			
				$mensaje_wordpress = $imagen . "<br />" . $mensaje . "<br />" . $categorias . "<br />" . $etiquetas;
				
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
			
			//Formato específico para Tumblr
			if (strpos($para, 'tumblr.com') !== false) 
			{
				$etiquetas = '';
				foreach(get_the_tags($entrada->ID) as $etiqueta) $etiquetas .= "#" . $etiqueta->name . " , ";
				$etiquetas = rtrim($etiquetas, " , ");

				$mensaje_tumblr = html_entity_decode($imagen . $mensaje, ENT_QUOTES, 'UTF-8');// . "<br />" . html_entity_decode($etiquetas, ENT_QUOTES, 'UTF-8');
				//html_entity_decode(CP_salto_de_linea($imagen . $mensaje . "<br /><br />" . $etiquetas), ENT_QUOTES, 'UTF-8');
				
				preg_match_all('/[\w\.=-]+@tumblr.com/', $para, $tumblr);
				if (isset($tumblr[0][0])) $cuentas[] = $tumblr[0][0];
			}
	
			$para = htmlspecialchars(CP_procesa_cuentas(get_option("crosspress_cuenta"), $cuentas)); //Quitamos las cuentas de WordPress.com, BufferApp.com y Tumblr.com, que se envían de forma distinta
			
			//Envía correo electrónico			
			if ($para) mail($para, $asunto, html_entity_decode($imagen . "<br />" . $mensaje, ENT_QUOTES, 'UTF-8'), $cabeceras_html); //A todos los servicios disponibles
			
			if (isset($wordpress[0][0])) mail($wordpress[0][0], $asunto, $mensaje_wordpress, $cabeceras_html); //Específico para WordPress.com
			if (isset($buffer[0][0])) mail($buffer[0][0], $asunto_buffer, $mensaje_buffer, $cabeceras); //Específico para BufferApp.com
			if (isset($tumblr[0][0])) mail($tumblr[0][0], $asunto, $mensaje_tumblr, $cabeceras_html); //Específico para Tumblr.com

			//mail('info@artprojectgroup.com', $asunto, $mensaje_tumblr, $cabeceras_html); //Control de funcionamiento
		}
	
		return $objeto_entrada;
	}
	
	//Pinta el formulario de configuración
	function CP_formulario_de_configuracion() {
		CP_actualizador();
?>
			<style type="text/css">
			div.donacion {
				background: #FFFFE0;
				border: 1px solid #E6DB55;
				float: right;
				margin: 10px 0px;
				padding: 10px;
				width: 220px;
				text-align: center;
			}
			div.donacion div {
				padding: 10px;
				margin: 10px auto 0px;
				width: 190px;
				border-top: 1px solid #E6DB55;
			}
			.cabecera img { 
				border:4px solid #888888;
			}
			form, .enlace { 
				padding-left:25px;
			}
			label {
				font-weight:bold;
				display:block;
				margin-top:10px;
				cursor: pointer;
				float: none;
				margin-bottom: 3px;
			}
			.campos {
				margin-bottom:15px;
			}
			.campo {
				margin:15px 0px;
				clear:both;
			}
			.campo_izquierda {
				float:left;
				margin:-15px 15px 15px 0px !important;
			}
			.campo div {
    			color: #555;
    			font-weight: normal;
    			margin-left: 15px;
			}
			.nota {
				color:#777;
				font-style:italic;
				font-size:90%;
				display:block;
			}
			.guardar {
				margin-top:10px;
			}
			input[type="text"], textarea {
				 background-color: #FCFCFC;
				 border: 1px solid #E0E0E0;
				 color: #696868;
				 font-weight: 300;
				 min-width: 188px;
				 padding: 8px 10px;
			}
			input[type="text"] {
				 max-width: 98%;
				 width: 300px;
			}
			textarea {
				 float: none;
				 height: 150px;
				 width: 25%;
				 min-width:582px;
			}
			input[type="submit"] {
				background: #fcfcfc;
				-webkit-box-shadow: 0 0 3px rgba(255,255,255,1) inset;
				-moz-box-shadow: 0 0 3px rgba(255,255,255,1) inset;
				box-shadow: 0 0 3px rgba(255,255,255,1) inset;
				background: -webkit-gradient(linear, left top, left bottom, from(#fcfcfc), to(#e2e2e2)); /* Webkit */
				background: -moz-linear-gradient(top,  #fcfcfc,  #e2e2e2); /* Firefox */
				filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fcfcfc', endColorstr='#e2e2e2'); /* Internet Explorer */
				border: 1px solid #D9D9D9;
				border-radius: 3px;
				color: #3B3B39 !important;
				padding: 7px 1.7em;
				text-shadow: 0 1px 0 white;
			}
			input:focus, textarea:focus, input:hover, textarea:hover {
				border-color: #D9001D;
				outline: medium none;
				-webkit-box-shadow: 0 0 5px rgba(217, 0, 29,0.75);
				-moz-box-shadow: 0 0 5px rgba(217, 0, 29,0.75);
				box-shadow: 0 0 5px rgba(217, 0, 29,0.75);
			}
			
			input[type="submit"]:focus, input[type="submit"]:hover {
    			cursor: pointer;
    			text-decoration: none;
			}
			</style>
			<div class="wrap">
				<h2><?php _e('CrossPress Options.', 'crosspress'); ?></h2>
				<hr />
<?php
		if ($this->actualizacion) echo '<div id="message" class="updated fade"><p><strong>' . __('Options saved.', 'crosspress') . '</strong></p></div>' . PHP_EOL;
		$tab = 1;
?>            
				<div class="donacion"><?php _e('If you enjoyed and find helpful this plugin, please make a donation.', 'crosspress'); ?>
					<div><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SK3B33K9YA3S4" target="_blank" title="PayPal"><img alt="CrossPress 2" border="0" src="<?php _e('https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif', 'crosspress'); ?>" width="92" height="26"></a></div>
				</div>
				<form method="post" action="">
					<div class="cabecera">
						<a href="http://www.artprojectgroup.es/plugins-para-wordpress/crosspress-2" title="CrossPress 2"><img src="http://www.artprojectgroup.es/wp-content/artprojectgroup/crosspress-2-582x139.jpg" width="582" height="139" /></a>
					</div>
					<div class="campos">
						<div class="campo">
                        <label for="cuenta"><?php _e('Email accounts of services:', 'crosspress'); ?></label>
                        <textarea id="cuenta" name="cuenta" cols="50" rows="5" tabindex="<?php echo $tab++; ?>"><?php echo stripcslashes(get_option('crosspress_cuenta')); ?></textarea>
                        <div><?php _e('Emails that lets you automatically post on each service.', 'crosspress'); ?></div>
                        <div><?php _e('Each email account must be entered onto a new line.', 'crosspress'); ?></div>
                        <div><?php _e('Tested on: ', 'crosspress'); ?><em>WordPress.com, Blogspot.com, BufferApp.com (Google+) <?php _e('and', 'crosspress'); ?> Tumblr.com</em>.</div>
						</div>
						<div class="campo">
                        <div class="campo_izquierda">
                        	<label for="pagina"><?php _e('Pages:', 'crosspress'); ?></label>
                        	<input id="pagina" name="pagina" type="checkbox" value="1" <?php echo (get_option('crosspress_pagina') == "1" ? "checked":  ""); ?> tabindex="<?php echo $tab++; ?>" /> <?php _e('Post pages.', 'crosspress'); ?>
                        </div>
                        <div class="campo_izquierda">
                        	<label for="imagen"><?php _e('Image:', 'crosspress'); ?></label>
                        	<input id="imagen" name="imagen" type="checkbox" value="1" <?php echo (get_option('crosspress_imagen') == "1" ? "checked":  ""); ?> tabindex="<?php echo $tab++; ?>" /> <?php _e('Post image.', 'crosspress'); ?>
                        </div>
                        <div class="campo_izquierda">
                        	<label for="publicacion"><?php _e('Full content:', 'crosspress'); ?></label>
                        	<input id="publicacion" name="publicacion" type="checkbox" value="1" <?php echo (get_option('crosspress_publicacion') == "1"  ? "checked":  ""); ?> tabindex="<?php echo $tab++; ?>" /> <?php _e('Post full content.', 'crosspress'); ?>
                        </div>
                        <div class="campo_izquierda">
                        	<label for="extracto"><?php _e('Excerpt:', 'crosspress'); ?></label>
                        	<input id="extracto" name="extracto" type="checkbox" value="1" <?php echo (get_option('crosspress_extracto') == "1" ? "checked":  ""); ?> tabindex="<?php echo $tab++; ?>" /> <?php _e('Post excerpt.', 'crosspress'); ?>
                        </div>
                        <div class="campo_izquierda">
                        	<label for="extracto_enlaces"><?php _e('Excerpt with links:', 'crosspress'); ?></label>
                        	<input id="extracto_enlaces" name="extracto_enlaces" type="checkbox" value="1" <?php echo (get_option('crosspress_extracto_enlaces') == "1" ? "checked":  ""); ?> tabindex="<?php echo $tab++; ?>" /> <?php _e('Post excerpt with links.', 'crosspress'); ?> <span class="nota"><?php _e('Except for', 'crosspress'); ?> BufferApp.com (Google+).</span>
                        </div>
						</div>
<?php
		//Inicializamos datos
		if (get_option('crosspress_enlace') === NULL) $enlace = __('Continue reading on ', 'crosspress');
		else $enlace = get_option('crosspress_enlace');
		if (get_option('crosspress_fuente') === NULL) $fuente = __('Original source: ', 'crosspress'); 
		else $fuente = get_option('crosspress_fuente');
		
		$enlace_original = network_site_url('/') . urlencode(__('publication-name', 'crosspress'));
		$titulo = __('Publication name', 'crosspress');
		$enlace_original = '<a href="' . $enlace_original . '" title="' . $titulo . __(' in ', 'crosspress') . get_bloginfo('name').'">' . $titulo . '</a>';
?>		
						<div class="campo">
                        <label for="enlace"><?php _e('Excerpt Link:', 'crosspress'); ?></label>
                        <input type="text" id="enlace" name="enlace" size="50" value="<?php echo stripcslashes($enlace); ?>" tabindex="<?php echo $tab++; ?>" />
                        <div><?php _e('You can customize, if desired, the link after the excerpt of the publication.', 'crosspress'); ?> <?php _e('It\'s very important to leave a blank space at the end.', 'crosspress'); ?></div>
                        <div><?php _e('The excerpt link now looks like this:', 'crosspress'); ?></div>
                        <div class="enlace" id="texto_enlace"><em><?php echo $enlace . $enlace_original; ?></em>.</div>
						</div>
						<div class="campo">
                        <label for="fuente"><?php _e('Post link:', 'crosspress'); ?></label>
                        <input type="text" id="fuente" name="fuente" size="50" value="<?php echo stripcslashes($fuente); ?>" tabindex="<?php echo $tab++; ?>" />
                        <div><?php _e('If you wish you can customize the link that appears after the full post. It\'s very important to leave a blank space.', 'crosspress'); ?> <?php _e('It\'s very important to leave a blank space at the end.', 'crosspress'); ?></div>
                        <div><?php _e('The post link now looks like this:', 'crosspress'); ?></div>
                        <div class="enlace" id="texto_fuente"><em><?php echo $fuente . $enlace_original; ?></em>.</div>
						</div>
						<div class="campo">
                        <label for="firma"><?php _e('Signature:', 'crosspress'); ?></label>
                        <textarea id="firma" name="firma" cols="50" rows="5" tabindex="<?php echo $tab++; ?>"><?php stripcslashes(get_option('crosspress_firma')); ?></textarea>
                        <div><?php _e('If desired you can add a signature. Introduced URLs will be converted into links automatically.', 'crosspress'); ?></div>
						</div>
					</div>
					<div class="guardar">
                    <input type="submit" value="<?php _e('Save &raquo;', 'crosspress'); ?>" name="submit" id="submit" tabindex="<?php echo $tab++; ?>" />
                  </div>
				</form>
			</div>
			<script type="text/javascript">
			jQuery(document).ready(function($) {
				$("#publicacion").click(function () {
					if ($("#extracto").is(':checked')) $("#extracto").attr('checked', false);
					if ($("#extracto_enlaces").is(':checked')) $("#extracto_enlaces").attr('checked', false);
					control();
				});
				$("#extracto").click(function () {
					if ($("#publicacion").is(':checked')) $("#publicacion").attr('checked', false);
					if ($("#extracto_enlaces").is(':checked')) $("#extracto_enlaces").attr('checked', false);
					control();
				});
				$("#extracto_enlaces").click(function () {
					if ($("#publicacion").is(':checked')) $("#publicacion").attr('checked', false);
					if ($("#extracto").is(':checked')) $("#extracto").attr('checked', false);
					control();
				});
				
				if ($("#cuenta").val() == "") 
				{
					$("#extracto_enlaces").attr('checked', true);
					$("#enlace").val("<?php _e('Continue reading on ', 'crosspress'); ?>");
					$("#fuente").val("<?php _e('Original source: ', 'crosspress'); ?>");
					$("#texto_enlace").html('<em><?php echo __('Continue reading on ', 'crosspress') . $enlace_original; ?></em>');
					$("#texto_fuente").html('<em><?php echo __('Original source: ', 'crosspress') . $enlace_original; ?></em>');
				}
				
				var control = function() {
					if (!$("#publicacion").is(':checked') && !$("#extracto").is(':checked') && !$("#extracto_enlaces").is(':checked')) $("#extracto_enlaces").attr('checked', true);
				};
			});
			</script>
<?php
	}
}

//Procesa las cuentas de correo electrónico
function CP_procesa_cuentas($listado, $cuentas = '') {
	$listado = nl2br($listado);
	if ($cuentas) foreach ($cuentas as $cuenta) $listado = str_replace($cuenta, '', $listado); //Elimina de la lista las cuentas enviadas

	$salto_de_linea = array('<br>', '<br/>', '<br />');
	$listado = str_replace($salto_de_linea, ', ', $listado) ; //remove new line
	$listado = str_replace(' ', '', $listado); //clear white space
	$listado = trim(trim(trim($listado), ', ')); //Elimina las comas y espacios en blanco
		
	return $listado;
}

//Actualiza y borra los valores viejos de la base de datos
function CP_actualizador() {
	$campos = array('crosspress_pin' => 'crosspress_cuenta', 'crosspress_signature' => 'crosspress_firma', 'crosspress_summary' => 'crosspress_extracto', 'crosspress_resena' => 'crosspress_enlace', 'crosspress_resumen' => 'crosspress_extracto');
	foreach ($campos as $campo_viejo => $campo_nuevo)
	{
		if (get_option($campo_viejo))
		{
			add_option($campo_nuevo, get_option($campo_viejo));
			delete_option($campo_viejo);
		}
	}
}

//Crea el extracto con enlaces
function CP_extracto_con_enlaces($extracto, $longitud) {
	if (count($extracto) > $longitud) 
	{
		array_pop($extracto);
		$extracto = implode(' ', $extracto);
	}
	
	return rtrim($extracto);
}

//Cierra los enlaces abiertos
function CP_cierra_enlaces ($html) {
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
function CP_devuelve_la_imagen($entrada, $contenido) {
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
function CP_procesa_la_imagen($imagen) {
	if (function_exists('mfrh_rename_media_on_publish')) mfrh_rename_media_on_publish($entrada->ID); //Renombra la imagen si existe y está activo Media File Renamer
	$alt = get_post_meta($imagen, '_wp_attachment_image_alt', true);
	$tamano = wp_get_attachment_image_src($imagen, 'large');
	//if ($tamano[2] > $tamano[1]) $tamano = wp_get_attachment_image_src($imagen, 'medium');
	$imagen = $tamano[0];
	$src = "data:image/" . pathinfo($imagen, PATHINFO_EXTENSION) . ";base64," . base64_encode(file_get_contents($imagen));

	return '<img src="' . $imagen . '" alt="' . $alt . '" style="max-width:100%;display:block;text-align:center;" />';
}

//Quita los saltos de línea HTML
function CP_salto_de_linea($string) {
	return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

//Iniciamos el plugin
new CrossPress;
?>
