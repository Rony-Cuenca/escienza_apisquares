from flask import Flask, jsonify, request, send_from_directory
from werkzeug.utils import secure_filename
import pandas as pd
import os
from datetime import datetime
import tempfile
import shutil

app = Flask(__name__)

def limpiar_carpeta(carpeta):
    if os.path.exists(carpeta):
        for archivo in os.listdir(carpeta):
            archivo_path = os.path.join(carpeta, archivo)
            try:
                if os.path.isfile(archivo_path) or os.path.islink(archivo_path):
                    os.unlink(archivo_path)
                elif os.path.isdir(archivo_path):
                    shutil.rmtree(archivo_path)
            except Exception as e:
                print(f"No se pudo eliminar {archivo_path}: {e}")

EXTENSIONES_PERMITIDAS = {'xls', 'xlsx'}
def extension_valida(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in EXTENSIONES_PERMITIDAS

@app.route('/procesar', methods=['POST'])
def procesar():
    estado = request.args.get('estado', type=int)
    
    if 'file' not in request.files:
        return jsonify({'status': 'error', 'message': 'No file received'}), 400

    file = request.files['file']
    
    try:
        df = pd.read_excel(file, header=None, engine='openpyxl')
    except Exception as e:
        return jsonify({'status': 'error', 'message': f'Error reading Excel: {str(e)}'}), 400
    try:
        if estado == 1:
            if len(df) <= 7:
                return jsonify({'status': 'error', 'message': 'Not enough rows'}), 400

            header = df.iloc[7].tolist()
            
            try:
                col_serie = header.index('Número')
                col_gravado = header.index('Total Gravado')
                col_exonerado = header.index('Total Exonerado')
                col_inafecto = header.index('Total Inafecto')
                col_igv = header.index('Total IGV')
                col_fecha = header.index('Fecha emisión')
            except ValueError as e:
                return jsonify({'status': 'error', 'message': f'Missing columns: {str(e)}'}), 400

            data_serie_gra = {}
            data_serie_exo = {}
            data_serie_ina = {}
            data_serie_igv = {}
            conteo_series = {}

            primer_valor_fecha = df.iloc[8, col_fecha]

            for _, row in df.iloc[8:].iterrows():
                if pd.isna(row[col_serie]) or pd.isna(row[col_gravado]) or pd.isna(row[col_exonerado]) or pd.isna(row[col_inafecto]) or pd.isna(row[col_igv]):
                    continue

                try:
                    serie = str(row[col_serie]).split('-')[0].strip()
                    if not serie:
                        continue
                    
                    def clean_number(num):
                        if pd.isna(num):
                            return 0.0
                        if isinstance(num, (int, float)):
                            return float(num)
                        return float(str(num).replace(',', ''))
                    
                    gravado = clean_number(row[col_gravado])
                    exonerado = clean_number(row[col_exonerado])
                    inafecto = clean_number(row[col_inafecto])
                    igv = clean_number(row[col_igv])

                    if serie not in data_serie_gra:
                        data_serie_gra[serie] = 0.0
                        data_serie_exo[serie] = 0.0
                        data_serie_ina[serie] = 0.0
                        data_serie_igv[serie] = 0.0
                        conteo_series[serie] = 0

                    data_serie_gra[serie] += gravado
                    data_serie_exo[serie] += exonerado
                    data_serie_ina[serie] += inafecto
                    data_serie_igv[serie] += igv
                    conteo_series[serie] += 1

                except Exception:
                    continue

            resultados = []
            for serie in sorted(data_serie_gra.keys()):
                total_gra = data_serie_gra[serie]
                total_exo = data_serie_exo[serie]
                total_ina = data_serie_ina[serie]
                total_igv = data_serie_igv[serie]
                total_total = total_gra + total_exo + total_ina + total_igv

                resultados.append({
                    'serie': serie,
                    'conteo': conteo_series[serie],
                    'gravado': round(total_gra, 2),
                    'exonerado': round(total_exo, 2),
                    'inafecto': round(total_ina, 2),
                    'igv': round(total_igv, 2),
                    'total': round(total_total, 2),
                    'fecha': primer_valor_fecha
                })

            return jsonify({
                'status': 'success',
                'estado': estado,
                'resultados': resultados
            })

        elif estado == 2:  # EDSuite
            if len(df) <= 1:
                return jsonify({'status': 'error', 'message': 'Not enough rows'}), 400

            header = df.iloc[1].tolist()
            
            try:
                col_serie = header.index('Serie')
                col_igv = header.index('IGV')
                col_total = header.index('Total')
                col_fecha = header.index('Fecha')

                col_producto = header.index('Producto')
                col_cantidad = header.index('Cantidad')

                if 'api_almacen_id' in header:
                    col_api = header.index('api_almacen_id')
                else:
                    col_api = None

                if 'Archivo_Origen' in header:
                    col_archivo = header.index('Archivo_Origen')
                else:
                    col_archivo = None
            except ValueError as e:
                return jsonify({'status': 'error', 'message': f'Missing columns: {str(e)}'}), 400

            data_serie_total = {}
            data_serie_igv = {}
            conteo_series = {}
            data_serie_api = {}  # Nuevo diccionario para almacenar el valor de api_almacen_id por serie
            data_producto_api = {}
            data_archivo_api = {}

            # Diccionarios para almacenar datos de productos, ahora usando tuplas (producto, api_id) como clave
            data_cantidad = {}
            data_producto_total = {}
            data_producto_info = {}  # Para almacenar información adicional del producto
            data_cantidad_archivo = {}
            data_archivo_serie = {}

            primer_valor_fecha = df.iloc[2, col_fecha]  

            for _, row in df.iloc[2:].iterrows():
                if pd.isna(row[col_serie]) or pd.isna(row[col_igv]) or pd.isna(row[col_total]) or pd.isna(row[col_producto]) or pd.isna(row[col_cantidad]):
                    continue

                try:
                    serie = str(row[col_serie]).strip()
                    producto = str(row[col_producto]).strip()

                    if col_archivo is not None:
                        archivo = str(row[col_archivo]).strip()

                    if col_api is not None and not pd.isna(row[col_api]):
                        api_value = str(row[col_api]).strip()
                    else:
                        api_value = ""
                    
                    if not serie:
                        continue

                    def clean_number(num):
                        if pd.isna(num):
                            return 0.0
                        if isinstance(num, (int, float)):
                            return float(num)
                        return float(str(num).replace(',', ''))
                        
                    total = clean_number(row[col_total])
                    igv = clean_number(row[col_igv])

                    cantidad = clean_number(row[col_cantidad])

                    if serie not in data_serie_total:
                        data_serie_total[serie] = 0.0
                        data_serie_igv[serie] = 0.0
                        conteo_series[serie] = 0
                        # Solo guardamos el valor de api_almacen_id la primera vez que vemos la serie
                        data_serie_api[serie] = api_value

                    data_serie_total[serie] += total
                    data_serie_igv[serie] += igv
                    conteo_series[serie] += 1

                    # Creamos una clave única basada en producto y api_almacen_id
                    producto_key = (producto, api_value)
                    
                    # Inicializamos los contadores si es la primera vez que vemos este producto con este api_value
                    if producto_key not in data_cantidad:
                        data_cantidad[producto_key] = 0.0
                        data_producto_total[producto_key] = 0.0
                        data_producto_info[producto_key] = api_value
                    
                    # Sumamos las cantidades
                    data_cantidad[producto_key] += cantidad
                    data_producto_total[producto_key] += total

                    if col_archivo is not None:
                        if archivo not in data_cantidad_archivo:
                            data_cantidad_archivo[archivo] = 0.0
                            data_archivo_serie[archivo] = set()
                            data_archivo_api[archivo] = api_value
                        data_cantidad_archivo[archivo] += 1
                        data_archivo_serie[archivo].add(serie)
                except Exception:
                    continue

            resultados = []
            total_total = 0.0
            for serie in sorted(data_serie_total.keys()):
                total_total = data_serie_total[serie]
                total_igv = data_serie_igv[serie]
                conteo = conteo_series[serie]
                api_almacen_id = data_serie_api.get(serie, "")
                
                resultados.append({
                    'serie': serie,
                    'conteo': conteo,
                    'igv': round(total_igv, 2),
                    'total': round(total_total, 2),
                    'fecha': primer_valor_fecha,
                    'api_almacen_id': api_almacen_id
                })

            resultados_productos = []
            # Ordenamos primero por nombre de producto y luego por api_almacen_id
            for (producto, api_value) in sorted(data_cantidad.keys()):
                producto_key = (producto, api_value)
                cantidad = data_cantidad[producto_key]
                total = data_producto_total[producto_key]
                
                resultados_productos.append({
                    'producto': producto,
                    'cantidad': cantidad,
                    'total': round(total, 2),
                    'fecha': primer_valor_fecha,
                    'api_almacen_id': api_value
                })

            resultados_archivo = []
            for archivo in sorted(data_cantidad_archivo.keys()):
                cantidad = data_cantidad_archivo[archivo]
                series = data_archivo_serie[archivo]
                api_almacen_id = data_archivo_api.get(archivo, "")
                resultados_archivo.append({
                    'archivo': archivo,
                    'cantidad': cantidad,
                    'series': list(series),
                    'api_almacen_id': api_almacen_id
                })

            return jsonify({
                'status': 'success',
                'estado': estado,
                'resultados': resultados,
                'resultados_productos': resultados_productos,
                'resultados_archivo': resultados_archivo
            })
        
        else:
            return jsonify({'status': 'error', 'message': 'Invalid estado value'}), 400
   
    except Exception as e:
        # Si algo falla, que lo capture acá también
        return jsonify({'status': 'error', 'message': f'Unexpected error: {str(e)}'}), 500

@app.route('/unificar', methods=['POST'])
def unificar_archivos():
    try:
        archivos = [
            request.files[key]
            for key in request.files
            if key.startswith('archivos[')
        ]

        if not archivos:
            return jsonify({'error': 'No se enviaron archivos'}), 400

        carpeta_destino = os.path.join(os.getcwd(), 'uploads/unificados')
        os.makedirs(carpeta_destino, exist_ok=True)
        limpiar_carpeta(carpeta_destino)  # Limpia carpeta antes de guardar
        temp_dir = tempfile.mkdtemp()
        columnas_deseadas = ['Serie','Fecha', 'IGV', 'Total', 'Producto', 'Cantidad', 'api_almacen_id']

        dfs = []
        for archivo in archivos:
            if not archivo.filename:
                return jsonify({'error': 'Uno de los archivos no tiene nombre válido'}), 400

            if not extension_valida(archivo.filename):
                return jsonify({'error': f'El archivo {archivo.filename} tiene una extensión no permitida'}), 400

            nombre_seguro = secure_filename(archivo.filename)
            ruta_temporal = os.path.join(temp_dir, nombre_seguro)
            archivo.save(ruta_temporal)

            try:
                df = pd.read_excel(ruta_temporal, skiprows=1)
            except Exception as e:
                return jsonify({'error': f'No se pudo leer el archivo {archivo.filename}: {str(e)}'}), 400

            if df.empty:
                return jsonify({'error': f'El archivo {archivo.filename} está vacío'}), 400

            columnas_presentes = [col for col in columnas_deseadas if col in df.columns]
            if not columnas_presentes:
                return jsonify({'error': f'El archivo {archivo.filename} no tiene columnas válidas.'}), 400

            df_filtrado = df[columnas_presentes].copy()
            if df_filtrado.empty:
                return jsonify({'error': f'El archivo {archivo.filename} no tiene datos válidos en las columnas seleccionadas.'}), 400

            df_filtrado["Archivo_Origen"] = archivo.filename
            dfs.append(df_filtrado)

        if not dfs:
            return jsonify({'error': 'No se pudieron leer datos válidos'}), 400

        df_final = pd.concat(dfs, ignore_index=True)
        if df_final.empty:
            return jsonify({'error': 'No se pudieron unificar datos válidos. Verifique el contenido de los archivos.'}), 400

        nombre_salida = f"excel_unificado_{int(datetime.now().timestamp())}.xlsx"
        ruta_salida = os.path.join(carpeta_destino, nombre_salida)

        try:
            with pd.ExcelWriter(ruta_salida) as writer:
                df_final.to_excel(writer, index=False, header=True, startrow=1)
        except Exception as e:
            return jsonify({'error': f'Error al guardar el archivo unificado: {str(e)}'}), 500

        return jsonify({'status': 'success', 'archivo': nombre_salida})

    except Exception as e:
        print(f"Error inesperado: {e}")
        return jsonify({'error': f'Error interno del servidor: {str(e)}'}), 500

    finally:
        shutil.rmtree(temp_dir, ignore_errors=True)

@app.route('/descargas/<nombre_archivo>', methods=['GET'])
def descargar_archivo(nombre_archivo):
    try:
        carpeta_destino = os.path.join(os.getcwd(), 'uploads/unificados')
        return send_from_directory(carpeta_destino, nombre_archivo, as_attachment=True)
    except Exception as e:
        return jsonify({'error': f'No se pudo descargar el archivo: {str(e)}'}), 500

@app.route('/verificar', methods=['POST'])
def verificar():
    serie_buscada = request.args.get('serie', type=str)
    if not serie_buscada:
        return jsonify({'status': 'error', 'message': 'Serie no proporcionada'}), 400

    if 'file' not in request.files:
        return jsonify({'status': 'error', 'message': 'No se recibió ningún archivo'}), 400

    file = request.files['file']

    try:
        df = pd.read_excel(file, header=None, engine='openpyxl')
    except Exception as e:
        return jsonify({'status': 'error', 'message': f'No se pudo leer el archivo: {str(e)}'}), 400

    # Asegúrate de que haya al menos 8 filas para tener encabezado y datos
    if len(df) <= 7:
        return jsonify({'status': 'error', 'message': 'El archivo no tiene suficientes filas'}), 400

    # La fila 8 (índice 7) es el encabezado real
    header = df.iloc[7].tolist()
    data = df.iloc[8:].copy()
    data.columns = header

    # Validar que existen las columnas requeridas
    required_columns = ['Número', 'Total', 'Fecha emisión', 'Estado']
    for col in required_columns:
        if col not in data.columns:
            return jsonify({'status': 'error', 'message': f'La columna {col} no existe en el archivo'}), 400

    # Dividir la columna 'Número' en 'Serie' y 'Numero'
    numero_split = data['Número'].astype(str).str.split('-', n=1, expand=True)
    data['Serie'] = numero_split[0].str.strip()
    data['Numero'] = numero_split[1].str.strip()

    # Filtrar por la serie buscada
    filtrado = data[data['Serie'] == serie_buscada]

    # Si no hay registros
    if filtrado.empty:
        return jsonify({
            'status': 'success',
            'resultados': []
        }), 200

    # Preparar respuesta
    registros = []
    for _, row in filtrado.iterrows():
        registros.append({
            'serie': row['Serie'],
            'numero': row['Numero'],
            'total': row['Total'],
            'fecha': str(row['Fecha emisión']),
            'estado': row['Estado'],
        })

    return jsonify({
        'status': 'success',
        'resultados': registros
    }), 200

if __name__ == '__main__':
    app.run(host="0.0.0.0", port=5000, debug=False)