# Guide: Hosting Renaissance SMS on your Local Network with Docker

This guide explains how to set up the School Management System on a dedicated computer and make it accessible to everyone in your school's network.

## Prerequisites

1.  **A Host Computer**: A dedicated desktop or server connected to the school's Wi-Fi or Ethernet.
2.  **Docker Desktop**: Installed and running on that computer. [Download here](https://www.docker.com/products/docker-desktop/).

---

## Step 1: Preparation (On your development computer)

1.  **Copy the files**: Copy your entire project folder (Renaissance SMS) to a USB drive or transfer it via the network to the "Host Computer".
2.  **Environment File**: Go to the `docker/` folder and copy `.env.docker` to the root directory, renaming it to just `.env`.
    > [!IMPORTANT]
    > Unlike your local development, the `.env` in Docker **must** use `DB_HOST=db` as configured in the `docker-compose.yml`.

---

## Step 2: Finding the Host IP (On the Host Computer)

To let other people access the system, you need to know the computer's network address.

1.  Open **PowerShell** or **Command Prompt**.
2.  Type: `ipconfig`
3.  Look for "IPv4 Address" under your active connection (Wi-Fi or Ethernet). It will look something like `192.168.1.15`.
4.  **Note this down.** This is how teachers will access the system.

---

## Step 3: Starting the System

On the host computer, navigate to the project folder in your terminal and run:

```powershell
# build the images and start the containers in the background
docker-compose up -d --build
```

**What this does:**
- Downloads the MySQL database (MariaDB).
- Builds your Laravel application.
- Compiles your CSS and JavaScript.
- Runs database migrations and seeds (setup).

---

## Step 4: Accessing the Portal

Once the command finishes, anyone on your school network can access the system!

-   **On the host computer**: Open `http://localhost:8080`
-   **On other computers/phones**: Open `http://192.168.1.15:8080` (Replace `192.168.1.15` with your actual IP from Step 2).

---

## Maintenance & Logs

If something isn't working, you can check the logs:
```powershell
docker-compose logs -f app
```

To stop the system:
```powershell
docker-compose down
```

> [!TIP]
> **Data Safety**: Your student and grade data is stored in a "Docker volume" named `db_data`. Even if you stop the containers, your data remains safe. To back up your data, you can export the MySQL database from the `renaissance_db` container using standard tools.
