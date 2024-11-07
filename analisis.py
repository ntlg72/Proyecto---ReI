from pyspark.sql import SparkSession
from pyspark.sql.functions import month, year, count, concat, lit, lpad, sum, when
import matplotlib.pyplot as plt
import plotly.express as px
import pandas as pd
import seaborn as sns
from matplotlib.patches import PathPatch


# Crear una sesión de Spark
spark = SparkSession.builder \
    .appName("Análisis de Ventas") \
    .getOrCreate()


# Conectar a la base de datos MySQL dentro del contenedor Docker
jdbc_url_carritos_BD = "jdbc:mysql://localhost:32002/carritos_BD"
connection_properties = {
    "user": "root",
    "password": "root",
    "driver": "com.mysql.cj.jdbc.Driver"
}


# Conectar a la base de datos MySQL dentro del contenedor Docker
jdbc_url_productos_BD = "jdbc:mysql://localhost:32000/productos_BD"
connection_properties = {
    "user": "root",
    "password": "root",
    "driver": "com.mysql.cj.jdbc.Driver"
}


# Leer datos desde la base de datos
facturas_df = spark.read.jdbc(url=jdbc_url_carritos_BD, table="factura", properties=connection_properties)
df_items = spark.read.jdbc(url=jdbc_url_carritos_BD, table="factura_items", properties=connection_properties)  # Asegúrate de que este DataFrame esté definido
df_productos = spark.read.jdbc(url=jdbc_url_productos_BD, table="productos", properties=connection_properties)  # Asegúrate de que este DataFrame esté definido


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
facturas_por_mes_pd = facturas_por_mes.orderBy('año', 'mes').toPandas()


# Mostrar el DataFrame resultante
print(facturas_por_mes_pd)


# Crear el histograma
plt.figure(figsize=(12, 6))
bars = plt.bar(facturas_por_mes_pd['año_mes'], facturas_por_mes_pd['frecuencia'], color='skyblue')


# Añadir el total encima de cada barra
for bar in bars:
    yval = bar.get_height()
    plt.text(bar.get_x() + bar.get_width()/2, yval, f'{yval:.0f}', ha='center', va='bottom')


# Configurar el gráfico
plt.title("Frecuencia de Facturas por Mes")
plt.xlabel("Año y Mes")
plt.ylabel("Número de Facturas")
plt.xticks(rotation=45)
plt.grid(axis='y')
plt.tight_layout()


# Guardar el gráfico en la ruta de Vagrant
output_path = '/vagrant/frecuencia_facturas.png'
plt.savefig(output_path)  # Guarda la imagen en la ruta especificada
plt.close()  # Cierra la figura para liberar memoria


# Agrupar por ciudad y sumar las ventas
ventas_por_ciudad = facturas_df.groupBy("ciudad").agg(sum("total").alias("total_ventas"))


# Convertir a Pandas DataFrame para usar con Plotly
ventas_por_ciudad_pd = ventas_por_ciudad.toPandas()


# Asegurarse de que total_ventas sea numérico
ventas_por_ciudad_pd['total_ventas'] = pd.to_numeric(ventas_por_ciudad_pd['total_ventas'], errors='coerce')


# Añadir coordenadas de las ciudades
coordenadas = {
    'Medellín': {'lat': 6.2442, 'lon': -75.5812},
    'Bogotá': {'lat': 4.7110, 'lon': -74.0721},
    'Cali': {'lat': 3.4516, 'lon': -76.5320},
    'Cartagena': {'lat': 10.3910, 'lon': -75.4794}
}


ventas_por_ciudad_pd['latitud'] = ventas_por_ciudad_pd['ciudad'].map(lambda x: coordenadas[x]['lat'])
ventas_por_ciudad_pd['longitud'] = ventas_por_ciudad_pd['ciudad'].map(lambda x: coordenadas[x]['lon'])


# Crear el mapa con Plotly
fig = px.scatter_mapbox(
    ventas_por_ciudad_pd,
    lat="latitud",
    lon="longitud",
    size="total_ventas",
    color="ciudad",
    hover_name="ciudad",
    hover_data=["total_ventas"],
    zoom=5,
    mapbox_style="carto-positron"
)


# Resaltar las ciudades específicas
fig.update_traces(marker=dict(size=12, color='red'), selector=dict(name="Medellín"))
fig.update_traces(marker=dict(size=12, color='blue'), selector=dict(name="Bogotá"))
fig.update_traces(marker=dict(size=12, color='green'), selector=dict(name="Cali"))
fig.update_traces(marker=dict(size=12, color='purple'), selector=dict(name="Cartagena"))


# Guardar el gráfico como HTML
fig.write_html('/vagrant/ventas_por_ciudad.html')


# Mostrar el mapa
fig.show()


# 4. Top Productos Vendidos
top_productos = df_items.groupBy("product_id").agg(sum("quantity").alias("cantidad_vendida")).orderBy("cantidad_vendida", ascending=False).limit(10)


