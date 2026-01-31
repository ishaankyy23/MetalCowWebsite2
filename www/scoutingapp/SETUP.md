# Metal Cow Scouting App - Complete Setup Guide

## Overview
The Metal Cow Scouting App is fully implemented in the `scoutingapp` folder with all required files and functionality ready to deploy.

## Folder Structure
```
scoutingapp/
├── index.html              (Main entry point)
├── scout-login.html        (Legacy page with main site formatting)
├── SETUP.md                (This file)
├── css/
│   └── styles.css         (Dark theme with neon green accents)
├── js/
│   ├── config.example.js   (Template for API credentials)
│   ├── config.js           (Your actual credentials - DO NOT COMMIT)
│   └── script.js           (Supabase integration + all functions)
└── html/
    ├── login.html          (Scout access/login page)
    ├── pit.html            (Pit scouting form)
    ├── field.html          (Field scouting form)
    ├── store.html          (Data viewer with table)
    └── stats.html          (Analytics dashboard with charts)
```

---

## Quick Setup (3 Steps)

### Step 1: Create Supabase Project
1. Go to https://supabase.com and sign up (free tier works great)
2. Create a new project
3. Go to **Settings → API** and note your:
   - **Project URL** (looks like: `https://xxxxx.supabase.co`)
   - **Anon Key** (long string starting with `eyJ...`)

### Step 2: Create Database Table
In Supabase, go to **SQL Editor** and paste this:

```sql
CREATE TABLE scouting_entries (
    id BIGSERIAL PRIMARY KEY,
    created_at TIMESTAMP DEFAULT NOW(),
    comp_name TEXT,
    scout_name TEXT,
    form_type TEXT,
    team_number INT,
    match_number INT,
    details JSONB
);
```

Click **Run** to create the table.

### Step 3: Configure API Credentials (Secure Method)

#### Option A: Copy Template File (Recommended)
1. Navigate to `js/` folder
2. Copy `config.example.js` and rename to `config.js`
3. Open `js/config.js` and fill in your credentials:
   ```javascript
   const SUPABASE_CONFIG = {
       SB_URL: 'https://your-project.supabase.co',
       SB_KEY: 'your-anon-key-here'
   };
   ```
4. **Save and you're done!** (config.js is already in .gitignore)

#### Option B: Manual Setup
1. Open `js/config.js` (or create if missing)
2. Add this content with YOUR credentials:
   ```javascript
   const SUPABASE_CONFIG = {
       SB_URL: 'https://YOUR-PROJECT.supabase.co',
       SB_KEY: 'YOUR-ANON-KEY'
   };
   ```

---

## Important: API Key Security

✅ **config.js is PROTECTED by .gitignore**
- Your actual API keys in `config.js` will NEVER be committed to Git
- Each team member gets their own `config.js` (not shared)
- `config.example.js` is the template (safe to share)

This prevents accidental exposure of sensitive credentials!

---

## Test the App

1. Open `index.html` in your browser
2. Click "Start Scouting"
3. Enter any scout name
4. Select a competition
5. Click "Launch System"
6. Fill out the Pit scouting form
7. Click "Submit Pit Data"
8. You should see a success message
9. Go to **Data** tab - your entry should appear!

---

## Features Implemented

### ✅ Multi-page Forms
- **Pit Scouting** - Drivetrain type, autonomous capabilities, endgame abilities
- **Field Scouting** - Live match performance, climb results, notes

### ✅ Real-time Cloud Sync
- All data automatically synced to Supabase
- Anonymous user authentication
- Persistent cloud storage

### ✅ Custom Modal System
- Professional alert/confirm/prompt dialogs
- Styled dark theme with neon accents
- Replaces browser defaults

### ✅ Data Management
- **Data Viewer** - See all scouting entries in a table
- **Admin Delete** - Clear all data with 2-step confirmation
- Sortable entries by type, team, competition

### ✅ Analytics Dashboard
- **Fuel Per Second Charts** - Team performance comparison
- **Climb Success Rate** - Visual pie chart
- **Team Metrics** - Automated analytics from scouting data

