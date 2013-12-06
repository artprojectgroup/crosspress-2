<?php global $crosspress; ?>

<div class="wrap">
  <h2>
    <?php _e('CrossPress Options.', 'crosspress'); ?>
  </h2>
  <hr />
  <?php
		if ($this->actualizacion) echo '<div id="message" class="updated fade"><p><strong>' . __('Options saved.', 'crosspress') . '</strong></p></div>' . PHP_EOL;
		$tab = 1;
		$configuracion = get_option('crosspress');
?>
  <h3><a href="<?php echo $crosspress['plugin_url']; ?>" title="Art Project Group"><?php echo $crosspress['plugin']; ?></a> </h3>
  <p>
    <?php _e('Post automatically the posts published in your website in other services like WordPress.com, Blogger, Google + via BufferApp.com y Tumblr.', 'crosspress'); ?>
  </p>
  <?php include('cuadro-donacion.php'); ?>
  <form method="post" action="">
    <div class="cabecera"> <a href="http://www.artprojectgroup.es/plugins-para-wordpress/crosspress-2" title="CrossPress 2"><img src="http://www.artprojectgroup.es/wp-content/artprojectgroup/crosspress-2-582x139.jpg" width="582" height="139" /></a> </div>
    <div class="campos">
      <div class="campo">
        <label for="cuenta">
          <?php _e('Email accounts of services:', 'crosspress'); ?>
        </label>
        <textarea id="cuenta" name="cuenta" cols="50" rows="5" tabindex="<?php echo $tab++; ?>"><?php echo (isset($configuracion['cuenta']) ? stripcslashes($configuracion['cuenta']) : ''); ?></textarea>
        <div>
          <?php _e('Emails that lets you automatically post on each service.', 'crosspress'); ?>
        </div>
        <div>
          <?php _e('Each email account must be entered onto a new line.', 'crosspress'); ?>
        </div>
        <div>
          <?php _e('Tested on: ', 'crosspress'); ?>
          <em>WordPress.com, Blogspot.com, BufferApp.com (Google+)
          <?php _e('and', 'crosspress'); ?>
          Tumblr.com</em>.</div>
      </div>
      <div class="campo">
        <div class="campo_izquierda">
          <label for="pagina">
            <?php _e('Pages:', 'crosspress'); ?>
          </label>
          <input id="pagina" name="pagina" type="checkbox" value="1" <?php echo (isset($configuracion['pagina']) && $configuracion['pagina'] == "1" ? "checked":  ""); ?> tabindex="<?php echo $tab++; ?>" />
          <?php _e('Post pages.', 'crosspress'); ?>
        </div>
        <div class="campo_izquierda">
          <label for="imagen">
            <?php _e('Image:', 'crosspress'); ?>
          </label>
          <input id="imagen" name="imagen" type="checkbox" value="1" <?php echo (isset($configuracion['imagen']) && $configuracion['imagen'] == "1" ? "checked":  ""); ?> tabindex="<?php echo $tab++; ?>" />
          <?php _e('Post image.', 'crosspress'); ?>
        </div>
        <div class="campo_izquierda">
          <label for="publicacion">
            <?php _e('Full content:', 'crosspress'); ?>
          </label>
          <input id="publicacion" name="publicacion" type="checkbox" value="1" <?php echo (isset($configuracion['publicacion']) && $configuracion['publicacion'] == "1"  ? "checked":  ""); ?> tabindex="<?php echo $tab++; ?>" />
          <?php _e('Post full content.', 'crosspress'); ?>
        </div>
        <div class="campo_izquierda">
          <label for="extracto">
            <?php _e('Excerpt:', 'crosspress'); ?>
          </label>
          <input id="extracto" name="extracto" type="checkbox" value="1" <?php echo (isset($configuracion['extracto']) && $configuracion['extracto'] == "1" ? "checked":  ""); ?> tabindex="<?php echo $tab++; ?>" />
          <?php _e('Post excerpt.', 'crosspress'); ?>
        </div>
        <div class="campo_izquierda">
          <label for="extracto_enlaces">
            <?php _e('Excerpt with links:', 'crosspress'); ?>
          </label>
          <input id="extracto_enlaces" name="extracto_enlaces" type="checkbox" value="1" <?php echo (isset($configuracion['extracto_enlaces']) && $configuracion['extracto_enlaces'] == "1" ? "checked":  ""); ?> tabindex="<?php echo $tab++; ?>" />
          <?php _e('Post excerpt with links.', 'crosspress'); ?>
          <span class="nota">
          <?php _e('Except for', 'crosspress'); ?>
          BufferApp.com (Google+).</span> </div>
      </div>
      <?php
		//Inicializamos datos
		if (!isset($configuracion['enlace']) || $configuracion['enlace'] === NULL) $enlace = __('Continue reading on ', 'crosspress');
		else $enlace = $configuracion['enlace'];
		if (!isset($configuracion['fuente']) || $configuracion['fuente'] === NULL) $fuente = __('Original source: ', 'crosspress'); 
		else $fuente = $configuracion['fuente'];
		
		$enlace_original = network_site_url('/') . urlencode(__('publication-name', 'crosspress'));
		$titulo = __('Publication name', 'crosspress');
		$enlace_original = '<a href="' . $enlace_original . '" title="' . $titulo . __(' in ', 'crosspress') . get_bloginfo('name').'">' . $titulo . '</a>';
?>
      <div class="campo">
        <label for="enlace">
          <?php _e('Excerpt Link:', 'crosspress'); ?>
        </label>
        <input type="text" id="enlace" name="enlace" size="50" value="<?php echo stripcslashes($enlace); ?>" tabindex="<?php echo $tab++; ?>" />
        <div>
          <?php _e('You can customize, if desired, the link after the excerpt of the publication.', 'crosspress'); ?>
          <?php _e('It\'s very important to leave a blank space at the end.', 'crosspress'); ?>
        </div>
        <div>
          <?php _e('The excerpt link now looks like this:', 'crosspress'); ?>
        </div>
        <div class="enlace" id="texto_enlace"><em><?php echo $enlace . $enlace_original; ?></em>.</div>
      </div>
      <div class="campo">
        <label for="fuente">
          <?php _e('Post link:', 'crosspress'); ?>
        </label>
        <input type="text" id="fuente" name="fuente" size="50" value="<?php echo stripcslashes($fuente); ?>" tabindex="<?php echo $tab++; ?>" />
        <div>
          <?php _e('If you wish you can customize the link that appears after the full post. It\'s very important to leave a blank space.', 'crosspress'); ?>
          <?php _e('It\'s very important to leave a blank space at the end.', 'crosspress'); ?>
        </div>
        <div>
          <?php _e('The post link now looks like this:', 'crosspress'); ?>
        </div>
        <div class="enlace" id="texto_fuente"><em><?php echo $fuente . $enlace_original; ?></em>.</div>
      </div>
      <div class="campo">
        <label for="firma">
          <?php _e('Signature:', 'crosspress'); ?>
        </label>
        <textarea id="firma" name="firma" cols="50" rows="5" tabindex="<?php echo $tab++; ?>"><?php echo (isset($configuracion['firma']) ? stripcslashes($configuracion['firma']) : ''); ?>
</textarea>
        <div>
          <?php _e('If desired you can add a signature. Introduced URLs will be converted into links automatically.', 'crosspress'); ?>
        </div>
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
