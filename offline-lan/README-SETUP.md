# Offline LAN Setup — Imprint Inventory

Goal: the **store PC** and the **inventory PC** both use the **same live data**,
with **no internet needed**.

| PC          | IP address       | Role                                  |
|-------------|------------------|---------------------------------------|
| Inventory   | 192.168.150.80   | **Holds the database** (keep it ON)   |
| Store       | 192.168.150.106  | Uses the inventory PC's database      |

> The Inventory PC must be turned ON for the Store PC to work.
> That is the trade-off for both seeing the same live data.

---

## STEP 1 — Copy the whole `inventory-system` folder to BOTH PCs
Use a USB drive or a shared network folder. Copy the entire folder
(it already includes everything needed to run offline).

---

## STEP 2 — On the INVENTORY PC (192.168.150.80)

1. Start **XAMPP** → turn on **Apache** and **MySQL**.
2. Open `offline-lan\create-mysql-user.sql`, and run it in phpMyAdmin
   (XAMPP → MySQL → **Admin** → **SQL** tab → paste → **Go**).
3. Double-click `offline-lan\inventory-pc-open-firewall.ps1`
   → "Run with PowerShell" → click **Yes** if it asks for Administrator.
   (Follow the on-screen note about `bind-address` if shown.)
4. Double-click `offline-lan\start-server.bat`. Leave the window open.
5. This PC is ready. Open `http://localhost:8000` to use it here.

---

## STEP 3 — On the STORE PC (192.168.150.106)

1. Start **XAMPP** → turn on **Apache** (MySQL not required here).
2. Open the file `inventory-system\.env` in Notepad and change these lines:

   ```
   DB_HOST=192.168.150.80
   DB_PORT=3306
   DB_DATABASE=inventory_system
   DB_USERNAME=inventory
   DB_PASSWORD=imprint2026
   ```

   (If you chose a different password in Step 2, use that here.)
   Save and close.
3. Double-click `offline-lan\start-server.bat`. Leave the window open.
4. Open `http://localhost:8000` to use it here.

---

## Done!
Both PCs now show the same inventory, fully offline.

### Important
- **Do NOT** run `npm run dev` or `npm run build` on these PCs.
  The styling is already built into the copied folder.
- If the Store PC says it can't connect to the database,
  check that the Inventory PC is ON and its `start-server`/XAMPP MySQL is running.

### Troubleshooting a database connection error on the Store PC
- Ping the inventory PC first: open Command Prompt and run `ping 192.168.150.80`
- Make sure Step 2 (SQL user + firewall) was completed on the inventory PC.
