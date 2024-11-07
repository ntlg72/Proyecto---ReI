from pyspark.sql import SparkSession
from pyspark.sql.functions import month, year, count, concat, lit, lpad, sum, when
import matplotlib.pyplot as plt
import plotly.express as px
import pandas as pd
import seaborn as sns
from matplotlib import patheffects
from matplotlib.patches import PathPatch
from colorama import Fore, Style, init


# Crear una sesión de Spark
spark = SparkSession.builder \
    .appName("Análisis de Ventas") \
    .getOrCreate()


# Conectar a la base de datos MySQL dentro del contenedor Docker
jdbc_url_carritos_BD = "jdbc:mysql://localhost:32002/carritos_BD"
jdbc_url_productos_BD = "jdbc:mysql://localhost:32000/productos_BD"
connection_properties = {
    "user": "root",
    "password": "root",
    "driver": "com.mysql.cj.jdbc.Driver"
}


# Leer datos desde las bases de datos
facturas_df = spark.read.jdbc(url=jdbc_url_carritos_BD, table="factura", properties=connection_properties)
df_items = spark.read.jdbc(url=jdbc_url_carritos_BD, table="factura_items", properties=connection_properties)
df_productos = spark.read.jdbc(url=jdbc_url_productos_BD, table="productos", properties=connection_properties)


## frecuencias de las facturas


# Convertir la columna 'fecha' a tipo datetime
facturas_df = facturas_df.withColumn('fecha', facturas_df['fecha'].cast('timestamp'))


# Extraer año y mes
facturas_df = facturas_df.withColumn('año', year(facturas_df['fecha']))
facturas_df = facturas_df.withColumn('mes', month(facturas_df['fecha']))


# Agrupar por año y mes y contar el número de facturas
facturas_por_mes = facturas_df.groupBy('año', 'mes').agg(count('*').alias('frecuencia'))


# Crear una columna para mostrar el mes en formato 'YYYY-MM'
facturas_por_mes = facturas_por_mes.withColumn('año_mes', concat(facturas_por_mes['año'].cast('string'), lit('-'), lpad(facturas_por_mes['mes'].cast('string'), 2, '0')))


# Convertir a Pandas DataFrame para visualización
facturas_por_mes_pd = facturas_por_mes.orderBy('frecuencia', ascending=False).toPandas()


# Configuración del gráfico
plt.figure(figsize=(14, 7))


# Colores "Amanecer en Primavera"
spring_dawn_colors = ['#f7cac9', '#f8edeb', '#ffe5b4', '#c5e0dc', '#a2c8cc']


# Crear las sombras detrás de las barras con un desplazamiento leve para efecto 3D
for index, row in facturas_por_mes_pd.iterrows():
    bar_pos = index  # Posición de la barra en el eje x
    sombra = plt.bar(bar_pos, row['frecuencia'], color='gray', alpha=0.3, width=0.8, zorder=1)
    sombra[0].set_x(sombra[0].get_x() - 0.08)  # Desplazar sombra levemente hacia la izquierda
    sombra[0].set_y(sombra[0].get_y() - 0.3)   # Desplazar sombra levemente hacia abajo


# Crear las barras principales con los colores especificados
bars = plt.bar(facturas_por_mes_pd['año_mes'], facturas_por_mes_pd['frecuencia'],
               color=spring_dawn_colors, edgecolor='gray', linewidth=1.2, zorder=2)


# Mostrar valores encima de cada barra
for bar in bars:
    yval = bar.get_height()
    plt.text(bar.get_x() + bar.get_width() / 2, yval + 1, f'{yval:.0f}', ha='center', va='bottom',
             fontsize=10, color='#34495E', weight='bold')


# Estilo del gráfico con ajustes en títulos y etiquetas
plt.title("Frecuencia de Facturas por Mes", fontsize=18, fontweight='bold', color='#2C3E50')
plt.xlabel("Año y Mes", fontsize=14, labelpad=15, color='#2C3E50')
plt.ylabel("Número de Facturas", fontsize=14, labelpad=15, color='#2C3E50')


# Fondo y cuadrícula con estilo personalizado
plt.gca().set_facecolor('#F9F9F9')
plt.grid(axis='y', linestyle='--', color='#BDC3C7', alpha=0.6)


# Rotación de etiquetas y ajuste de diseño
plt.xticks(rotation=45, fontsize=10, color='#2C3E50')
plt.yticks(color='#2C3E50')
plt.tight_layout()


# Definir el path de salida
output_facturas_path = '/vagrant/frecuencia_facturas.png'


# Guardar el gráfico en la ruta especificada
plt.savefig(output_facturas_path)


### mapa del colombia con las ventas
# Agrupar por ciudad y sumar las ventas
ventas_por_ciudad = facturas_df.groupBy("ciudad").agg(sum("total").alias("total_ventas"))
ventas_por_ciudad_pd = ventas_por_ciudad.toPandas()

