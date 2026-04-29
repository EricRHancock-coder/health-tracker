# Health Tracker - UI/UX Mockups

> Visual design reference for the Alzheimer's Care Application

---

## Login Screen

**Design:** Centered minimal card layout (full-screen background)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                             │
│                              🏥 Health Tracker                               │
│                              Alzheimer's Care                               │
│                                                                             │
│              ┌─────────────────┐                                            │
│              │  📧 Email       │                                            │
│              └─────────────────┘                                            │
│                                                                             │
│              ┌─────────────────┐                                            │
│              │  🔒 Password    │                                            │
│              └─────────────────┘                                            │
│                                                                             │
│              ┌─────────────────┐                                            │
│              │    SIGN IN      │                                            │
│              └─────────────────┘                                            │
│                                                                             │
│              Login incorrect                                                │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

**Specifications:**
- Error message: "Login incorrect" (vague, no credential enumeration)
- No self-registration or password recovery (admin-managed accounts)
- Redirect to Dashboard on successful login

---

## Navigation Structure

| Role | Menu Items |
|------|------------|
| **Administrator** | Dashboard, Users, Residents, Reports, Medications, Audit |
| **Caregiver** (Read/Write) | Dashboard, Residents, Reports |
| **Read-Only** | Dashboard, Residents, Reports, View Records |

---

## Dashboard Layouts

### Administrator Dashboard

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    MOST ACTIVE RESIDENTS                                     │
├─────────────────┬─────────────┬─────────────────────────────────────────────┤
│ John Miller     │ 12 records  │ [👁️ View Record]                            │
│ Sarah Chen      │ 10 records  │ [👁️ View Record]                            │
│ Robert Davis    │ 8 records   │ [👁️ View Record]                            │
│ Maria Garcia    │ 7 records   │ [👁️ View Record]                            │
│ James Wilson    │ 6 records   │ [👁️ View Record]                            │
└─────────────────┴─────────────┴─────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                    LOCKED RECORDS                                            │
├──────────┬──────────────┬───────────┬─────────────┬────────────────┬─────────┤
│ Type     │ Resident     │ Locked By │ Locked At   │ Expires        │ Release │
├──────────┼──────────────┼───────────┼─────────────┼────────────────┼─────────┤
│ Daily    │ John Miller  │ jsmith    │ 09:45 AM    │ 10:45 AM       │ 🔓      │
│ Med      │ Sarah Chen   │ mwilson   │ 10:15 AM    │ 11:15 AM       │ 🔓      │
│ Daily    │ Robert Davis │ akim      │ 10:30 AM    │ 11:30 AM       │ 🔓      │
└──────────┴──────────────┴───────────┴─────────────┴────────────────┴─────────┘
│                                                    [🔓 Release All Locks]     │
└─────────────────────────────────────────────────────────────────────────────┘

┌────────────────────────────┐  ┌───────────────────────────────────────────────┐
│       QUICK ACTIONS        │  │         RECENT AUDIT ACTIVITY                 │
│                            │  │                                               │
│  [➕ New Resident]          │  │  09:23  jsmith updated DailyRecord #145       │
│  [➕ New User]              │  │  09:15  mwilson created Medication #89        │
│  [➕ New Medication]        │  │  08:47  akim marked med TAKEN (R-301)         │
│                            │  │  08:30  system auto-released lock #12        │
└────────────────────────────┘  └───────────────────────────────────────────────┘
```

### Caregiver (Read/Write) Dashboard

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    MOST ACTIVE RESIDENTS                                     │
├─────────────────┬─────────────┬─────────────────────────────────────────────┤
│ John Miller     │ 12 records  │ [👁️ View Record]                            │
│ Sarah Chen      │ 10 records  │ [👁️ View Record]                            │
│ Robert Davis    │ 8 records   │ [👁️ View Record]                            │
│ Maria Garcia    │ 7 records   │ [👁️ View Record]                            │
│ James Wilson    │ 6 records   │ [👁️ View Record]                            │
└─────────────────┴─────────────┴─────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                    LOCKED RECORDS                                            │
├──────────┬──────────────┬───────────┬─────────────┬────────────────┬─────────┤
│ Type     │ Resident     │ Locked By │ Locked At   │ Expires        │ Release │
├──────────┼──────────────┼───────────┼─────────────┼────────────────┼─────────┤
│ Daily    │ John Miller  │ jsmith    │ 09:45 AM    │ 10:45 AM       │ 🔓      │
│ Med      │ Sarah Chen   │ mwilson   │ 10:15 AM    │ 11:15 AM       │ 🔓      │
│ Daily    │ Robert Davis │ akim      │ 10:30 AM    │ 11:30 AM       │ 🔓      │
└──────────┴──────────────┴───────────┴─────────────┴────────────────┴─────────┘
│                                                    [🔓 Release All Locks]     │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                    RECENT ACTIVITY                                           │
├──────────────────┬─────────────┬────────────────────────────────────────────┤
│ Record #145      │ John Miller │ Updated: 09:23 AM                          │
│ Med #203         │ Sarah Chen  │ Administered: 09:15 AM                     │
└──────────────────┴─────────────┴────────────────────────────────────────────┘
```

