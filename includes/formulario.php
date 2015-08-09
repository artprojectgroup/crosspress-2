<?php global $crosspress, $entradas, $tipos_prohibidos; ?>

<div class="wrap">
  <h2>
    <?php _e( 'CrossPress Options.', 'crosspress' ); ?>
  </h2>
  <?php
		if ( $this->actualizacion ) {
			echo '<div id="message" class="updated fade"><p><strong>' . __( 'Options saved.', 'crosspress' ) . '</strong></p></div>' . PHP_EOL;
		}
		$tab = 1;
		$configuracion = get_option( 'crosspress' );
		
		//Inicializamos datos
		if ( !isset( $configuracion['enlace'] ) || $configuracion['enlace'] === NULL ) {
			$enlace = __( 'Continue reading on ', 'crosspress' );
		} else {
			$enlace = $configuracion['enlace'];
		}
		if ( !isset( $configuracion['fuente'] ) || $configuracion['fuente'] === NULL ) {
			$fuente = __( 'Original source: ', 'crosspress' ); 
		} else {
			$fuente = $configuracion['fuente'];
		}
		
		$enlace_original = network_site_url( '/' ) . urlencode( __( 'publication-name', 'crosspress' ) );
		$titulo = __( 'Publication name', 'crosspress' );
		$enlace_original = '<a href="' . $enlace_original . '" title="' . $titulo . __( ' in ', 'crosspress' ) . get_bloginfo( 'name' ).'">' . $titulo . '</a>';
  ?>
  <h3><a href="<?php echo $crosspress['plugin_url']; ?>" title="Art Project Group"><?php echo $crosspress['plugin']; ?></a> </h3>
  <p>
    <?php _e( 'Post automatically the posts published in your website in other services like WordPress.com, Blogger and Google + via BufferApp.com.', 'crosspress' ); ?>
  </p>
  <?php include( 'cuadro-informacion.php' ); ?>
  <form method="post" action="">
    <div class="cabecera"> <a href="<?php echo $crosspress['plugin_url']; ?>" title="<?php echo $crosspress['plugin']; ?>" target="_blank"><img src="<?php echo plugins_url( '../assets/images/cabecera.jpg', __FILE__ ); ?>" class="imagen" alt="<?php echo $crosspress['plugin']; ?>" /></a> </div>
    <table class="form-table apg-table">
      <tbody>
        <tr valign="top">
          <th scope="row"><label for="cuenta">
              <?php _e( 'Email accounts of services:', 'crosspress' ); ?>
            </label>
          </th>
          <td><textarea id="cuenta" name="cuenta" cols="50" rows="5" tabindex="<?php echo $tab++; ?>"><?php echo ( isset( $configuracion['cuenta'] ) ? stripcslashes( $configuracion['cuenta'] ) : '' ); ?></textarea>
            <p class="description">
              <?php _e( 'Emails that lets you automatically post on each service.', 'crosspress' ); ?>
              <br />
              <?php _e( 'Each email account must be entered onto a new line.', 'crosspress' ); ?>
              <br />
              <?php _e( 'Tested on: ', 'crosspress' ); ?>
              <em>WordPress.com, Blogspot.com
              <?php _e( 'and', 'crosspress' ); ?>
              BufferApp.com (Google+)</em>.</p></td>
        </tr>
        <?php if ( $entradas || isset( $configuracion['entradas'] ) ) { ?>
        <tr valign="top">
          <th scope="row"><label for="cuenta">
              <?php _e( 'Custom post types:', 'crosspress' ); ?>
            </label>
          </th>
          <td><textarea id="entradas" name="entradas" cols="50" rows="5" tabindex="<?php echo $tab++; ?>"><?php echo ( isset( $configuracion['entradas'] ) ? stripcslashes( $configuracion['entradas'] ) : $entradas ); ?></textarea>
            <p class="description">
              <?php _e( 'Custom post types used to post on each service.', 'crosspress' ); ?>
              <br />
              <?php _e( 'Each custom post type must be entered onto a new line.', 'crosspress' ); ?>
              <br />
              <?php 
				$contador = 1;
				foreach ( $tipos_prohibidos as $tipo_prohibido ) {
					if ( $contador < count( $tipos_prohibidos ) - 1 ) {
						echo $tipo_prohibido . ",";
					} else if ( $contador < count( $tipos_prohibidos ) ) {
						echo $tipo_prohibido . " " . __( 'and', 'crosspress' ) . " ";
					} else {
						echo $tipo_prohibido;
					}
					$contador++;
			  	}
				?>
              <?php _e( 'are your others unused custom post types.', 'crosspress' ); ?>
            </p></td>
        </tr>
        <?php } ?>
        <tr valign="top">
          <th scope="row"><?php _e( 'Pages:', 'crosspress' ); ?>
          </th>
          <td><input id="pagina" name="pagina" type="checkbox" value="1" <?php echo ( isset( $configuracion['pagina'] ) && $configuracion['pagina'] == "1" ? "checked":  "" ); ?> tabindex="<?php echo $tab++; ?>" />
            <label for="pagina">
              <?php _e( 'Post pages.', 'crosspress' ); ?>
            </label></td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e( 'Image:', 'crosspress' ); ?>
          </th>
          <td><input id="imagen" name="imagen" type="checkbox" value="1" <?php echo ( isset( $configuracion['imagen'] ) && $configuracion['imagen'] == "1" ? "checked":  "" ); ?> tabindex="<?php echo $tab++; ?>" />
            <label for="imagen">
              <?php _e( 'Post image.', 'crosspress' ); ?>
            </label></td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e( 'Full content:', 'crosspress' ); ?>
          </th>
          <td><input id="publicacion" name="publicacion" type="checkbox" value="1" <?php echo ( isset( $configuracion['publicacion'] ) && $configuracion['publicacion'] == "1"  ? "checked":  "" ); ?> tabindex="<?php echo $tab++; ?>" />
            <label for="publicacion">
              <?php _e( 'Post full content.', 'crosspress' ); ?>
            </label></td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e( 'Excerpt:', 'crosspress' ); ?>
          </th>
          <td><input id="extracto" name="extracto" type="checkbox" value="1" <?php echo ( isset( $configuracion['extracto'] ) && $configuracion['extracto'] == "1" ? "checked":  "" ); ?> tabindex="<?php echo $tab++; ?>" />
            <label for="extracto">
              <?php _e( 'Post excerpt.', 'crosspress' ); ?>
            </label></td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e( 'Excerpt with links:', 'crosspress' ); ?>
          </th>
          <td><input id="extracto_enlaces" name="extracto_enlaces" type="checkbox" value="1" <?php echo ( isset( $configuracion['extracto_enlaces'] ) && $configuracion['extracto_enlaces'] == "1" ? "checked":  "" ); ?> tabindex="<?php echo $tab++; ?>" />
            <label for="extracto_enlaces">
              <?php _e( 'Post excerpt with links.', 'crosspress' ); ?>
            </label>
            <p class="description">
              <?php _e( 'Except for', 'crosspress' ); ?>
              BufferApp.com (Google+).</p></td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="enlace">
              <?php _e( 'Excerpt Link:', 'crosspress' ); ?>
            </label>
          </th>
          <td><input type="text" id="enlace" name="enlace" size="50" value="<?php echo stripcslashes( $enlace ); ?>" tabindex="<?php echo $tab++; ?>" />
            <p class="description">
              <?php _e( 'You can customize, if desired, the link after the excerpt of the publication.', 'crosspress' ); ?>
              <?php _e( 'It\'s very important to leave a blank space at the end.', 'crosspress' ); ?>
              <br />
              <?php _e( 'The excerpt link now looks like this:', 'crosspress' ); ?>
              <br />
              <code><?php echo $enlace . $enlace_original; ?></code>.</p></td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="fuente">
              <?php _e( 'Post link:', 'crosspress' ); ?>
            </label>
          </th>
          <td><input type="text" id="fuente" name="fuente" size="50" value="<?php echo stripcslashes( $fuente ); ?>" tabindex="<?php echo $tab++; ?>" />
            <p class="description">
              <?php _e( 'If you wish you can customize the link that appears after the full post. It\'s very important to leave a blank space.', 'crosspress' ); ?>
              <?php _e( 'It\'s very important to leave a blank space at the end.', 'crosspress' ); ?>
              <br />
              <?php _e( 'The post link now looks like this:', 'crosspress' ); ?>
              <br />
              <code><?php echo $fuente . $enlace_original; ?></code>.</p></td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="firma">
              <?php _e( 'Signature:', 'crosspress' ); ?>
            </label>
          </th>
          <td><textarea id="firma" name="firma" cols="50" rows="5" tabindex="<?php echo $tab++; ?>"><?php echo ( isset( $configuracion['firma'] ) ? stripcslashes( $configuracion['firma'] ) : '' ); ?>
