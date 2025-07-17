<?php

class ArchivoService
{
    public function getUploadDir($tipo)
    {

        $base = __DIR__ . '/../../uploads/';
        switch (strtolower($tipo)) {
            case 'sire':
                return $base . 'sire/';
            case 'nubox':
                return $base . 'nubox/';
            case 'edsuite':
                return $base . 'edsuite/';
            default:
                throw new Exception("Tipo de carpeta no soportado: $tipo");
        }
    }

    public function generarNombreArchivo($tipo, $originalName)
    {
        $prefix = strtolower($tipo);
        $timestamp = date('Y-m-d_H-i-s');
        return $prefix . '_' . $timestamp . '_' . basename($originalName);
    }

    public function moverArchivoSubido($archivo, $tipo)
    {
        $uploadDir = $this->getUploadDir($tipo);
        $this->crearCarpeta($uploadDir);
        $nombre = $this->generarNombreArchivo($tipo, $archivo['name']);
        $destino = $uploadDir . $nombre;
        $this->subirArchivo($archivo, $destino);
        return $destino;
    }

    public function listarArchivosPorTipo($tipo, $patron = '*')
    {
        $dir = $this->getUploadDir($tipo);
        return $this->listarArchivos($dir, $patron);
    }

    public function limpiarCarpetaTipo($tipo)
    {
        $dir = $this->getUploadDir($tipo);
        $this->limpiarCarpeta($dir, false);
    }

    public function eliminarArchivosPorTipo($tipo, $patron = '*')
    {
        $archivos = $this->listarArchivosPorTipo($tipo, $patron);
        foreach ($archivos as $archivo) {
            $this->eliminarArchivo($archivo);
        }
    }

    public function existeArchivoEnTipo($tipo, $nombre)
    {
        $dir = $this->getUploadDir($tipo);
        return $this->existeArchivo($dir . $nombre);
    }

    public function leerArchivoEnTipo($tipo, $nombre)
    {
        $dir = $this->getUploadDir($tipo);
        return $this->leerArchivo($dir . $nombre);
    }

    public function eliminarArchivoEnTipo($tipo, $nombre)
    {
        $dir = $this->getUploadDir($tipo);
        return $this->eliminarArchivo($dir . $nombre);
    }
    public function subirArchivo($archivo, $destino)
    {
        $directorio = dirname($destino);
        if (!file_exists($directorio)) {
            if (!mkdir($directorio, 0777, true)) {
                throw new Exception("No se pudo crear el directorio destino: $directorio");
            }
        }
        if (!isset($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name'])) {
            throw new Exception("Archivo no válido o no subido correctamente");
        }
        if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
            throw new Exception("No se pudo mover el archivo a $destino");
        }
        return true;
    }

    public function crearCarpeta($ruta)
    {
        if (!file_exists($ruta)) {
            if (!mkdir($ruta, 0777, true)) {
                throw new Exception("No se pudo crear la carpeta: $ruta");
            }
        }
        return true;
    }

    public function limpiarCarpeta($ruta, $borrarRaiz = false)
    {
        if (!is_dir($ruta)) return;
        $items = scandir($ruta);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $ruta . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->limpiarCarpeta($path, true);
            } else {
                @unlink($path);
            }
        }
        if ($borrarRaiz) {
            @rmdir($ruta);
        }
    }

    public function listarArchivos($ruta, $patron = '*')
    {
        if (!is_dir($ruta)) return [];
        return glob($ruta . DIRECTORY_SEPARATOR . $patron);
    }

    public function existeArchivo($ruta)
    {
        return file_exists($ruta);
    }

    public function eliminarArchivo($ruta)
    {
        if (file_exists($ruta) && is_file($ruta)) {
            return unlink($ruta);
        }
        return false;
    }

    public function leerArchivo($ruta)
    {
        if (!file_exists($ruta)) {
            throw new Exception("No existe el archivo: $ruta");
        }
        return file_get_contents($ruta);
    }
}
