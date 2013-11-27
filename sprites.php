<?php

// The following file contains the `css_class` function, that is used to create human friendly Class Names
require_once './css_class.fn.php';



/*
 * Images CSS Sprite Generator Class
 * 
 * @original   http://www.innvo.com/c/PHP/1315192249-css-sprites-with-php
 * @modified   Mohamed Gamil <Gemy.me>
 * @license    MIT
 */
class images_to_sprite {
	
	
	// Allowed File Types
	var $filetypes  = array
	(
		'jpg'   => TRUE,
		'png'   => TRUE,
		'jpeg'  => TRUE,
		'gif'   => TRUE
	);
	
	
	/**
	 *  Constructor
	 *  
	 *  @param [string] $folder Input Images Container
	 *  @param [string] $output Output File Path
	 *  @param [bool]   $mkScss Create a SASS (SCSS) File?
	 *  @return void
	 *  
	 *  @example:
	 *     $instance = new images_to_sprite( './images', './sprites/icon' );
	 */
	function __construct( $folder = 'images', $output = 'sprites', $mkScss = FALSE, $mkHTML = FALSE )
	{
		// Images Folder
		$this->folder   = $folder;
		
		// Output Path
		$this->output   = $output;
		$this->files    = array();
		
		// Create SASS (SCSS) File?
		$this->mkscss   = $mkScss;
		
		// Create a Demo HTML File?
		$this->mkhtml   = $mkHTML;
		
		// Make Sure There is no Trailing Slash at the end of the Output Path
		if ( substr( $this->output, -1 ) === '/' )
		{
			$this->output = substr( $this->output, 0, -1 );
		}
	}
	
	
	/**
	 *  Process
	 *  
	 *  @return [bool] Process Status (TRUE/FALSE)
	 */
	function process()
	{
		$basedir  = $this->folder;
		$rows     = $count = $cols = $bigy = $bigx =0;
		$dims     = array();
		$files    = array();
		
		// Read through the directory for suitable images
		if( $handle = opendir( $this->folder ) )
		{
			while ( FALSE !== ( $file = readdir( $handle ) ) )
			{
				$split = explode( '.', $file );
				$ext   = end( $split );
				
				// Ignore NON-Allowed Files
				if( $file == '.' OR !isset( $this->filetypes[ $ext ] ) OR !$this->filetypes[ $ext ] )
				{
					continue;
				}
				
				// Get Image Dimensions
				$size   = getimagesize( $this->folder .'/'. $file );
				$dims[] = $size[0]. '|' .$size[1];
				
				// Image will be added to sprite, add to array
				$this->files[$file] = array
				(
					'file' => $file,
					'ext'  => strtolower( $ext ),
					'x'    => $size[0],
					'y'    => $size[1]
				);
			}
			closedir($handle);
			
			$dims   = array_unique( $dims );
			$count  = count( $this->files );
			$cols   = floor( log( $count ) + ( 0.05 * $count ) );
			$rows   = $cols !== 1 ? ceil( $count / $cols ) : 1;
		}
		
		// Return FALSE if there are no VALID Files.
		if ( count( $this->files ) == 0 )
		{
			return FALSE;
		}
		
		// Calculate Biggest X/Y
		foreach( $dims as $dim )
		{
			$dim = explode( '|', $dim );
			
			if ( $bigx < $dim[0] )
				$bigx = $dim[0];
			
			if ( $bigy < $dim[1] )
				$bigy = $dim[1];
		}
		
		// Calculate The Overall Height & Width
		$this->xx = $cols * $bigx;
		$this->yy = $rows * $bigy;
		
		// Uncomment to Display some Debugging info
		/*
		echo "<pre>\nX = ", $bigx, "\nY = ", $bigy, "\nXX = ", $this->xx, "\nYY = ",   ## Biggest Calculated Width, Biggest Calculated Height
		     $this->yy, "\nColumns = ", $cols, "\nRows = ", $rows;                     ## Total Width, Total Height, Rows, Columns
		echo "\n- - - - - - - - - - -\n";
		echo "\n\nFiles List:\n"; print_r( $this->files );                             ## Files List
		exit;
		*/
		
		// Create Image
		$im       = imagecreatetruecolor( $this->xx, $this->yy );
		imagesavealpha( $im, true );
		
		// Add Alpha Channel
		$alpha    = imagecolorallocatealpha( $im, 0, 0, 0, 127 );
		imagefill( $im, 0, 0,$alpha );
		
		// Append Images To Sprite & Generate CSS
		$xcounter = 0;
		$ycounter = 0;
		$n        = explode( '/', $this->output );
		$n        = end( $n );
		$ns       = array();
		$fp       = fopen( $this->output . '.css', 'w' );
		
		if ( $this->mkscss == TRUE )
		{
			$fpscss = fopen( $this->output . '.scss', 'w' );
		}
		
		if ( $this->mkhtml == TRUE )
		{
			$fphtml = fopen( $this->output . '.html', 'w' );
		}
		
		// Initial CSS Style
		fwrite
		(	$fp,
			'.' . $n
			. " {\n\twidth: {$bigx}px;\n\theight: {$bigy}px;\n\tbackground-image: url('{$n}.png');\n}\n"
		);
		
		if ( $this->mkscss == TRUE )
		{
			fwrite
			(	$fpscss,
				'.' . $n
				. " {\n\twidth: {$bigx}px;\n\theight: {$bigy}px;\n\tbackground-image: url('{$n}.png');\n\n"
			);
		}
		
		if ( $this->mkhtml == TRUE )
		{
			fwrite
			(	$fphtml,
				"<!DOCTYPE html>\n<html>\n\n\t<head>\n\t\t<title>CSS Sprites Demo | .{$n}</title>\n\t\t<meta charset='utf-8' />" .
				"\n\t\t<link rel='stylesheet' type='text/css' media='all' href='{$n}.css' />" .
				"\n\t\t<style type='text/css'>" .
					"\n\t\t\t" .
					"body {\n\t\t\t\ttext-align: center;\n\t\t\t}" .
					"\n\t\t\t" .
					".{$n}, .{$n}-icnlabel, .{$n}-icnrow {\n\t\t\t\tdisplay: inline-block;\n\t\t\t\tvertical-align: middle;\n\t\t\t}" .
					"\n\t\t\t" .
					".{$n}-icnlabel {\n\t\t\t\tmin-width: 45%;\n\t\t\t\ttext-align: left;\n\t\t\t}\n\t\t" .
					"\n\t\t\t" .
					".{$n}-icnrow {\n\t\t\t\twidth: 23.076923076923077%;\n\t\t\t\tmargin: 2em 0;margin-left: 1.5641%;\n\t\t\t}\n\t\t</style>" .
				"\n\t</head>\n\n\t<body>"
			);
		}
		
		// Is there is a common word(s) in file names, that better be removed?
		$cw  = array();
		
		foreach( $this->files as $key => $file )
		{
			$f  = $file['file'];
			$f  = substr( str_replace( array_keys( $this->filetypes ), '', strtolower( $f ) ), 0, -1 );
			$f  = preg_replace( '/[0-9]+/', '', $f );
			$f  = preg_replace( '/[\_\s]+/', '-', trim( $f ) );
			$f  = str_replace( '--', '-', $f );
			$w  = explode( '-', $f );
			
			foreach( $w as $ww )
			{
				if ( !isset( $cw[ $ww ] ) )
				{
					$cw[ $ww ]	= 0;
				}
				
				$cw[ $ww ]++;
			}
		}
		
		// Make Friendly CSS Class Names for each File
		$in  = array();
		$et  = 1;
		foreach( $this->files as $key => $file )
		{
			$f  = $file['file'];
			$nn = substr( str_replace( array_keys( $this->filetypes ), '', strtolower( $f ) ), 0, -1 );
			$nn = css_class( $nn, 'dash', TRUE );
			$nn = preg_replace( '/[0-9]+/', '', trim( $nn ) );
			$nn = preg_replace( '/[\_]+/', '-', $nn );
			$nn = str_replace( '--', '-', $nn );
			$ad = FALSE;
			
			// If there is a word that has been repeated more than 10% times of files count, remove it.
			foreach( $cw as $w => $c )
			{
				if ( $c >= ( 0.1 * $count ) )
				{
					$nn = str_replace( array( $w.'-', '-'.$w ), '', $nn );
				}
			}
			
			// Make Sure there is no DASH in Class Names at the beginning or at the end
			if ( substr( $nn, 0, 1 ) == '-' )
			{
				$nn = substr( $nn, 1, strlen( $nn ) );
			}
			if ( substr( $nn, -1 ) == '-' )
			{
				$nn = substr( $nn, 0, strlen( $nn ) -1 );
			}
			
			// Empty Class Name? Create a Class
			if ( trim( $nn ) == '' OR !preg_match( "/^([-a-z_\-])+$/i", $nn ) )
			{
				$nn = $n . $et;
				$et++;
			}else
			{
				// Add Class Name Counter
				if ( !isset( $in[ $nn ] ) )
				{
					$in[ $nn ]	= 0;
				}
				
				// Same Class Name? Increment by 1, and Add as Suffix
				foreach( $ns as $nnn )
				{
					if ( $nnn == $nn )
					{
						$in[ $nn ]++;
					}
				}
				
				if ( $in[ $nn ] >= 1 )
				{
					$ns[ $f ] = $nn . ( $in[ $nn ] + 1 );
					$ad       = TRUE;
				}
			}
			
			if ( $ad == FALSE )
			{
				$ns[ $f ] = $nn;
			}
		}
		
		// Write all CSS Styles
		foreach( $this->files as $key => $file )
		{
			// Next Row Please!
			if ( $xcounter >= $cols )
			{
				$xcounter = 0;
			}
			
			$fname  = $file['file'];
			$ex     = ( $file['ext'] == 'jpg' ) ? 'jpeg' : $file['ext'];
			$fn		= 'imagecreatefrom' . $ex;
			$fx     = $file['x'];
			$fy     = $file['y'];
			$nname  = $ns[ $fname ];
			$cx     = $bigx * $xcounter;
			$cy     = $bigy * $ycounter;
			$calcX  = $cx == 0 ? 0 : '-'. $cx . 'px';
			$calcY  = $cy == 0 ? 0 : '-'. $cy . 'px';
			
			$posX   = $bigx != $fx ? ( ($bigx / 2) - ($fx / 2) ) : 0;
			$posY   = $bigy != $fx ? ( ($bigy / 2) - ($fy / 2) ) : 0;
			
			// In GD We Trust
			if ( function_exists( $fn ) )
			{
				$im2    = $fn( $this->folder . '/' . $fname );
				imagecopy( $im, $im2, $cx + $posX, $cy + $posY, 0, 0, $fx, $fy );
			}else
			{
				// File Type Not Supported, There is Nothing We Can Do!
				continue;
			}
			
			// Write CSS Style
			fwrite
			(	$fp,
				'.'.$n.'.'.$nname." {\n\tbackground-position: {$calcX} {$calcY};\n}\n"
			);
			
			// Write SCSS Style
			if ( $this->mkscss == TRUE )
			{
				fwrite
				(	$fpscss,
					"\t" . '&.'.$nname." {\n\t\tbackground-position: {$calcX} {$calcY};\n\t}\n\n"
				);
			}
			
			// Write HTML Sample
			if ( $this->mkhtml == TRUE )
			{
				fwrite
				(	$fphtml,
					"\n\n\t\t" .
					"<div class='{$n}-icnrow'>" .
						"\n\t\t\t" .
						"<span class='{$n}-icnlabel'>{$nname}</span>" .
						"\n\t\t\t" .
						"<span class='{$n} {$nname}'></span>" .
					"\n\t\t" .
					"</div>"
				);
			}
			
			// Increase X-Axis Counter
			$xcounter++;
			
			// Reached Columns Limit? Just Reset.
			if ( $xcounter >= $cols )
			{
				$ycounter++;
			}
		}
		
		// Write SCSS Closing Bracket
		if ( $this->mkscss == TRUE )
		{
			fwrite
			(	$fpscss,
				"}"
			);
		}
		
		// Write HTML Closing Markup
		if ( $this->mkhtml == TRUE )
		{
			fwrite
			(	$fphtml,
				"\n\n\t</body>\n\n</html>"
			);
		}
		
		// Free Resources
		fclose($fp);
		
		if ( $this->mkscss == TRUE )
		{
			fclose($fpscss);
		}
		
		if ( $this->mkhtml == TRUE )
		{
			fclose($fphtml);
		}
		
		imagepng( $im, $this->output.'.png' ); // Save image to file
		imagedestroy( $im );
		
		// All Good!
		return TRUE;
	}
}
 
?>