</textarea>
            <p class="description">
              <?php _e( 'If desired you can add a signature. Introduced URLs will be converted into links automatically.', 'crosspress' ); ?>
            </p></td>
        </tr>
      </tbody>
    </table>
    <p class="submit">
      <input class="button-primary" type="submit" value="<?php _e( 'Save Changes', 'crosspress' ); ?>"  name="submit" id="submit" tabindex="<?php echo $tab++; ?>" />
    </p>
  </form>
</div>
<script type="text/javascript">
jQuery( document ).ready( function( $ ) {
	$( "#publicacion" ).click( function () {
		if ( $( "#extracto" ).is( ':checked' ) ) {
			$( "#extracto" ).attr( 'checked', false );
		}
		if ( $( "#extracto_enlaces" ).is( ':checked' ) ) {
			$( "#extracto_enlaces" ).attr( 'checked', false );
		}
		control();
	} );
	$( "#extracto" ).click( function () {
		if ( $( "#publicacion" ).is( ':checked' ) ) {
			$( "#publicacion" ).attr( 'checked', false );
		}
		if ( $( "#extracto_enlaces" ).is( ':checked' ) ) {
			$( "#extracto_enlaces" ).attr( 'checked', false );
		}
		control();
	} );
	$( "#extracto_enlaces" ).click( function () {
		if ( $( "#publicacion" ).is( ':checked' ) ) {
			$( "#publicacion" ).attr( 'checked', false );
		}
		if ( $( "#extracto" ).is( ':checked' ) ) {
			$( "#extracto" ).attr( 'checked', false );
		}
		control();
	} );
				
	if ( $( "#cuenta" ).val() == "" ) {
		$( "#extracto_enlaces" ).attr( 'checked', true );
		$( "#enlace" ).val( "<?php _e( 'Continue reading on ', 'crosspress' ); ?>" );
		$( "#fuente" ).val( "<?php _e( 'Original source: ', 'crosspress' ); ?>" );
		$( "#texto_enlace" ).html( '<em><?php echo __( 'Continue reading on ', 'crosspress' ) . $enlace_original; ?></em>' );
		$( "#texto_fuente" ).html( '<em><?php echo __( 'Original source: ', 'crosspress' ) . $enlace_original; ?></em>' );
	}
				
	var control = function() {
		if ( !$( "#publicacion" ).is( ':checked' ) && !$( "#extracto" ).is( ':checked' ) && !$( "#extracto_enlaces" ).is( ':checked' ) ) {
			$( "#extracto_enlaces" ).attr( 'checked', true );
		}
	};
} );
</script> 
