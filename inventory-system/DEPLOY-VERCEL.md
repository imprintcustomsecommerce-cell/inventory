# Deploying to Vercel with an Aiven MySQL database

This is the portfolio/demo deployment. The LAN setup in `../offline-lan` is
unaffected — nothing here changes how the app runs on your local network.

## How uploads work now

Vercel is serverless: each request gets a fresh, read-only filesystem, so a file
written to `storage/` during an upload is gone by the time the browser asks for
it back. Uploaded product images, inventory images and project proofs are
therefore stored **as bytes in the database** (the `media` table) and served
back through `/media/{type}/{id}`.

Images that already exist on disk from a LAN install keep working — the app
falls back to the old path when there is no database copy.

---

## 1. Create the Aiven database

In the Aiven console: **Create service → MySQL**. Once it is running, open
**Connection information** and keep the tab open — you need Host, Port, User,
Password, and Database name. These are the same values you'd put into MySQL
Workbench.

Download the **CA Certificate** from that panel and save it as:

```
inventory-system/storage/certs/aiven-ca.pem
```

Commit it. It is a public certificate, not a secret, and the deployed app needs
it in the bundle — Aiven refuses unencrypted connections.

## 2. Create the local production env file

```bash
cp .env.production.example .env.production
```

Fill in the Aiven values, then generate a key:

```bash
php artisan key:generate --env=production --show
```

Paste the output into `APP_KEY`. `.env.production` is gitignored.

## 3. Create the schema and demo data

> **The Aiven service hosts more than one app.** `defaultdb` holds the ecommerce
> site and `production` holds another project. This app uses its own `inventory`
> database — check `DB_DATABASE` before running anything.
>
> Never run `migrate:fresh` against this service. It **drops every table** in the
> selected database, and pointing it at the wrong one destroys another app's
> data. Plain `migrate` is safe: it only applies what is pending.

Vercel has no shell, so run migrations **from your machine against Aiven**:

```bash
php artisan migrate --seed --force --env=production
```

This creates the tables and seeds demo content: five staff logins (one per
role) and stock spread across all three warehouses.

Demo logins — all use the password `password`:

| Role      | Email                   |
|-----------|-------------------------|
| Admin     | admin@imprint.ph        |
| Store     | store@imprint.ph        |
| Inventory | warehouse@imprint.ph    |
| Materials | materials@imprint.ph    |
| Events    | events@imprint.ph       |

**Change these before sharing the link publicly** — anyone who reads this file
can sign in as an admin.

## 4. Configure Vercel

Import the repo at [vercel.com/new](https://vercel.com/new). Set the **Root
Directory** to `inventory-system`.

Under **Settings → Environment Variables**, add every variable from your
`.env.production`, including the `/tmp` cache paths — Laravel cannot boot on a
read-only filesystem without them.

Set `APP_URL` to the deployment URL once you know it, then redeploy so
generated links and image URLs point at the right host.

## 5. Deploy

Push to your default branch, or run `vercel --prod`.

---

## Things worth knowing

**Images count against the Aiven storage quota.** They live in the database, so
plan storage is consumed by media as well as rows, and database backups grow
with them. Fine for a demo; watch it if this ever carries real catalogue data.

**Uploads are capped at 4 MB** by the existing validation rules. Vercel also
caps a serverless request body at 4.5 MB, so the two line up — but don't raise
the app limit without checking the platform one.

**Queues don't run.** `QUEUE_CONNECTION=database` will queue jobs, but nothing
processes them without a worker, which Vercel can't host. Nothing in the current
feature set depends on it.

**Cold starts.** The first request after idle takes a few seconds while PHP
boots. Normal for serverless, and harmless for a portfolio piece.
