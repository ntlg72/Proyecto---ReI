FROM python:3.8

# Define el directorio de trabajo dentro del contenedor
WORKDIR /app

# Copia los archivos CSV y scripts para carritos
COPY ./facturas.csv /app/db/carritodb/
COPY ./items_factura.csv /app/db/carritodb/
COPY ./cargar_datos_factura.py /app/db/carritodb/
COPY ./cargar_datos_items_factura.py /app/db/carritodb/

# Copia los archivos CSV y scripts para productos
COPY ./carga_datos_productos.py /app/db/productodb/
COPY ./productos.csv /app/db/productodb/

# Copia los archivos CSV y scripts para usuarios
COPY ./carga_datos_usuarios.py /app/db/usuariodb/
COPY ./usuarios.csv /app/db/usuariodb/

# Copia el script principal de carga de datos
COPY ./carga.py /app/

# Instala las dependencias necesarias
RUN pip install pandas mysql-connector-python sqlalchemy

# Comando por defecto para ejecutar solo el script carga.py
CMD ["sh", "-c", "sleep 30 && python3 /app/carga.py"]