# Combinar con los productos para obtener nombres
top_productos = top_productos.join(df_productos, on="product_id").select("product_name", "cantidad_vendida")


# Cambiar la fuente a DejaVu Sans, que tiene un soporte amplio de caracteres
plt.rcParams['font.family'] = 'DejaVu Sans'


# Seleccionar los 5 productos más vendidos
top_5_productos = top_productos.limit(5).toPandas()


# Crear el gráfico de pastel
plt.figure(figsize=(10, 8))
plt.pie(
    top_5_productos['cantidad_vendida'],
    labels=top_5_productos['product_name'],
    autopct='%1.1f%%',
    startangle=90,
    pctdistance=0.85,
    explode=[0.05]*5,
    colors=plt.cm.Pastel2.colors,
    shadow=True,
    wedgeprops={'edgecolor': 'gray', 'linewidth': 1},
    textprops={'color': '#336ca5', 'fontsize': 12}
)


# Agregar un círculo blanco en el centro
centre_circle = plt.Circle((0, 0), 0.60, fc='white')
fig = plt.gcf()
fig.gca().add_artist(centre_circle)


# Título con fuente y color personalizado
plt.title("Top 5 Productos Más Vendidos", fontsize=18, fontweight='bold', color='#005187')


# Guardar el gráfico en la ruta de Vagrant
output_path_top_productos = '/vagrant/top_productos.png'
plt.savefig(output_path_top_productos)  # Guarda la imagen en la ruta especificada


plt.tight_layout()
plt.show()




### ventas por categoria


ventas_por_categoria = df_items.join(df_productos, on="product_id", how="inner") \
    .groupBy("product_category") \
    .agg(sum("price").alias("total_ventas_categoria"), sum("quantity").alias("total_cantidad_vendida")) \
    .sort("total_ventas_categoria", ascending=False)


# Convertir a Pandas
ventas_por_categoria_pd = ventas_por_categoria.toPandas()  # Cambia aquí


# Estilo de gráfico
plt.style.use('fivethirtyeight')  # Usamos el estilo 'fivethirtyeight' para un look profesional


# Crear una paleta de colores pastel
pastel_colors = sns.color_palette("pastel", len(ventas_por_categoria_pd))
colors = pastel_colors.as_hex()  # Convertir a hex para usar en Matplotlib


# Crear el gráfico de barras
plt.figure(figsize=(14, 7))
bars = plt.bar(
    ventas_por_categoria_pd['product_category'],
    ventas_por_categoria_pd['total_ventas_categoria'],
    color=colors,
    edgecolor='gray',  # Borde de las barras en gris suave
    linewidth=1.2
)


# Agregar etiquetas encima de las barras
for bar in bars:
    yval = bar.get_height()
    plt.text(
        bar.get_x() + bar.get_width() / 2,
        yval + 1000,  # Un margen adicional para mejor legibilidad
        f'{yval:,.0f}',
        ha='center',
        va='bottom',
        fontsize=11,
        color='dimgray',
        fontweight='bold'
    )


# Configuración de título y etiquetas con colores acordes
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


# Guardar el gráfico en la ruta de Vagrant
output_path_ventas_categoria = '/vagrant/ventas_por_categoria.png'
plt.savefig(output_path_ventas_categoria, dpi=300, bbox_inches='tight')


plt.tight_layout()
plt.show()






### rango de precio


# Crear un nuevo DataFrame seleccionando las columnas necesarias
rango_precios = df_productos.select('product_name', 'unit_price_cop')


# Asignar rangos de precios
rango_precios = rango_precios.withColumn(
    'rango_precio',
    when(rango_precios['unit_price_cop'] < 500000, '< $500,000')
    .when((rango_precios['unit_price_cop'] >= 500000) & (rango_precios['unit_price_cop'] < 1000000), '$500,000 - $999,999')
    .when((rango_precios['unit_price_cop'] >= 1000000) & (rango_precios['unit_price_cop'] < 2000000), '$1,000,000 - $1,999,999')
    .otherwise('>= $2,000,000')
)


# Agrupar por 'rango_precio' y contar las ocurrencias de 'product_name'
rango_precios_count = rango_precios.groupBy('rango_precio').agg(count('product_name').alias('count')).orderBy('rango_precio')


# Convertir a Pandas
rango_precios_pd = rango_precios_count.toPandas()


# Crear el gráfico de barras
plt.figure(figsize=(12, 7), facecolor='white')  # Fondo blanco
colors = sns.color_palette("pastel", len(rango_precios_pd))  # Paleta de colores pasteles


# Dibujar las barras con sombra para efecto 3D
bars = plt.bar(rango_precios_pd['rango_precio'], rango_precios_pd['count'], color=colors, edgecolor='darkgray', linewidth=2, zorder=8)


# Crear el efecto 3D aplicando sombras en cada barra
for bar in bars:
    bar.set_edgecolor('none')  # Sin bordes para barras principales
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