# Añadir coordenadas de las ciudades
coordenadas = {
    'Medellin': {'lat': 6.2442, 'lon': -75.5812},
    'Bogota': {'lat': 4.7110, 'lon': -74.0721},
    'Cali': {'lat': 3.4516, 'lon': -76.5320},
    'Cartagena': {'lat': 10.3910, 'lon': -75.4794}
}
ventas_por_ciudad_pd['latitud'] = ventas_por_ciudad_pd['ciudad'].map(lambda x: coordenadas[x]['lat'])
ventas_por_ciudad_pd['longitud'] = ventas_por_ciudad_pd['ciudad'].map(lambda x: coordenadas[x]['lon'])

# Conversión del campo 'total_ventas' a un tipo numérico
ventas_por_ciudad_pd['total_ventas'] = pd.to_numeric(ventas_por_ciudad_pd['total_ventas'], errors='coerce')

# Filtrar datos no válidos (si existen)
ventas_por_ciudad_pd = ventas_por_ciudad_pd.dropna(subset=['total_ventas'])

# Crear el mapa con Plotly y personalizar el texto de hover
fig = px.scatter_mapbox(
    ventas_por_ciudad_pd,
    lat="latitud",
    lon="longitud",
    size="total_ventas",
    color="ciudad",
    mapbox_style="carto-positron",
    zoom=5,
    center={"lat": 4.7110, "lon": -74.0721},
    size_max=50,
    hover_name="ciudad",
    hover_data={"latitud": True, "longitud": True, "total_ventas": True}
)

# Resaltar las ciudades específicas con colores personalizados
fig.update_traces(marker=dict(size=12, color='red'), selector=dict(name="Medellin"))
fig.update_traces(marker=dict(size=12, color='blue'), selector=dict(name="Bogota"))
fig.update_traces(marker=dict(size=12, color='green'), selector=dict(name="Cali"))
fig.update_traces(marker=dict(size=12, color='purple'), selector=dict(name="Cartagena"))

# Definir el path de salida
output_ventas_path = '/vagrant/ventas_por_ciudad.html'
fig.write_html(output_ventas_path)

print(f"Mapa generado y guardado en {output_ventas_path}")





## top de productos mas vendidso


# Calcular el top productos vendidos
top_productos = df_items.groupBy("product_id").agg(sum("quantity").alias("cantidad_vendida")).orderBy("cantidad_vendida", ascending=False).limit(10)


# Combinar con los nombres de productos
top_productos = top_productos.join(df_productos, on="product_id").select("product_name", "cantidad_vendida")


# Configuración de la fuente
plt.rcParams['font.family'] = 'DejaVu Sans'


# Seleccionar los 5 productos más vendidos y convertir a Pandas
top_5_productos = top_productos.limit(5).toPandas()


# Crear el gráfico de dona
plt.figure(figsize=(10, 8))
plt.pie(
    top_5_productos['cantidad_vendida'],
    labels=top_5_productos['product_name'],
    autopct='%1.1f%%',
    startangle=90,
    pctdistance=0.85,
    explode=[0.05] * 5,
    colors=plt.cm.Pastel2.colors,
    shadow=True,
    wedgeprops={'edgecolor': 'gray', 'linewidth': 1},
    textprops={'color': '#336ca5', 'fontsize': 12}
)


# Añadir círculo en el centro para crear efecto de dona
centre_circle = plt.Circle((0, 0), 0.60, fc='white')
fig = plt.gcf()
fig.gca().add_artist(centre_circle)


# Título con estilo personalizado
plt.title("Top 5 Productos Más Vendidos", fontsize=18, fontweight='bold', color='#005187')


# Guardar el gráfico
output_path_top_productos = '/vagrant/top_productos.png'
plt.savefig(output_path_top_productos)  # Guarda la imagen en la ruta especificada


plt.tight_layout()
plt.close()


### compras por categoria


# Calcular ventas por categoría
ventas_por_categoria = df_items.join(df_productos, on="product_id", how="inner") \
    .groupBy("product_category") \
    .agg(sum("price").alias("total_ventas_categoria"), sum("quantity").alias("total_cantidad_vendida")) \
    .sort("total_ventas_categoria", ascending=False)


# Convertir el DataFrame de Spark a Pandas
ventas_por_categoria_pd = ventas_por_categoria.toPandas()


# Estilo de gráfico
plt.style.use('fivethirtyeight')


# Crear una paleta de colores pastel
pastel_colors = sns.color_palette("pastel", len(ventas_por_categoria_pd))
colors = pastel_colors.as_hex()  # Convertir a colores hexadecimales


# Crear el gráfico de barras
plt.figure(figsize=(14, 7))
bars = plt.bar(
    ventas_por_categoria_pd['product_category'],
    ventas_por_categoria_pd['total_ventas_categoria'],
    color=colors,
    edgecolor='gray',
    linewidth=1.2
)