### ✅ Mobile Responsive Design
- Dark theme with neon green (#63AD3F) accents
- Adapts perfectly to all screen sizes
- Touch-friendly form inputs

---

## Navigation

All pages (except login) include this navigation bar:
- **Login** - Return to scout access page
- **Pit** - Go to pit scouting form
- **Field** - Go to field scouting form
- **Data** - View all cloud data
- **Analytics** - See performance charts

---

## Customization

### Change Primary Color
Open `css/styles.css` and replace all instances of `#63AD3F` with your brand color:
```css
/* Replace #63AD3F with your color */
color: #63AD3F;
```

### Add More Competitions
Edit `html/login.html` and add more options:
```html
<select id="compName">
    <option value="2026ilpe">Central Illinois (Peoria)</option>
    <option value="2026mokc">Greater Kansas City</option>
    <option value="2026your">Your Competition</option>  <!-- Add here -->
</select>
```

### Modify Forms
Edit `html/pit.html` or `html/field.html` to add/remove fields:
- Add input fields inside the form
- They automatically sync to Supabase
- Data stored in the `details` JSONB field

### Change Team Information
Add more fields to the pit scouting form:
```html
<div class="question">
    <label for="teamName">Team Name</label>
    <input type="text" id="teamName" name="teamName" placeholder="Team name">
</div>
```

---

## Linking from Main Website

Add this to your main Metal Cow website navigation:

```html
<a href="scoutingapp/scouting.html">Scouting</a>
```

Or if you need absolute path:
```html
<a href="/scoutingapp/scouting.html">Scouting</a>
```

---

## Technical Stack

| Component | Technology |
|-----------|------------|
| Frontend | HTML/CSS/JavaScript (Vanilla) |
| Backend | Supabase (PostgreSQL + Auth) |
| Database | PostgreSQL with JSONB |
| Charts | Chart.js |
| Authentication | Supabase Anonymous Auth |
| API | Supabase REST API |

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| **Data not saving** | Check Supabase credentials in config.js; verify table exists in Supabase |
| **Charts not showing** | Ensure Chart.js CDN loads; check browser console for errors; verify data exists |
| **Modal not appearing** | Verify styles.css is linked; check browser DevTools → Elements |
| **404 on navigation links** | Check relative paths in HTML nav links; ensure files exist |
| **"config is not defined" error** | Make sure config.js is loaded before script.js; check script tags order |
| **Supabase connection error** | Verify SB_URL and SB_KEY are correct; check CORS in Supabase settings |
| **Data won't load on Data tab** | Verify Supabase table was created; check network tab in DevTools |

---

## For Your Team

### First Time Setup (Everyone Does This):
1. Clone/pull the repository
2. Copy `js/config.example.js` to `js/config.js`
3. Add your Supabase credentials to `config.js`
4. Test by opening `index.html`
5. **Never commit config.js** (it's in .gitignore)

### Updating Forms:
- Edit `html/pit.html` or `html/field.html`
- Add new fields to the form
- Commit and push
- Everyone automatically gets updates

### Checking Cloud Data:
- All team members see the same cloud data
- Go to Data tab to see all entries
- Check Analytics for team insights

---

## Security Notes

- ✅ API keys stored safely in config.js (not committed)
- ✅ Each developer gets their own config.js
- ✅ Anonymous authentication (no passwords needed)
- ✅ All data in Supabase is encrypted at rest
- ✅ HTTPS only - data encrypted in transit

---

## File Purpose Reference

| File | Purpose |
|------|---------|
| `index.html` | Welcome page, link to scouting app |
| `scout-login.html` | Legacy page with main site formatting |
| `html/login.html` | Scout login - enter name and competition |
| `html/pit.html` | Robot capabilities form (pit scouting) |
| `html/field.html` | Match performance form (field scouting) |
| `html/store.html` | View all scouted data, admin delete |
| `html/stats.html` | Analytics charts and team metrics |
| `js/config.example.js` | Template for API credentials |
| `js/config.js` | Your actual credentials (DO NOT COMMIT) |
| `js/script.js` | All app logic and Supabase integration |
| `css/styles.css` | Dark theme styling |
| `.gitignore` | Prevents config.js from being committed |

---

## Support & Resources

- **Supabase Docs:** https://supabase.com/docs
- **Chart.js Docs:** https://www.chartjs.org/docs/latest/
- **PostgreSQL JSON:** https://www.postgresql.org/docs/current/datatype-json.html

---

## Next Steps

1. ✅ Create Supabase project
2. ✅ Create database table
3. ✅ Set up config.js with credentials
4. ✅ Test the app with sample data
5. ✅ Link from main website
6. ✅ Train team on using pit/field forms
7. ✅ Monitor analytics and collected data

**You're all set! Happy scouting!** 🚀
