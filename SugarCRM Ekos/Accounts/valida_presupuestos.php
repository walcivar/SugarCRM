<?php 
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point'); 

$resultado="";
$nombre_oportunidad = $_POST['oportunidad'];
$id_oportunidad_old = $_POST['id_op_old'];
$id_usuario_actual = $_POST['id_usuario'];
$nombre_cuenta = $_POST['cuenta'];
$guarda_o_no = $_POST['guarda_o_no'];
$probabilidad='0';
$id_oportunidad='';
$distintos =0;
$oport = new Opportunity();
$oport->retrieve_by_string_fields(array('name'=>$nombre_oportunidad));
if($oport->id)
{ 
	$id_oportunidad = $oport->id; 
}
else
{
	$id_oportunidad = '';
}
if(strpos($nombre_cuenta,"/") !== false)
{
    $nombre_cuenta = str_replace("/", "&", $nombre_cuenta);
}
$total=0;
$cont=0;
$anio = date("Y");
$duenio = $_POST['duenio'];
$usuario = new User();
$usuario->retrieve_by_string_fields(array('id'=>$id_usuario_actual));
$nombres_usuario = $usuario->first_name." ".$usuario->last_name;
$db =  DBManagerFactory::getInstance();
if($duenio == $nombres_usuario)
{
}
else
{
	$query0 = "SELECT id FROM users where CONCAT(first_name,' ',last_name) = '".$duenio."'";
	$result0 = $db->query($query0, true, 'Error selecting the oportunity record');
	if($row0=$db->fetchByAssoc($result0))
	{
		$id_usuario_actual = $row0['id'];
		$nombres_usuario = $duenio;
		$distintos = 1;
	}
}

$cuenta = new Account();
$cuenta->retrieve_by_string_fields(array('name'=>$nombre_cuenta));
$id_cuenta = $cuenta->id;

if($id_oportunidad == '')
{
	$query6 = "select * FROM opportunities where name = '".$nombre_oportunidad."'";
}
else
{
	$query6 = "select * FROM opportunities where id = '".$id_oportunidad."'";
}
$result6 = $db->query($query6, true, 'Error selecting the product record');
if($row6=$db->fetchByAssoc($result6))
{
	$probabilidad = $row6['probability'];
}
else
{
	$probabilidad = '0';
}

if($id_oportunidad == '')
{
	$query3 = "select count(id_producto_c) as total from m1_oportunidad_productos_cstm where oportunidad_c = '".$nombre_oportunidad."' and estado_c = '1'";
}
else
{
	$query3 = "select count(id_producto_c) as total from m1_oportunidad_productos_cstm where oportunidad_c = '".$row6['name']."' and estado_c = '1'";
}
$result3 = $db->query($query3, true, 'Error selecting the product record');
if($row3=$db->fetchByAssoc($result3))
{
	$total = $row3['total'];
}

if($id_oportunidad == '')
{
	$query2 = "select id_producto_c from m1_oportunidad_productos_cstm where oportunidad_c = '".$nombre_oportunidad."' and estado_c = '1'";
}
else
{
	$query2 = "select id_producto_c from m1_oportunidad_productos_cstm where oportunidad_c = '".$row6['name']."' and estado_c = '1'";
}
$result2 = $db->query($query2, true, 'Error selecting the product record');
while($row2=$db->fetchByAssoc($result2))
{
	$id_producto = $row2['id_producto_c'];
	$det_asig_prod = new Detalle_asig_producto();
	$det_asig_prod->retrieve_by_string_fields(array('producttemplate_id_c'=>$id_producto,'account_id_c'=>$id_cuenta,'assigned_user_id'=>$id_usuario_actual,'estatus_c'=>'Aprobado'));
	if($det_asig_prod->id)
	{
		//El producto ya esta aprobado para este usuario
		//verifica que el usuario tenga presupuesto para ese producto
	  $presupuesto = new Presupuestos();
		$presupuesto->retrieve_by_string_fields(array('assigned_user_id'=>$id_usuario_actual,'nombre_periodo_c'=>$anio,'tipo_c'=>'distribucion','producttemplate_id_c'=>$id_producto,'estatus_c'=>'Aprobado'));					
		if($presupuesto->id)
		{
			// se contabiliza que el producto si puede 
			$cont++;
		}		
	}
	else
	{
		//CUANDO EL USUARIO VENDE UN PRODUCTO ASIGNADO A OTRO USUARIO
	//$resultado.=" - A ";		
		$det_asig_prod2 = new Detalle_asig_producto();
		//valida si algun usuario ya tiene asignado ese producto
		$det_asig_prod2->retrieve_by_string_fields(array('producttemplate_id_c'=>$id_producto,'account_id_c'=>$id_cuenta,'estatus_c'=>'Aprobado'));
		if($det_asig_prod2->id)
		{	
			//El producto ya esta asignado a otro usuario
			//Obtiene el nombre y el id del usuario asignado a ese producto
			$usuario2 = new User();
			$usuario2->retrieve_by_string_fields(array('id'=>$det_asig_prod2->assigned_user_id));
			$nombres_usuario2 = $usuario2->first_name." ".$usuario2->last_name;
			$id_usuario2 = $det_asig_prod2->assigned_user_id;

			//valida que el otro usuario tenga presupuesto para ese producto
			$presupuesto2 = new Presupuestos();
			$presupuesto2->retrieve_by_string_fields(array('assigned_user_id'=>$id_usuario2,'nombre_periodo_c'=>$anio,'tipo_c'=>'distribucion','producttemplate_id_c'=>$id_producto,'estatus_c'=>'Aprobado'));
			if($presupuesto2->id) 
			{//el producto esta para otro usuario pero igual cumple							
				$cont++;				
			}		
		}
	}
}



if($total == $cont)
{
	echo '1';
}
else
{
	echo '0';
}
//echo " Res: ".$resultado;
?>