<?php

if ( !function_exists( 'shoestrap_phpless_compiler' ) ) :
/*
 * This function can be used to compile a less file to css using the lessphp compiler
 */
function shoestrap_phpless_compiler() {

  if ( !class_exists( 'Less_Parser' ) ) :
    require_once 'less.php/Less.php';
  endif;

  if ( shoestrap_getVariable( 'minimize_css', true ) == 1 ) :
    $options = array( 'compress'=>true );
  else :
    $options = array( 'compress'=>false );
  endif;

  $parser = new Less_Parser( $options );

  $parser->parse( shoestrap_complete_less() );
  $css = $parser->getCss();
  // This is a REALLY ugly hack...
  $css = str_replace( get_template_directory() . '/assets/less/fonts/', '', $css );
  $css = str_replace( get_template_directory_uri() . '/assets/', '../', $css );
  // Add FUGLY hack for child themes
  if ( is_child_theme() ) :
    $css = str_replace( get_stylesheet_directory(), get_stylesheet_directory_uri(), $css );
  endif;

  return apply_filters( 'shoestrap_compiler_output', $css );
}
endif;


if ( !function_exists( 'shoestrap_compile_css' ) ) :
function shoestrap_compile_css( $method = 'php' ) {
  global $wp_filesystem;
  
  // Initialize the Wordpress filesystem, no more using file_put_contents function
  if ( empty( $wp_filesystem ) ) :
    require_once( ABSPATH . '/wp-admin/includes/file.php' );
    WP_Filesystem();
  endif;
  $content = '/********* Do not edit this file *********/

';
  
  if ( $method == 'php' ) :
    if ( get_option( 'shoestrap_activated' ) == 1 ) :
      $content .= shoestrap_phpless_compiler();
      $file = shoestrap_css();
      if ( is_writeable( $file ) || ( !file_exists( $file ) && is_writeable( dirname( $file ) ) ) ) :
        if ( !$wp_filesystem->put_contents( $file, $content, FS_CHMOD_FILE ) ) :
          return $content;
        endif;
      endif;
    endif;
  endif;
}
endif;


if ( !function_exists( 'shoestrap_makecss' ) ) :
/*
 * Write the CSS to file
 */
function shoestrap_makecss() {
  shoestrap_compile_css();
}
endif;


if ( is_writable( get_template_directory() . '/assets/less/custom.less' ) ) :
  // If the Custom LESS file has changed, trigger the compiler.
  if ( filemtime( get_template_directory() . '/assets/less/custom.less' ) != get_option( 'shoestrap_custom_lessfile_datetime' ) ) :
    shoestrap_makecss();
  endif;

  // Update the 'shoestrap_custom_lessfile_datetime' option with the new filem data of the custom.less file
  update_option( 'shoestrap_custom_lessfile_datetime', filemtime( get_template_directory() . '/assets/less/custom.less' ) );
endif;