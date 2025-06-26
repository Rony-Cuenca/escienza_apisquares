from flask import Flask, jsonify, request, send_file, send_from_directory
from werkzeug.utils import secure_filename
import pandas as pd
import os
from datetime import datetime
import tempfile
import shutil
import time
import threading

app = Flask(__name__)

def limpiar_carpeta(carpeta, delay=10):
    """Elimina todos los archivos en la carpeta después de cierto delay."""
    def limpiar():
        time.sleep(delay)
        for archivo in os.listdir(carpeta):
            archivo_path = os.path.join(carpeta, archivo)
            if os.path.isfile(archivo_path):
                os.remove(archivo_path)
    threading.Thread(target=limpiar).start()


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
        validarNubox = []

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

                total_fila = gravado + exonerado + inafecto + igv
                validarNubox.append({
                    'serie': serie,
                    'total': total_fila
                })

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
            'resultados': resultados,
            'validarNubox': validarNubox
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
        except ValueError as e:
            return jsonify({'status': 'error', 'message': f'Missing columns: {str(e)}'}), 400

        data_serie_total = {}
        data_serie_igv = {}
        conteo_series = {}

        data_cantidad = {}

        primer_valor_fecha = df.iloc[2, col_fecha]  

        for _, row in df.iloc[2:].iterrows():
            if pd.isna(row[col_serie]) or pd.isna(row[col_igv]) or pd.isna(row[col_total]) or pd.isna(row[col_producto]) or pd.isna(row[col_cantidad]):
                continue

            try:
                serie = str(row[col_serie]).strip()
                producto = str(row[col_producto]).strip()
                
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

                data_serie_total[serie] += total
                data_serie_igv[serie] += igv
                conteo_series[serie] += 1

                if producto not in data_cantidad:
                    data_cantidad[producto] = 0.0

                data_cantidad[producto] += cantidad
            except Exception:
                continue

        resultados = []
        total_total = 0.0
        for serie in sorted(data_serie_total.keys()):
            total_total = data_serie_total[serie]
            total_igv = data_serie_igv[serie]
            conteo = conteo_series[serie]
            
            resultados.append({
                'serie': serie,
                'conteo': conteo,
                'igv': round(total_igv, 2),
                'total': round(total_total, 2),
                'fecha': primer_valor_fecha
            })

        resultados_productos = []
        for producto in sorted(data_cantidad.keys()):
            cantidad = data_cantidad[producto]
            
            resultados_productos.append({
                'producto': producto,
                'cantidad': cantidad,
                'total': round(total_total, 2)
            })

        return jsonify({
            'status': 'success',
            'estado': estado,
            'resultados': resultados,
            'resultados_productos': resultados_productos
        })
@app.route('/unificar', methods=['POST'])
def unificar_archivos():
    if 'archivos[]' not in request.files:
        return jsonify({'error': 'No se enviaron archivos'}), 400

    archivos = request.files.getlist('archivos[]')

    if not archivos:
        return jsonify({'error': 'Lista vacía de archivos'}), 400

    carpeta_destino = os.path.join(os.getcwd(), 'uploads/unificados')
    os.makedirs(carpeta_destino, exist_ok=True)
    temp_dir = tempfile.mkdtemp()
    try:
        dfs = []
        for archivo in archivos:
            nombre_seguro = secure_filename(archivo.filename)
            ruta_temporal = os.path.join(temp_dir, nombre_seguro)
            archivo.save(ruta_temporal)

            df = pd.read_excel(ruta_temporal)
            df["Archivo_Origen"] = archivo.filename
            dfs.append(df)

        df_final = pd.concat(dfs, ignore_index=True)

        nombre_salida = f"excel_unificado_{int(datetime.now().timestamp())}.xlsx"
        ruta_salida = os.path.join(carpeta_destino, nombre_salida)

        df_final.to_excel(ruta_salida, index=False)

        return jsonify({'status': 'ok', 'archivo': nombre_salida})
    except Exception as e:
        return jsonify({'error': str(e)}), 500
    finally:
        shutil.rmtree(temp_dir, ignore_errors=True)  

@app.route('/descargas/<nombre_archivo>', methods=['GET'])
def descargar_archivo(nombre_archivo):
    carpeta_destino = os.path.join(os.getcwd(), 'uploads/unificados')
    limpiar_carpeta(carpeta_destino)
    return send_from_directory(carpeta_destino, nombre_archivo, as_attachment=True)
    

if __name__ == '__main__':
    app.run(port=5000, debug=False)  # Disable debug mode for production