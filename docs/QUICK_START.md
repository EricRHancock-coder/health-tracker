# Health Tracker - Quick Start Guide

Get the application running in 5 minutes.

---

## Step 1: Start the Backend (Terminal 1)

```bash
cd /Users/fefe/Projects/health-tracker/backend
php -S localhost:8080 -t public/
```

You should see: `PHP Development Server started`

---

## Step 2: Start the Frontend (Terminal 2)

```bash
cd /Users/fefe/Projects/health-tracker/frontend
npm run dev
```

You should see: `Local: http://localhost:3000/`

---

## Step 3: Open in Browser

Navigate to: **http://localhost:3000**

---

## Step 4: Login

Use the default credentials:
- **Username**: `admin`
- **Password**: `admin123`

---

## Step 5: Explore

### Dashboard
- View all residents
- Search by name or room
- Click a resident to see details

### Resident Details
- View personal information
- See medications
- Click "Daily Record" to add tracking data

### Daily Record Form
- Select date
- Fill in bathing info
- Select cognitive state
- Select mood
- Add notes
- Save

### Admin Features
- User Management: Add/edit users (admin only)
- Audit Log: View all activity (admin only)

---

## Test Data Included

5 residents are pre-loaded:
1. John Doe (Room 101)
2. Jane Smith (Room 102)
3. Robert Johnson (Room 103)
4. Mary Williams (Room 104)
5. David Brown (Room 105)

---

## API Testing (Optional)

Test the API with curl:

```bash
# Login
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# Get residents (replace TOKEN with actual JWT)
curl http://localhost:8080/api/residents \
  -H "Authorization: Bearer TOKEN"
```

---

## File Structure Quick Reference

```
health-tracker/
├── backend/          # PHP API
│   └── public/       # Web root
├── frontend/         # React app
│   └── src/          # Source code
└── docs/            # Documentation
```

---

## Troubleshooting

### Port 8080 already in use
```bash
# Kill existing PHP server
pkill -f "php -S localhost:8080"
```

### Port 3000 already in use
```bash
# Use different port for frontend
npm run dev -- --port 3001
```

### Database locked
```bash
# Remove and re-seed database
cd /Users/fefe/Projects/health-tracker/backend
rm database/health_tracker.db database/.encryption_key
php seed.php
```

---

## Production Deployment

### Build Frontend
```bash
cd /Users/fefe/Projects/health-tracker/frontend
npm run build
```

### Deploy with Cloudflare Tunnel
```bash
# Install
brew install cloudflared

# Create tunnel
cloudflared tunnel create health-tracker

# Get credentials file location
cloudflared tunnel token <TUNNEL_ID>

# Run
cloudflared tunnel --url http://localhost:8080
```

---

## Need Help?

1. Check **IMPLEMENTATION_SUMMARY.md** for full documentation
2. Review **PROJECT.md** for architecture decisions
3. Look at code comments - they're detailed!

---

## Default Credentials

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Administrator |

**⚠️ Change before production use!**

---

**Ready to use!** 🎉
