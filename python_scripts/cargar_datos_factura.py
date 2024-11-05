import pandas as pd
from sqlalchemy import create_engine


# Configurar la URL de la conexión usando SQLAlchemy
usuario = "root"
contraseña = "root"
host = "carritos_BD"  # Cambia a carritos_BD para que apunte al contenedor MySQL
puerto = "3306"       # Puerto interno del contenedor MySQL
nombre_bd = "carritos_BD"
ruta_csv = '/app/db/carritodb/facturas.csv'  # Ruta dentro del contenedor python_scripts

# Crear la URL de conexión
database_url = f"mysql+mysqlconnector://{usuario}:{contraseña}@{host}:{puerto}/{nombre_bd}"
engine = create_engine(database_url)


# Leer el archivo CSV, ignorando la columna de índice si existe
facturas_df = pd.read_csv(ruta_csv, index_col=0)


# O eliminar 'Unnamed: 0' si persiste como columna
facturas_df = facturas_df.loc[:, ~facturas_df.columns.str.contains('^Unnamed')]


# Importar el DataFrame a MySQL
try:
    facturas_df.to_sql('factura', con=engine, if_exists='append', index=False)
    print("Datos importados con éxito.")
except Exception as e:
    print("Ocurrió un error al importar los datos:", e)
