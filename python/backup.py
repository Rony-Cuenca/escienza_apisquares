from flask import Flask, jsonify, request
import pandas as pd
import io

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
        
    elif estado == 2:
        header = df.iloc[3].tolist()
        resultado = header[1] if len(header) > 1 else None

        return jsonify({
            'status': 'success',
            'estado': estado,
            'resultados': resultado
        })
    elif estado == 3:
        if len(df) <= 1:
            return jsonify({'status': 'error', 'message': 'Not enough rows'}), 400

        header = df.iloc[1].tolist()
        
        try:
            col_serie = header.index('Serie')
            col_igv = header.index('IGV')
            col_total = header.index('Total')
        except ValueError as e:
            return jsonify({'status': 'error', 'message': f'Missing columns: {str(e)}'}), 400

        data_serie_total = {}
        data_serie_igv = {}
        conteo_series = {}

        for _, row in df.iloc[2:].iterrows():
            if pd.isna(row[col_serie]) or pd.isna(row[col_igv]) or pd.isna(row[col_total]):
                continue

            try:
                if not col_serie:
                    continue

                def clean_number(num):
                    if pd.isna(num):
                        return 0.0
                    if isinstance(num, (int, float)):
                        return float(num)
                    return float(str(num).replace(',', ''))
                
                total = clean_number(row[col_total])
                igv = clean_number(row[col_igv])

                if col_serie not in data_serie_total:
                    data_serie_total[col_serie] = 0.0
                    data_serie_igv[col_serie] = 0.0
                    conteo_series[col_serie] = 0

                data_serie_total[col_serie] += total
                data_serie_igv[col_serie] += igv
                conteo_series[col_serie] += 1

            except Exception:
                continue

        resultados = []
        for serie in sorted(data_serie_total.keys()):
            total_total = data_serie_total[serie]
            total_igv = data_serie_igv[serie]

            resultados.append({
                'serie': serie,
                'conteo': conteo_series[serie],
                'igv': round(total_igv, 2),
                'total': round(total_total, 2),
                'fecha': primer_valor_fecha
            })

        return jsonify({
            'status': 'success',
            'estado': estado,
            'resultados': resultados
        })
   
if __name__ == '__main__':
    app.run(port=5000, debug=False)  # Disable debug mode for production