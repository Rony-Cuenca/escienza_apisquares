from flask import Flask, jsonify, request
import pandas as pd
import os
from datetime import datetime
import tempfile
import shutil

app = Flask(__name__)

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
    # Verificar si hay archivos en la solicitud
    if not request.files:
        return jsonify({'status': 'error', 'message': 'No files received'}), 400
    
    # Obtener todos los archivos (nota el getlist para array)
    files = request.files.getlist('file[]')  # Cambiado a 'file[]' para coincidir con PHP
    
    if not files:
        return jsonify({'status': 'error', 'message': 'No valid files uploaded'}), 400
    
    temp_dir = tempfile.mkdtemp()
    temp_files = []
    
    try:
        # Guardar archivos temporalmente
        for i, file in enumerate(files):
            if file.filename == '':
                continue
                
            if not (file.filename.endswith('.xlsx') or file.filename.endswith('.xls')):
                continue
                
            temp_path = os.path.join(temp_dir, f'temp_{i}.xlsx')
            file.save(temp_path)
            temp_files.append(temp_path)
        
        if not temp_files:
            return jsonify({'status': 'error', 'message': 'No valid Excel files to process'}), 400
        
        # Procesar archivos
        dfs = []
        for file_path in temp_files:
            try:
                df = pd.read_excel(file_path)
                df['Archivo_Origen'] = os.path.basename(file_path)
                dfs.append(df)
            except Exception as e:
                print(f"Error processing {file_path}: {str(e)}")
                continue
        
        if not dfs:
            return jsonify({'status': 'error', 'message': 'Could not read any Excel file'}), 400
        
        # Unificar DataFrames
        df_unificado = pd.concat(dfs, ignore_index=True)
        
        # Crear respuesta
        output = io.BytesIO()
        df_unificado.to_excel(output, index=False)
        output.seek(0)
        
        return send_file(
            output,
            mimetype='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            as_attachment=True,
            download_name=f'unificado_{datetime.now().strftime("%Y%m%d_%H%M%S")}.xlsx'
        )
        
    except Exception as e:
        return jsonify({'status': 'error', 'message': f'Error processing files: {str(e)}'}), 500
    
    finally:
        # Limpiar archivos temporales
        if os.path.exists(temp_dir):
            shutil.rmtree(temp_dir)
if __name__ == '__main__':
    app.run(port=5000, debug=False)  # Disable debug mode for production