<?php
/**
 * Página para peticiones AJAX.
 *
 * @package		Note System
 * @subpackage	AJAX
 * @author		Karmac
 */

// Comprobar si la página se cargo con AJAX.
if ( strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) != 'xmlhttprequest' )
{
	die( 'No acceda a esta p&aacute;gina directamente desde su navegador.' );
}

// Tipo de archivo: JSON.
header( 'Content-type: application/json' );

// Incluimos otros archivos.
require_once 'config.php';
require_once 'core.php';

// Realizamos la conexión con la base de datos.
$mysqli = new mysqli( $hostname, $username, $password, $database );
if ( $mysqli->connect_errno )
{
	show_error( 'Error de conexión.' );
}
// Codificación de caracteres.
$mysqli->set_charset( 'utf8' );

// Comparamos la variable action en la URL con las diferentes acciones.
switch ( $_GET['action'] )
{
	// Obtener todas las notas.
	case 'getAll' :
		if ( $query = $mysqli->query( 'SELECT * FROM notes ORDER BY position ASC' ) )
		{
			$arr = array();
			while ( $row = $query->fetch_assoc() )
			{
				$arr[] = Notes::unserialized_return( $row['id'], $row['text'], $row['date'] );
			}
			echo json_encode( $arr );
		}
		break;

	// Reorganizar.
	case 'rearrange' :
		Notes::rearrange( $_POST['positions'] );
		break;

	// Añadir.
	case 'add' :
		Notes::add( '¡Aquí está su nueva nota! Puede eliminarla o editarla utilizando los botones a la izquierda.' );
		break;

	// Eliminar.
	case 'delete' :
		Notes::delete( $_POST['id'] );
		break;

	// Editar.
	case 'edit' :
		Notes::edit( $_POST['id'], $_POST['text'], $_POST['date'] );
		break;

	// No se especificó ninguna de las acciones anteriores.
	default :
		show_error( 'La acción especificada no es correcta.' );
		break;
}
?>