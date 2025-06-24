import pandas as pd
import sys

import os

def procesar_edsuite(ruta_archivo):
    # Normalizar la ruta del archivo
    ruta_archivo = os.path.abspath(ruta_archivo)
    print(f"Procesando archivo: {ruta_archivo}")
    
    # Leer el archivo Excel
    try:
        df = pd.read_excel(ruta_archivo, header=None, engine='openpyxl')
        print("\nContenido del archivo:")
        print(df)
        
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
                serie = str(row[col_serie]).strip()
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

                if serie not in data_serie_total:
                    data_serie_total[serie] = 0.0
                    data_serie_igv[serie] = 0.0
                    conteo_series[serie] = 0

                data_serie_total[serie] += total
                data_serie_igv[serie] += igv
                conteo_series[serie] += 1

            except Exception:
                continue
        # Generar resultados
        print("\nResultados finales:")
        resultados = []
        for serie in sorted(data_serie_total.keys()):
            total_total = data_serie_total[serie]
            total_igv = data_serie_igv[serie]
            conteo = conteo_series[serie]
            
            print(f"\nSerie: {serie}")
            print(f"Conteo: {conteo}")
            print(f"IGV: {total_igv}")
            print(f"Total: {total_total}")
            
            resultados.append({
                'serie': serie,
                'conteo': conteo,
                'igv': round(total_igv, 2),
                'total': round(total_total, 2)
            })

        return resultados

    except Exception as e:
        print(f"Error general: {str(e)}")
        return None

# Ejemplo de uso
if __name__ == '__main__':
    if len(sys.argv) != 2:
        print("Uso: python prueba.py <ruta_archivo_excel>")
        print("Ejemplo: python prueba.py C:\\Users\\user\\Desktop\\Prueba.xlsx")
        sys.exit(1)
    
    # Ruta del archivo que quieres procesar
    ruta_archivo = sys.argv[1]
    
    # Procesar el archivo
    resultados = procesar_edsuite(ruta_archivo)
    
    if resultados:
        print("\nResultados finales:")
        for resultado in resultados:
            print(resultado)
    else:
        print("\nNo se pudieron procesar los datos")