<?php
/**
 * Objetos y funciones del sistema.
 *
 * @package		Note System
 * @subpackage	Core Functions
 * @author		Karmac
 */

/**
 * Manejo de las notas.
 */
final class Notes
{
	// Prevenimos la instanciación.
	private function __construct() {}

	/**
	 * Añade una nueva nota.
	 *
	 * @access	public
	 * @static
	 *
	 * @param	string	$text	Contenido de la nota.
	 */
	public static function add( $text )
	{
		global $mysqli;

		// Obtenemos la posición de la última nota.
		if ( $query = $mysqli->query( 'SELECT MAX(position) FROM notes' ) )
		{
			$row = $query->fetch_row();
			// La posición de la nueva nota.
			$position = $row[0] + 1;

			// Insertamos la nota nueva.
			if ( $stmt = $mysqli->prepare( 'INSERT INTO notes (text, date, position) VALUES (?, ?, ?)' ) )
			{
				$date = time();
				$stmt->bind_param( 'sii', $text, $date, $position );
				$stmt->execute();

				echo self::serialized_return( $mysqli->insert_id, $text, $date );
			}
			else
			{
				show_error( 'Error al intentar añadir la nota.' );
			}
		}
		else
		{
			show_error( 'Error al intentar añadir la nota.' );
		}
	}

	/**
	 * Ordena las notas actuales.
	 *
	 * @access	public
	 * @static
	 *
	 * @param	array	$positions	Array con los IDs de las notas ordenadas según su posición.
	 */
	public static function rearrange( $positions )
	{
		global $mysqli;

		// Creamos la consulta, WHEN id THEN posición+1
		foreach ( $positions as $position => $id )
			$sql .= ' WHEN ' . (int)$id . ' THEN ' . ( (int)$position + 1 );

		// La ejecutamos.
		if ( $stmt = $mysqli->prepare( 'UPDATE notes SET position = CASE id ' . $sql . ' END' ) )
		{
			$stmt->execute();
			show_success( 'Las notas fueron ordenadas correctamente.' );
		}
		else
		{
			show_error( 'Error al intentar ordenar las notas.' );
		}	
	}

	/**
	 * Elimina una nota.
	 *
	 * @access	public
	 * @static
	 *
	 * @param	integer		$id		ID de la nota.
	 */
	public static function delete( $id )
	{
		global $mysqli;

		// Eliminamos la nota.
		if ( $stmt = $mysqli->prepare( 'DELETE FROM notes WHERE id = ?' ) )
		{
			$stmt->bind_param( 'i', $id );
			$stmt->execute();
			show_success( 'La nota se eliminó correctamente.' );
		}
		else
		{
			show_error( 'Error la intentar eliminar la nota.' );
		}	
	}

	/**
	 * Edita una nota.
	 *
	 * @access	public
	 * @static
	 *
	 * @param	integer		$id		ID de la nota.
	 * @param	string		$text	Contenido de la nota.
	 * @param	integer		$date	Fecha de la nota.
	 */
	public static function edit( $id, $text, $date )
	{
		global $mysqli;

		// Editamos la nota.
		if ( $stmt = $mysqli->prepare( 'UPDATE notes SET text = ?, date = ? WHERE id = ?' ) )
		{
			$stmt->bind_param( 'sii', $text, $date, $id );
			$stmt->execute();
			show_success( 'La nota fue editada correctamente.' );
		}
		else
		{
			show_error( 'Error la intentar editar la nota.' );
		}	
	}

	/**
	 * Devuelve los datos de la nota como un array asociativo.
	 *
	 * @access	public
	 * @static
	 *
	 * @param	integer		$id		ID de la nota.
	 * @param	string		$text	Contenido de la nota.
	 * @param	integer		$date	Fecha de la nota.
	 * @return	array		Datos de la nota.
	 */
	public static function unserialized_return( $id, $text, $date )
	{
		return array(
			'id'	=>	$id,
			'text'	=>	$text,
			'date'	=>	date( $GLOBALS['date_format'], $date )
		);
	}

	/**
	 * Devuelve los datos de una nota como JSON.
	 *
	 * @access	public
	 * @static
	 *
	 * @param	integer		$id		ID de la nota.
	 * @param	string		$text	Contenido de la nota.
	 * @param	integer		$date	Fecha de la nota.
	 * @return	string		Datos de la nota convertidos a JSON.
	 */
	public static function serialized_return( $id, $text, $date )
	{
		return json_encode( array( self::unserialized_return( $id, $text, $date ) ) );
	}
}

/**
 * Imprime un error en pantalla.
 *
 * @access	public
 * @param	string	$message	Mensaje.
 * @param	integer	$code		Tipo de error. Opcional. Por defecto: 500 Internal Server Error
 */
function show_error( $message, $code = 500 )
{
	$errors = array(
		500	=> 'Internal Server Error'
	);

	header( "HTTP/1.1 {$code} {$errors[$code]}" );
	exit( $message );
}

/**
 * Imprime un mensaje de suceso.
 *
 * @access	public
 * @param	string	$message	Mensaje.
 */
function show_success( $message )
{
	echo '{"success":"' . $message . '"}';
}
?>