<?php 
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point'); 

$resultado="";
$cuenta = $_POST['cuenta'];
$nombre_oportunidad = $_POST['nombre'];
if(strpos($cuenta,"/") !== false)
{
    $cuenta = str_replace("/", "&", $cuenta);
}
$cuenta_bean = new Account();
$cuenta_bean->retrieve_by_string_fields(array('name'=>$cuenta));

$db =  DBManagerFactory::getInstance(); 
$query = "SELECT a.id AS id_producto, a.name AS name_producto, a.category_id AS id_categoria, b.name AS name_categoria FROM product_templates a, product_categories b WHERE a.category_id = b.id and a.deleted = '0' and b.deleted = '0' ORDER BY b.name ASC, a.name ASC";
$result = $db->query($query, true, 'Error selecting the product record');
while($row=$db->fetchByAssoc($result))
{
	$query2 = "SELECT a.first_name, a.last_name, b.nombre_producto_c FROM users a, detalle_asig_producto_cstm b, detalle_asig_producto c WHERE b.account_id_c = '".$cuenta_bean->id."' AND b.producttemplate_id_c = '".$row['id_producto']."' AND b.estatus_c = 'Aprobado' AND b.id_c = c.id AND a.id = c.assigned_user_id and a.status = 'Active' and a.deleted = '0'";
	$result2 = $db->query($query2, true, 'Error selecting the user owner record');
	if($row2=$db->fetchByAssoc($result2))
	{
		$resultado.=$row2['first_name']." ".$row2['last_name']."_".$row2['nombre_producto_c']."/";
	}
}

echo $resultado;

?>