# Añadir etiquetas encima de las barras
for bar in bars:
    yval = bar.get_height()
    plt.text(
        bar.get_x() + bar.get_width() / 2,
        yval + 1000,  # Espacio extra para la legibilidad
        f'{yval:,.0f}',
        ha='center',
        va='bottom',
        fontsize=11,
        color='dimgray',
        fontweight='bold'
    )


# Configuración de título y etiquetas
plt.title("Ventas por Categoría de Producto", fontsize=24, fontweight='bold', color='slategray', pad=20)
plt.xlabel("Categoría de Producto", fontsize=18, fontweight='bold', color='slategray', labelpad=15)
plt.ylabel("Total Ventas ($)", fontsize=18, fontweight='bold', color='slategray', labelpad=15)
plt.xticks(rotation=0, ha='center', fontsize=13, color='gray', fontweight='bold')
plt.yticks(fontsize=13, color='dimgray')
plt.grid(axis='y', linestyle=':', alpha=0.5)


# Personalizar el fondo y bordes
plt.gca().set_facecolor('whitesmoke')
plt.gca().spines['top'].set_visible(False)
plt.gca().spines['right'].set_visible(False)
plt.gca().spines['left'].set_color('lightgray')
plt.gca().spines['bottom'].set_color('lightgray')


# Guardar y mostrar el gráfico
output_path_ventas_categoria = '/vagrant/ventas_por_categoria.png'
plt.savefig(output_path_ventas_categoria, dpi=300, bbox_inches='tight')
plt.tight_layout()
plt.show()


###
rango_precios = df_productos.select('product_name', 'unit_price_cop')


# Asignar rangos de precios usando Spark
rango_precios = rango_precios.withColumn(
    'rango_precio',
    when(rango_precios['unit_price_cop'] < 500000, '< $500,000')
    .when((rango_precios['unit_price_cop'] >= 500000) & (rango_precios['unit_price_cop'] < 1000000), '$500,000 - $999,999')
    .when((rango_precios['unit_price_cop'] >= 1000000) & (rango_precios['unit_price_cop'] < 2000000), '$1,000,000 - $1,999,999')
    .otherwise('>= $2,000,000')
)


# Agrupar por 'rango_precio' y contar las ocurrencias de 'product_name'
rango_precios_count = rango_precios.groupBy('rango_precio').agg(count('product_name').alias('count')).orderBy('rango_precio')


# Convertir el DataFrame de Spark a Pandas para la visualización
rango_precios_pd = rango_precios_count.toPandas()


# Crear el gráfico de barras
plt.figure(figsize=(12, 7), facecolor='white')  # Fondo blanco
colors = sns.color_palette("pastel", len(rango_precios_pd))  # Paleta de colores pasteles


# Dibujar las barras con sombra para efecto 3D
bars = plt.bar(rango_precios_pd['rango_precio'], rango_precios_pd['count'], color=colors, edgecolor='darkgray', linewidth=2, zorder=8)


# Crear el efecto 3D aplicando sombras en cada barra
for bar in bars:
    # Crear una sombra notablemente desplazada
    shadow = PathPatch(bar.get_path(), facecolor='gray', lw=0, alpha=0.5, zorder=2)  # Aumentar la opacidad
    shadow.set_transform(bar.get_transform() + plt.matplotlib.transforms.Affine2D().translate(-8, -8))  # Mayor desplazamiento
    plt.gca().add_patch(shadow)


# Añadir etiquetas encima de las barras con un estilo limpio y moderno
for bar in bars:
    yval = bar.get_height()
    plt.text(
        bar.get_x() + bar.get_width() / 2,
        yval + 10,
        f'{yval}',
        ha='center',
        va='bottom',
        fontsize=14,
        fontweight='bold',
        color='#95b0c9'
    )


# Mejorar el título y etiquetas
plt.title("Distribución de Productos por Rango de Precio", fontsize=24, fontweight='bold', color='#5a758c', pad=20)
plt.xlabel("Rango de Precio", fontsize=18, fontweight='bold', color='#b3cfe9', labelpad=15)
plt.ylabel("Cantidad de Productos", fontsize=18, fontweight='bold', color='#b3cfe9', labelpad=15)
plt.xticks(fontsize=14, fontweight='bold', color='gray', rotation=0)
plt.yticks(fontsize=14, color='gray')


# Eliminar la cuadrícula en el área de las barras y en el fondo
plt.gca().set_axisbelow(True)
plt.grid(visible=False)


# Guardar el gráfico en la ruta de Vagrant
output_path_rango_precio = '/vagrant/producto_rango_precio.png'
plt.savefig(output_path_rango_precio, dpi=300, bbox_inches='tight')


plt.tight_layout()
plt.show()
