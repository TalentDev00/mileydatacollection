<?php

function connection_ssh($bd_config) {
    //shell_exec('-ssh miley@localhost -pw "VjV2eu7Hbn" -P 22 -N -L 3307:162.243.131.72:3306');
    shell_exec('-ssh root@162.243.131.72 -pw "e987edea598e4867b4c7e27bb3074af4" -P 22 -N -L 3307:localhost:3306');
    $conn = new mysqli($bd_config['host'], $bd_config['user'], $bd_config['pass'], $bd_config['database'], 3307);

    //$conn = new mysqli($bd_config['host'], $bd_config['user'], $bd_config['pass'], $bd_config['database']);
    if($conn->connect_errno) {
        return false;
    } else {
        return $conn;
    }
}

function connection($bd_config) {
    $conn = new mysqli($bd_config['host'], $bd_config['user'], $bd_config['pass'], $bd_config['database']);
    
    if($conn->connect_errno) {
        return false;
    } else {
        return $conn;
    }
}

function close_connection($conexion) {
    $thread = $conexion->thread_id;
    $conexion->kill($thread);
    $conexion->close();
}

function cleanData($datos) {
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos);
    
    return $datos;
}

function cleanId($id) {
    return (int)cleanData($id);
}

function parse_results($stmt)
{
    $params = array();
    $data = array();
    $results = array();
    $meta = $stmt->result_metadata();
    
    while($field = $meta->fetch_field())
        $params[] = &$data[$field->name]; 
    
    call_user_func_array(array($stmt, 'bind_result'), $params);
    
    while($stmt->fetch())
    {
        foreach($data as $key => $val) 
        {
            $c[$key] = $val;
        }
        $results[] = $c;
    }
    return $results;
}

function get_news_companies($connection) {
    $query = $connection->query("
        SELECT * FROM tblNewsCompanies 
    ");

    $result = array();
    
    while($row = $query->fetch_array(MYSQLI_ASSOC)) {
        $result[] = $row;
    }

    return ($result) ? $result : false;
}

function get_news_companie_by_id($connection, $id) {
    $statement = $connection->prepare("
        SELECT * FROM tblNewsCompanies
        WHERE id = ?
    ");
    $statement->bind_param('i', $id);
    $statement->execute();

    $resultado = parse_results($statement);

    return ($resultado) ? $resultado : false;
}