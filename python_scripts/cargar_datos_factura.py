import pandas as pd
from sqlalchemy import create_engine

# Configuración de conexión
usuario = "root"
contraseña = "root"
host = "carritos_BD"
puerto = "3306"
nombre_bd = "carritos_BD"
ruta_csv = '/app/db/carritodb/facturas.csv'

# Crear URL de conexión
database_url = f"mysql+mysqlconnector://{usuario}:{contraseña}@{host}:{puerto}/{nombre_bd}"
engine = create_engine(database_url)

# Leer el archivo CSV
try:
    facturas_df = pd.read_csv(ruta_csv, index_col=0)
    print("Datos leídos del CSV:", facturas_df)
except Exception as e:
    print("Error al leer el archivo CSV:", e)

# Verificar y limpiar el DataFrame
facturas_df = facturas_df.loc[:, ~facturas_df.columns.str.contains('^Unnamed')]

# Importar a MySQL
try:
    facturas_df.to_sql('factura', con=engine, if_exists='append', index=False)
    print("Datos importados con éxito.")
except Exception as e:
    print("Ocurrió un error al importar los datos:", e)
    raise  # Esto mostrará el traceback completo del error