### Read-Only Dashboard

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    MOST ACTIVE RESIDENTS                                     │
├─────────────────┬─────────────┬─────────────────────────────────────────────┤
│ John Miller     │ 12 records  │ [👁️ View Record]                            │
│ Sarah Chen      │ 10 records  │ [👁️ View Record]                            │
│ Robert Davis    │ 8 records   │ [👁️ View Record]                            │
│ Maria Garcia    │ 7 records   │ [👁️ View Record]                            │
│ James Wilson    │ 6 records   │ [👁️ View Record]                            │
└─────────────────┴─────────────┴─────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                    LOCKED RECORDS (View Only)                                │
├──────────┬──────────────┬───────────┬─────────────┬────────────────────────────┤
│ Type     │ Resident     │ Locked By │ Locked At   │ Expires                  │
├──────────┼──────────────┼───────────┼─────────────┼────────────────────────────┤
│ Daily    │ John Miller  │ jsmith    │ 09:45 AM    │ 10:45 AM                 │
│ Med      │ Sarah Chen   │ mwilson   │ 10:15 AM    │ 11:15 AM                 │
│ Daily    │ Robert Davis │ akim      │ 10:30 AM    │ 11:30 AM                 │
└──────────┴──────────────┴───────────┴─────────────┴────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                    RECENT DAILY RECORDS                                      │
├─────────────────┬─────────────┬───────────────────────────────────────────────┤
│ Resident        │ Date        │ Status                                        │
├─────────────────┼─────────────┼───────────────────────────────────────────────┤
│ John Miller     │ 2026-04-23  │ ✅ Recorded - Bath: Yes                     │
│ Sarah Chen      │ 2026-04-23  │ ✅ Recorded - Bath: Yes                     │
│ Robert Davis    │ 2026-04-23  │ ⏳ Pending                                  │
│ Maria Garcia    │ 2026-04-23  │ ✅ Recorded - Bath: No (reason:...)        │
└─────────────────┴─────────────┴───────────────────────────────────────────────┘
```

---

## Dashboard Notes

- All users see **Most Active Residents** at the top (5 most active)
- **Locked Records** visible to all roles with columns: Type, Resident, Locked By, Locked At, Expires
- Only **Admin** and **Caregiver** can release locks (per-record and Release All buttons)
- **Read-Only** users see locked records but cannot release them
- No facility overview or system status sections
- Daily/Medication records accessed via Resident detail pages only

---

## Reports Portal (Placeholder)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                             │
│                         📊 Reports Portal                                    │
│                                                                             │
│              ┌─────────────────────────────────────────┐                     │
│              │                                         │                     │
│              │        📈 📊 📉                        │                     │
│              │                                         │                     │
│              │     Reporting features                  │                     │
│              │     will be added in a                  │                     │
│              │     future release                      │                     │
│              │                                         │                     │
│              │     Coming Soon                         │                     │
│              │                                         │                     │
│              └─────────────────────────────────────────┘                     │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```
