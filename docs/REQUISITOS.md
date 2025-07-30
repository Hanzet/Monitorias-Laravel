# Requisitos del Sistema

## 🖥️ Requisitos Mínimos

### Sistema Operativo

-   **Windows 10/11** (con WSL2 recomendado)
-   **Ubuntu 20.04+** (WSL2 o nativo)
-   **macOS 10.15+**

### Hardware Mínimo

-   **RAM**: 8GB (recomendado 16GB)
-   **Almacenamiento**: 10GB de espacio libre
-   **CPU**: 2 núcleos (recomendado 4+)

## 🛠️ Software Requerido

### 1. Docker Desktop

-   **Versión**: 4.0+
-   **Descarga**: https://www.docker.com/products/docker-desktop
-   **Nota**: Para WSL2, instalar Docker Desktop for Windows

### 2. Git

-   **Versión**: 2.30+
-   **Descarga**: https://git-scm.com/downloads

### 3. Editor de Código (Opcional)

-   **VS Code**: https://code.visualstudio.com/
-   **PHPStorm**: https://www.jetbrains.com/phpstorm/
-   **Sublime Text**: https://www.sublimetext.com/

### 4. Cliente HTTP (Opcional)

-   **Postman**: https://www.postman.com/
-   **Insomnia**: https://insomnia.rest/
-   **Thunder Client** (extensión de VS Code)

## 🔧 Configuración de WSL2 (Windows)

### 1. Habilitar WSL2

```powershell
# Ejecutar como administrador
dism.exe /online /enable-feature /featurename:Microsoft-Windows-Subsystem-Linux /all /norestart
dism.exe /online /enable-feature /featurename:VirtualMachinePlatform /all /norestart
```

### 2. Reiniciar el sistema

### 3. Instalar WSL2

```powershell
wsl --install
```

### 4. Instalar Ubuntu desde Microsoft Store

-   Buscar "Ubuntu" en Microsoft Store
-   Instalar la versión más reciente

### 5. Configurar Docker Desktop para WSL2

-   Abrir Docker Desktop
-   Ir a Settings > Resources > WSL Integration
-   Habilitar integración con WSL2
-   Seleccionar la distribución Ubuntu

## 📦 Verificación de Instalación

### Verificar Docker

```bash
docker --version
docker-compose --version
```

### Verificar Git

```bash
git --version
```

### Verificar WSL2 (Windows)

```powershell
wsl --list --verbose
```

## 🚨 Notas Importantes

### Para WSL2

-   Docker Desktop debe estar ejecutándose
-   Los puertos se mapean automáticamente entre WSL2 y Windows
-   El rendimiento es mejor que WSL1

### Para Linux Nativo

-   Instalar Docker Engine directamente
-   No se requiere Docker Desktop

### Para macOS

-   Docker Desktop incluye todo lo necesario
-   No se requiere configuración adicional

## 🔍 Diagnóstico de Problemas

### Verificar que Docker funcione

```bash
docker run hello-world
```

### Verificar recursos disponibles

```bash
# En WSL2
free -h
df -h
```

### Verificar conectividad de red

```bash
ping google.com
```

## 📚 Recursos Adicionales

-   [Documentación oficial de Docker](https://docs.docker.com/)
-   [Documentación de WSL2](https://docs.microsoft.com/en-us/windows/wsl/)
-   [Laravel Documentation](https://laravel.com/docs)
-   [MySQL Documentation](https://dev.mysql.com/doc/)
