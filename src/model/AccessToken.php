<?php
require_once 'config/conexion.php';

class AccessToken
{
    public static function insertar($data)
    {
        $conn = Conexion::conectar();
        $sql = "INSERT INTO access_token 
        (id_cliente, id_sucursal, rol, estado, hashcode, id_user_create, user_create, user_update, date_create, date_expired, comentario)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iisisissss",
            $data['id_cliente'],
            $data['id_sucursal'],
            $data['rol'],
            $data['estado'],
            $data['hashcode'],
            $data['id_user_create'],
            $data['user_create'],
            $data['user_update'],
            $data['date_expired'],
            $data['comentario']
        );
        return $stmt->execute();
    }

    public static function obtenerPorId($id_token)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM access_token WHERE id_token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_token);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    public static function obtenerPorHash($hashcode)
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM access_token WHERE hashcode = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $hashcode);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    public static function listar($filtros = [])
    {
        $conn = Conexion::conectar();
        $sql = "SELECT * FROM access_token WHERE 1=1";
        $params = [];
        $types = '';

        if (isset($filtros['id_cliente'])) {
            $sql .= " AND id_cliente = ?";
            $params[] = $filtros['id_cliente'];
            $types .= 'i';
        }
        if (isset($filtros['id_sucursal'])) {
            $sql .= " AND id_sucursal = ?";
            $params[] = $filtros['id_sucursal'];
            $types .= 'i';
        }
        if (isset($filtros['estado'])) {
            $sql .= " AND estado = ?";
            $params[] = $filtros['estado'];
            $types .= 'i';
        }
        $sql .= " ORDER BY date_create DESC";
        $stmt = $conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public static function actualizarEstado($id_token, $nuevoEstado, $camposExtra = [])
    {
        $conn = Conexion::conectar();
        $sql = "SELECT estado, date_expired FROM access_token WHERE id_token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_token);
        $stmt->execute();
        $res = $stmt->get_result();
        $token = $res->fetch_assoc();

        if (!$token) return false;

        if ($token['date_expired'] && strtotime($token['date_expired']) < time()) {
            if ($nuevoEstado != 4) return false;
        }

        if (in_array($token['estado'], [2, 3, 4])) {
            return false;
        }

        $sql = "UPDATE access_token SET estado = ?, user_update = ?, date_update = NOW()";
        $params = [$nuevoEstado, $_SESSION['usuario']];
        $types = "is";

        if (isset($camposExtra['date_used'])) {
            $sql .= ", date_used = ?";
            $params[] = $camposExtra['date_used'];
            $types .= "s";
        }
        if (isset($camposExtra['user_used'])) {
            $sql .= ", user_used = ?";
            $params[] = $camposExtra['user_used'];
            $types .= "s";
        }
        if (isset($camposExtra['ip_used'])) {
            $sql .= ", ip_used = ?";
            $params[] = $camposExtra['ip_used'];
            $types .= "s";
        }
        if (isset($camposExtra['comentario'])) {
            $sql .= ", comentario = ?";
            $params[] = $camposExtra['comentario'];
            $types .= "s";
        }
        $sql .= " WHERE id_token = ?";
        $params[] = $id_token;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    public static function revocar($id_token, $comentario = null)
    {
        return self::actualizarEstado($id_token, 3, ['comentario' => $comentario]);
    }

    public static function marcarComoUsado($id_token, $user_used, $ip_used)
    {
        return self::actualizarEstado($id_token, 2, [
            'date_used' => date('Y-m-d H:i:s'),
            'user_used' => $user_used,
            'ip_used' => $ip_used
        ]);
    }
}
