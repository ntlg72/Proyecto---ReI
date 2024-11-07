

# **Sistema de Gestión de Tienda en Microservicios**


# Proyecto: Redes e Infraestructura

[Michel Burgos Santos](https://github.com/Michelburgos)

[Juan David Daza Rivera](https://github.com/JuanDavidDazaR)

[Esmeralda Erazo Varela](https://github.com/memerazo)

[Natalia López Gallego](https://github.com/ntlg72)

## Descripción

Este proyecto desarrolla una solución de comercio electrónico basada en una aplicación web para el retailer “MiPunto”. La aplicación incluye un panel de administración para gestionar productos y visualizar facturas, además de una interfaz amigable para los clientes. Los clientes pueden navegar por el catálogo, gestionar el carrito de compras y generar facturas. El proyecto se divide en dos partes: la primera, el empaquetado y despliegue de una aplicación inicial basada en microservicios (Usuarios, Productos y Carritos), a los cuales se accede a través de API REST, en un clúster de contenedores; la segunda, el despliegue de un clúster de procesamiento de datos distribuidos para analizar un dataset de facturas y generar reportes en un dashboard en el panel de administración de la aplicación.

Este proyecto implementa un sistema de gestión de tienda basado en una arquitectura de microservicios. Utiliza Docker y Docker Swarm para la orquestación, HAProxy para el balanceo de carga y PySpark para análisis de datos, con el objetivo de facilitar la administración de usuarios, productos y carritos de compra. Los resultados de los análisis se presentan en un dashboard para los administradores.



## Ambiente de trabajo

Primero debemos crear nuestro ambiente de trabajo consta de las siguientes herramientas: Vagrant + VirtualBox + Ubuntu. Veremos cómo manejar cada una de ellas.

OJO: Para no tener problemas con este ambiente de trabajo se recomienda desactivar las actualizaciones automáticas de Windows.

#### Instalación de VirtualBox.

Descargar e instalar la última versión de VirtualBox. La descarga la puede hacer desde el siguiente enlace: https://www.virtualbox.org/wiki/Downloads. La instalación no requiere ninguna configuración especial así que se puede hacer con todos los valores por defecto.

#### Instalación de Vagrant

Descargar e instalar la última versión de Vagrant, la descarga la puede hacer desde el siguiente enlace: https://releases.hashicorp.com/vagrant/

En la consola de Windows se puede verificar la versión de Vagrant que se instaló:

 ```
 vagrant version
 ```
 
Instalar el plugin vbguest para vagrant, con el fin de mantener las adiciones de los guest de VirtualBox actualizados. Estas adiciones (Guest Additions) son un paquete de software que forma parte de VirtualBox y añade funcionalidades a la instalación básica de VirtualBox que mejoran su rendimiento y consiguen un mejor nivel de integración entre la máquina huésped y la máquina anfitriona

```
plugin install vagrant-vbguest
```

#### Configuración y creación de las máquina virtuales

Muchos de los servicios que trabajaremos en este módulo requieren un servidor y un cliente, por tanto configuraremos 2 máquinas virtuales. El proceso de configuración es el siguiente: 

1. Cree un directorio y le da un nombre, en este ejemplo se llamará prueba.

 2. Ingrese al directorio y desde la consola de Windows ejecute el siguiente comando vagrant init con lo cual se crea un archivo de configuración llamado Vagrantfile

```
vagrant init
```

El archivo Vagrantfile contiene la información básica para la creación de las dos máquinas virtuales. El contenido de Vagrantfile debe ser el siguiente:

```
# -*- mode: ruby -*-

# vi: set ft=ruby :

  

Vagrant.configure("2") do |config|

  

if  Vagrant.has_plugin? "vagrant-vdguest"

config.vbguest.no_install =  true

config.vdguest.auto_update =  false

config.vdguest.no_remote =  true

  

end

config.vm.define :clienteUbuntu  do |clienteUbuntu|

clienteUbuntu.vm.box =  "bento/ubuntu-22.04"

clienteUbuntu.vm.network :private_network, ip:  "192.168.100.2"

clienteUbuntu.vm.hostname =  "clienteUbuntu"

end

  

config.vm.define :servidorUbuntu  do |servidorUbuntu|

servidorUbuntu.vm.box =  "bento/ubuntu-22.04"

servidorUbuntu.vm.network :private_network, ip:  "192.168.100.3"

servidorUbuntu.vm.hostname =  "servidorUbuntu"

servidorUbuntu.vm.provider "virtualbox"  do |v|

v.cpus =  3

v.memory =  2048

end

end

end
```

Este Vagrantfile define dos máquinas virtuales, una llamada servidorUbuntu con dirección ip 192.168.100.3 y la otra llamada clienteUbuntu con dirección ip 192.168.100.4, ambas instanciadas desde un box en el repositorio de bento llamado bento/Ubuntu-22.04. 

3. Crear las máquinas virtuales mediante el comando vagrant up desde la consola de Windows.
```
vagrant up 
```
Verificar el estado de las máquinas con el comando vagrant status 
```
vagrant status 
```
 4. Conexión a las máquinas virtuales A través del servicio de SSH establecer una conexión con las máquinas virtuales.
 
Se inicia ssh para la maquina *servidorUbuntu*
```
vagrant ssh servidorUbuntu 
```
Ahora para *clienteUbuntu*
```
vagrant ssh clienteUbuntu 
```

## Configuración e Instalación de Docker en las maquinas 

1. Verificar Docker en tu ecosistema LINUX
Verifica que Docker esté instalado:
```
docker --version
```

####  Si Docker no está instalado, sigue estos pasos:
Actualiza los paquetes existentes:
```
sudo apt-get update
```

Ejecuta el script de instalación de Docker:
```
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
```

Verifica que Docker esté funcionando correctamente:
```
docker run hello-world
```
Este comando descargará una imagen de prueba de Docker y ejecutará un contenedor que imprimirá un mensaje de éxito si todo está bien.

los anteriores pasos reazliazarlas en las dos maquinas *servidorUbuntu* y *clienteUbuntu* 

## Clonar Repositorio del Proyecto desde Git hub e Iniciar Swarm
2. Clonar el repositorio Clona este repositorio en la maquina *servidorUbuntu* :

```
git clone https://github.com/ntlg72/Proyecto---ReI.git
```
Navega al directorio del proyecto:
```
cd Proyecto---ReI/
```
Verifica que todo esté en orden:
```
ls
```
Deberías ver algo similar a esto:


>db  docker-compose.yml  haproxy  microservicios  python_scripts  README.md  swarm  web

3. Configuración del Docker Swarm 

Navega al directorio swarm:
```
cd swarm
```
Inicia Docker Swarm con el siguiente comando:
```
docker swarm init --advertise-addr 192.168.100.3
```
(Recuerda reemplazar 192.168.100.3 con la IP de tu máquina.)
Este comando generará un token similar al siguiente:
```
docker swarm join --token SWMTKN-1-<token> 192.168.100.3:2377
```
En el nodo Worker, pega el comando anterior. Esto mostrará algo similar a:

>This node joined a swarm as a worker.

4. Swarm y Despliegue

Etiqueta los nodos, Obtén el ID del nodo:
```
docker node ls
```
Etiqueta los nodos:
```
docker node update --label-add type=servidorUbuntu <node-ID>
docker node update --label-add type=clienteUbuntu <node-ID>
```
(Reemplaza <node-ID> con los IDs correspondientes.)
Despliega el stack en el nodo Master, desde Proyecto_swarm:
```
docker stack deploy -c docker-compose.yml MiPunto
```
Deberías ver algo similar a:

>Creating network MiPunto_default
Creating service MiPunto_python_scripts
Creating service MiPunto_web1
Creating service MiPunto_haproxy
..

Verifica que todo esté funcionando:
```
docker service ls
```
Las réplicas deberían verse de la siguiente manera:

>jgfoy9slnmi6   MiPunto_carritos         replicated   1/1    michelb16/proy-carritos:latest     :3003->3003/tcp
yia1tkwqqou9   MiPunto_carritos_BD      replicated   1/1    michelb16/proy-carritodb:latest    :32002->3306/tcp
...

5.  Acceso a la aplicación

Verifica la aplicación en tu navegador, Accede a la IP de tu servidor en el puerto 8080:

http://192.168.100.3:8080
(Cambia la IP si tu localhost es diferente.)

Crear cuentas:
Administrador: Crea una cuenta para administrador con las siguiente indicaciones 
usuario: admin
email: admin@admin.com
nombre: administrador
con la clave que prefieras (se sugiere 12345).
ciudad: Cali
direccion: 1234
documento de identidad: 123456 

Cliente: Crea una cuenta con los datos que desees. Tienes que rellenar cada campo del formualrio para crear un usuario cliente 


 ## Generar el analisis para crear las graficas que se visualizaran en el *Administrador* de la pagina web
 
6. Instalación de Apache Spark Instalar Java en Ubuntu 22.04 (Puede revisar https://linuxhint.com/installjava-ubuntu-22-04/)
```
sudo apt update
```
```
sudo apt install -y openjdk-18-jdk
```
```
cat <<EOF | sudo tee /etc/profile.d/jdk18.sh
export JAVA_HOME=/usr/lib/jvm/java-1.18.0-openjdk-amd64
export PATH=\$PATH:\$JAVA_HOME/bin
EOF
```
```
source /etc/profile.d/jdk18.sh
```
Una vez instalado Java puede verificar la instalación obteniendo la versión instalada:
```
java -version
```
#### Descargar y descomprimir Spark 

Verifique en https://dlcdn.apache.org/spark/ la última versión de spark y proceda a descargarla usando wget, es posible que deba usar una versión posterior, por lo cual deberá cambiar los números de la versión en el comando de descarga. 
```
mkdir labSpark
```
```
cd labSpark/ 
```
```
wget https://dlcdn.apache.org/spark/spark3.5.3/spark-3.5.3-bin-hadoop3.tgz 
```
```
tar -xvzf spark-3.5.1-bin-hadoop3.tgz
```

7. Corre el analisis 

Instala las siguientes librerias de Python
se deben instalar las librerias de python 
```
pip install pandas sqlalchemy mysql-connector-python
pip3 install plotly
pip install seaborn
```

Se debe descargar el conector de mysql en la maquina, para este archivo es para que el trabajo de analisis de saprk se pueda conectar a la base de datos de carrtos_BD y productos_BD. El siguiente comando se debe ejecutar en la maquina de *servidorUbuntu*  en la carpeta del proyecto
```
wget https://dev.mysql.com/get/Downloads/Connector-J/mysql-connector-j_9.1.0-1ubuntu24.10_all.deb
```
Despues de descargar el conector, se ejecuta el siguiente comando para 
se utiliza para instalar el paquete .deb
```
sudo dpkg -i mysql-connector-j_9.1.0-1ubuntu24.10_all.deb
```


 Primero debemos iniciar tanto el master y worker de spark con lo siguientes comandos 
 debos ir a la carpeta *sbin*
 ```
cd labSpark/spark-3.5.3-bin-hadoop3/sbin
```

Para inicar el master utlizamos :
```
./start-master.sh
```
Para el worker utilizamos el siguiente comando:
```
./start-worker.sh spark://192.168.100.3:7077
```


8. para inicar el trabajo de spark para genera las graficas, debes estar en el directerio del proyecto

```
cd Proyecto---ReI
```
Luego iniciamos el trabajo del spark
```
/root/labSpark/spark-3.5.3-bin-hadoop3/bin/spark-submit --jars /usr/share/java/mysql-connector-java-9.1.0.jar analisis.py
```

9. Visualización de de las graficas

Como ya creamos al usaurios *admin* entramos con su usario y clave en la pagina web, en la aparatado de ver estadisticas ahcemos clik sobre el icono, y este te mostarar las graficas que genro spark, ademas en podemos ahcer click en la ver por ciudad, te mostrara las ventas por ciudad. 




