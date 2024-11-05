import subprocess


# Lista de scripts a ejecutar
scripts = [
    'db/carritodb/carga_datos_factura.py',
    'db/productodb/carga_datos_productos.py',
    'db/carritodb/carga_datos_items_facturas.py',
    'db/usuariodb/carga_datos_usuarios.py'
]


# Función para ejecutar cada script
def ejecutar_scripts(scripts):
    for script in scripts:
        try:
            print(f"Ejecutando {script}...")
            subprocess.run(['python3', script], check=True)
            print(f"{script} ejecutado con éxito.")
        except subprocess.CalledProcessError as e:
            print(f"Ocurrió un error al ejecutar {script}: {e}")


if __name__ == "__main__":
    ejecutar_scripts(scripts)
