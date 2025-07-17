<?php

class ApiPythonService
{
    private $baseUrl;

    public function __construct($baseUrl = 'http://localhost:5000')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function procesarArchivo($archivoPath, $estado)
    {
        $url = $this->baseUrl . '/procesar?estado=' . $estado;
        $cfile = new \CURLFile(
            $archivoPath,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['file' => $cfile],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: multipart/form-data'],
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        if ($response === false) {
            throw new Exception("Error al conectar con la API: $curlError");
        }
        $json = json_decode($response, true);
        if ($json === null) {
            throw new Exception("Respuesta de la API no es un JSON válido. Código HTTP: $httpCode. Respuesta: $response");
        }
        if (isset($json['status']) && $json['status'] === 'error') {
            throw new Exception("Error de la API: " . ($json['message'] ?? 'Error desconocido'));
        }
        if ($httpCode !== 200) {
            throw new Exception("Error HTTP $httpCode de la API con mensaje: " . ($json['message'] ?? 'Error desconocido'));
        }
        return $json;
    }

    public function unificarExcels($archivos)
    {
        $url = $this->baseUrl . '/unificar';
        $cfileArray = [];
        foreach ($archivos['tmp_name'] as $idx => $tmpPath) {
            $nombre = $archivos['name'][$idx];
            $cfileArray["archivos[$idx]"] = new \CURLFile($tmpPath, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $nombre);
        }
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $cfileArray,
            CURLOPT_TIMEOUT => 30
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        if ($response === false) {
            throw new Exception("Error de conexión con la API: $curlError");
        }
        $respuesta = json_decode($response, true);
        if ($respuesta === null) {
            throw new Exception("La API devolvió una respuesta no válida: $response");
        }
        if (isset($respuesta['error'])) {
            throw new Exception("API: " . $respuesta['error']);
        }
        if ($httpCode !== 200 || !isset($respuesta['status']) || $respuesta['status'] !== 'success') {
            throw new Exception("Error inesperado de la API");
        }
        return $respuesta['archivo'];
    }

    public function verificarSerieNubox($archivoPath, $serie)
    {
        $url = $this->baseUrl . '/verificar?serie=' . urlencode($serie);
        $cfile = new \CURLFile($archivoPath, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['file' => $cfile],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: multipart/form-data'],
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        if ($response === false) {
            throw new Exception("Error al conectar con la API: $curlError");
        }
        $registros = json_decode($response, true);
        if ($registros === null) {
            throw new Exception("Respuesta de la API no es un JSON válido. Código HTTP: $httpCode. Respuesta: $response");
        }
        if (isset($registros['status']) && $registros['status'] === 'error') {
            throw new Exception("Error de la API: " . ($registros['message'] ?? 'Error desconocido'));
        }
        if ($httpCode !== 200) {
            throw new Exception("Error HTTP $httpCode de la API con mensaje: " . ($registros['message'] ?? 'Error desconocido'));
        }
        return $registros;
    }
}
