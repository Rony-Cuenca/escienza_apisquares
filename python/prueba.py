import pandas as pd
import os
import argparse
from datetime import datetime

def unificar_excels(ruta_archivo, salida=None, hoja=0):
    """
    Une múltiples archivos Excel en uno solo.
    
    Args:
        ruta_archivo (str): Ruta del archivo Excel o directorio con archivos Excel
        salida (str, optional): Nombre del archivo de salida. Por defecto es 'unificado_<fecha>.xlsx'
        hoja (int/str, optional): Nombre o índice de la hoja a leer. Por defecto es 0 (primera hoja)
    """
    # Determinar si es archivo o directorio
    if os.path.isfile(ruta_archivo):
        archivos = [ruta_archivo]
    elif os.path.isdir(ruta_archivo):
        archivos = [os.path.join(ruta_archivo, f) for f in os.listdir(ruta_archivo) 
                    if f.endswith(('.xlsx', '.xls'))]
    else:
        raise ValueError("La ruta proporcionada no es un archivo ni un directorio válido")
    
    if not archivos:
        raise ValueError("No se encontraron archivos Excel para unificar")
    
    # Leer todos los archivos
    dfs = []
    for archivo in archivos:
        try:
            df = pd.read_excel(archivo, sheet_name=hoja)
            df['Archivo_Origen'] = os.path.basename(archivo)
            dfs.append(df)
            print(f"Procesado: {archivo}")
        except Exception as e:
            print(f"Error al procesar {archivo}: {str(e)}")
    
    if not dfs:
        raise ValueError("No se pudo leer ningún archivo Excel válido")
    
    # Unificar todos los DataFrames
    df_unificado = pd.concat(dfs, ignore_index=True)
    
    # Generar nombre de salida si no se proporciona
    if salida is None:
        fecha = datetime.now().strftime("%Y%m%d_%H%M%S")
        salida = f"unificado_{fecha}.xlsx"
    
    # Guardar el archivo unificado
    df_unificado.to_excel(salida, index=False)
    print(f"\nArchivos unificados guardados en: {os.path.abspath(salida)}")
    return salida

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Unificar archivos Excel en uno solo')
    parser.add_argument('ruta', help='Ruta del archivo Excel o directorio con archivos Excel')
    parser.add_argument('-o', '--output', help='Nombre del archivo de salida (opcional)')
    parser.add_argument('-s', '--sheet', default=0, 
                        help='Nombre o índice de la hoja a leer (por defecto: 0)')
    
    args = parser.parse_args()
    
    try:
        archivo_salida = unificar_excels(args.ruta, args.output, args.sheet)
    except Exception as e:
        print(f"Error: {str(e)}")