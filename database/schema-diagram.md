# 7 Ensemble - Database Schema Diagram

## 📊 Overview

This document describes the complete database architecture for the 7 Ensemble platform, including all tables, relationships, and data flow.

---

## 🗄️ Database Tables

### 1. users
**Purpose:** Store user accounts and profiles

**Key Fields:**
- `id` (PK)
- `name`, `email`, `password`
- `constellation_id` (FK → constellations)
- `current_tour` (1-7)
- `referral_code` (unique)
- `referred_by_id` (FK → users, self-referential)
- `total_paid`, `total_received`, `total_earnings`
- `has_paid_initial`, `payment_verified`
- `status` (active, suspended, banned, pending_verification)
- `role` (user, admin, moderator)

**Relationships:**
- `belongsTo` Constellation
- `hasMany` Tours
- `hasMany` Transactions
- `hasMany` Payouts
- `hasMany` PaymentMethods
- `hasOne` UserSetting
- `belongsTo` User (referrer)
- `hasMany` Users (referrals)
- `hasMany` Referrals (as referrer)
- `hasMany` Referrals (as referee)

---

### 2. constellations
**Purpose:** Group users into financial support groups

**Key Fields:**
- `id` (PK)
- `type` (triangulum, pleiades)
- `code` (unique identifier)
- `alcyone_id` (FK → users) - center person
- `current_tour` (1-7)
- `max_members` (3 or 7)
- `current_members` (counter)
- `status` (forming, active, completed, disbanded, frozen)
- `total_collected`, `total_distributed`

**Relationships:**
- `belongsTo` User (alcyone)
- `hasMany` Users
- `hasMany` ConstellationMembers
- `hasMany` Tours
- `hasMany` Transactions

---

### 3. constellation_members
**Purpose:** Track membership details within constellations

**Key Fields:**
- `id` (PK)
- `constellation_id` (FK → constellations)
- `user_id` (FK → users)
- `position` (1-7)
- `role` (alcyone, member)
- `status` (active, waiting, inactive, left)
- `total_contributed`, `total_received`
- `tours_completed`, `current_tour`
- `joined_at`, `left_at`

**Relationships:**
- `belongsTo` Constellation
- `belongsTo` User

---

### 4. tours
**Purpose:** Track individual tour progress for each user

**Key Fields:**
- `id` (PK)
- `user_id` (FK → users)
- `constellation_id` (FK → constellations)
- `tour_requirement_id` (FK → tour_requirements)
- `tour_number` (1-7)
- `constellation_type` (triangulum, pleiades)
- `amount_to_pay`, `amount_paid`
- `amount_to_receive`, `amount_received`
- `amount_kept` (net earnings)
- `payment_status` (pending, partial, paid, overdue, cancelled)
- `receipt_status` (waiting, partial, received, completed)
- `status` (pending, active, in_progress, completed, failed)
- `alcyone_id` (FK → users)
- `is_alcyone` (boolean)
- `members_paid` / `required_members` (progress tracking)

**Relationships:**
- `belongsTo` User
- `belongsTo` Constellation
- `belongsTo` TourRequirement
- `belongsTo` User (alcyone)
- `hasMany` Transactions
- `hasMany` Payouts

**Unique Constraint:** (`user_id`, `constellation_id`, `tour_number`)

---

### 5. tour_requirements
**Purpose:** Template for tour configurations (amounts, requirements)

**Key Fields:**
- `id` (PK)
- `tour_number` (1-7)
- `option_type` (triangulum, pleiades)
- `amount_to_pay`, `amount_to_receive`, `amount_to_keep`
- `required_members` (3 or 7)
- `is_active` (boolean)

**Relationships:**
- `hasMany` Tours

**Data:**
- 7 requirements for Triangulum
- 7 requirements for Pléiades
- Total: 14 records

---

### 6. transactions
**Purpose:** Record all financial transactions

**Key Fields:**
- `id` (PK)
- `transaction_id` (unique, e.g., TXN-20240109-XXXX)
- `user_id` (FK → users)
- `tour_id` (FK → tours, nullable)
- `constellation_id` (FK → constellations, nullable)
- `payment_method_id` (FK → payment_methods, nullable)
- `type` (payment, payout, transfer, referral_bonus, admin_fee, refund, initial_payment)
- `direction` (credit, debit)
- `amount`, `currency` (EUR), `fee`, `net_amount`
- `related_user_id` (FK → users) - for P2P transfers
- `payment_gateway` (stripe, paypal, bank, mobile_money)
- `gateway_transaction_id`, `gateway_reference`
- `status` (pending, processing, completed, failed, refunded, cancelled, on_hold)
- `requires_verification`, `is_verified`
- `receipt_url`, `proof_of_payment_url`

**Relationships:**
- `belongsTo` User
- `belongsTo` Tour
- `belongsTo` Constellation
- `belongsTo` PaymentMethod
- `belongsTo` User (related_user)
- `belongsTo` User (verifier)

---

### 7. payouts
**Purpose:** Track withdrawal/payout requests from users

**Key Fields:**
- `id` (PK)
- `user_id` (FK → users)
- `tour_id` (FK → tours, nullable)
- `transaction_id` (FK → transactions, nullable)
- `amount`, `currency`, `fee`, `net_amount`
- `payment_method_type` (bank_transfer, paypal, mobile_money, crypto)
- `bank_account`, `iban`, `account_holder_name`
- `paypal_email`, `mobile_money_number`, `crypto_wallet_address`
- `status` (pending, processing, completed, failed, rejected, cancelled)
- `reference_number` (unique)
- `gateway_reference`, `proof_url`

**Relationships:**
- `belongsTo` User
- `belongsTo` Tour
- `belongsTo` Transaction

---

### 8. payment_methods
**Purpose:** Store user payment methods

**Key Fields:**
- `id` (PK)
- `user_id` (FK → users)
- `type` (card, bank_transfer, paypal, mobile_money, crypto)
- `provider` (stripe, paypal, mpesa, etc.)
- `provider_payment_method_id` (external ID)
- **Card:** `card_brand`, `card_last_four`, `card_exp_month`, `card_exp_year`
- **Bank:** `bank_name`, `account_holder_name`, `iban`, `bic_swift`
- **Mobile:** `mobile_money_provider`, `mobile_number`
- **Crypto:** `crypto_currency`, `crypto_wallet_address`
- `encrypted_details` (sensitive data)
- `is_default`, `is_verified`, `status`

**Relationships:**
- `belongsTo` User
- `hasMany` Transactions

---

### 9. referrals
**Purpose:** Track referral relationships and bonuses

**Key Fields:**
- `id` (PK)
- `referrer_id` (FK → users) - person who referred
- `referee_id` (FK → users) - person who was referred
- `level` (1, 2, 3) - referral depth
- `bonus_amount`, `bonus_percentage`, `total_bonus_earned`
- `status` (pending, qualified, paid, expired, cancelled)
- `qualification_type` (registration, first_payment, constellation_joined, tour_completed, recurring)
- `is_qualified`, `qualified_at`
- `is_paid`, `paid_at`
- `transaction_id` (FK → transactions, nullable)
- **Progress tracking:**
  - `referee_registered`, `referee_paid_initial`
  - `referee_joined_constellation`, `referee_tours_completed`
- `referred_at`, `expires_at`
- `days_to_convert` (time to qualification)

**Relationships:**
- `belongsTo` User (referrer)
- `belongsTo` User (referee)
- `belongsTo` Transaction

**Unique Constraint:** (`referrer_id`, `referee_id`)

---

### 10. user_settings
**Purpose:** Store user preferences and settings

**Key Fields:**
- `id` (PK)
- `user_id` (FK → users)
- **Localization:** `language`, `timezone`, `currency`
- **Notifications:** `email_notifications`, `push_notifications`, `sms_notifications`
  - `notify_on_payment_received`, `notify_on_payment_sent`
  - `notify_on_tour_completed`, `notify_on_constellation_update`
  - `notify_on_referral_bonus`
- **Display:** `theme`, `date_format`, `time_format`, `display_currency_symbol`
- **Privacy:** `privacy_show_earnings`, `privacy_show_constellation`, `privacy_show_referrals`
- **Security:** `two_factor_enabled`, `login_alerts`

**Relationships:**
- `belongsTo` User

---

## 📊 Entity Relationship Diagram

```
┌─────────────────┐
│     USERS       │◄────┐
│  (Central Hub)  │     │
└────────┬────────┘     │
         │              │
         │ 1:N          │ 1:1
         ▼              │
┌──────────────────────┐│
│ USER_SETTINGS        ││
└──────────────────────┘│
                        │
         ┌──────────────┘
         │ 1:N
         ▼
┌──────────────────────┐
│ PAYMENT_METHODS      │
└──────────────────────┘

┌─────────────────┐       ┌──────────────────────┐
│  CONSTELLATIONS │◄─────►│ CONSTELLATION_MEMBERS│
│                 │  1:N  │                      │
└────────┬────────┘       └──────────┬───────────┘
         │                           │
         │ 1:N                       │ N:1
         ▼                           ▼
    ┌─────────┐              ┌─────────────┐
    │  TOURS  │──────────────│    USERS    │
    └────┬────┘     N:1      └──────┬──────┘
         │                          │
         │ N:1                      │ 1:N
         ▼                          ▼
┌──────────────────┐       ┌────────────────┐
│ TOUR_REQUIREMENTS│       │  TRANSACTIONS  │
│   (Templates)    │       └────────┬───────┘
└──────────────────┘                │
                                    │ N:1
                                    ▼
                           ┌─────────────────┐
                           │ PAYMENT_METHODS │
                           └─────────────────┘

┌─────────────────┐       ┌─────────────────┐
│    REFERRALS    │──────►│  TRANSACTIONS   │
│                 │  1:1  │    (Bonus)      │
└─────┬───────────┘       └─────────────────┘
      │
      │ N:1
      ▼
┌─────────────────┐
│      USERS      │
│  (Referrer &    │
│   Referee)      │
└─────────────────┘

┌─────────────────┐       ┌─────────────────┐
│     PAYOUTS     │──────►│  TRANSACTIONS   │
│                 │  1:1  │                 │
└─────┬───────────┘       └─────────────────┘
      │
      │ N:1
      ▼
┌─────────────────┐
│      USERS      │
└─────────────────┘
```

---

## 🔄 Key Data Flows

### 1. User Registration Flow
```
1. User registers
2. User record created in `users` table
3. UserSetting record auto-created
4. Referral code auto-generated
5. If referred, Referral record created
```

### 2. Constellation Assignment Flow
```
1. User completes initial payment (21€)
2. System finds/creates appropriate constellation
3. ConstellationMember record created
4. User's constellation_id updated
5. If constellation full → status becomes 'active'
```

### 3. Tour Progression Flow
```
1. Tour record created for user
2. User pays amount_to_pay
3. Transaction record created (type: payment)
4. Payment goes to Alcyone
5. When all members pay:
   - Alcyone receives full amount
   - Tour marked as completed
   - Next tour starts
```

### 4. Payment Flow
```
1. User initiates payment
2. Transaction created (status: pending)
3. Payment gateway processes
4. Transaction updated (status: completed)
5. User's total_paid incremented
6. Tour's amount_paid incremented
7. Alcyone's total_received incremented
```

### 5. Referral Flow
```
1. User A shares referral code
2. User B registers with code
3. Referral record created (status: pending)
4. When User B pays initial:
   - Referral becomes 'qualified'
5. Bonus paid to User A
6. Transaction created (type: referral_bonus)
7. Referral marked as 'paid'
```

---

## 📈 Database Statistics (Sample Data)

After seeding:
- **Users:** 101 (100 users + 1 admin)
- **Constellations:** 20 (12 Triangulum + 8 Pléiades)
- **Constellation Members:** ~96 (all active users)
- **Tours:** ~400+ (multiple tours per user)
- **Transactions:** ~200+
- **Payouts:** ~30
- **Referrals:** ~40
- **Payment Methods:** ~70
- **Tour Requirements:** 14 (7 × 2 types)

---

## 🔑 Important Indexes

### Performance Indexes
- `users.constellation_id`
- `users.referral_code` (unique)
- `users.email` (unique)
- `transactions.user_id`
- `transactions.status`
- `transactions.type`
- `tours.user_id + tour_number`
- `constellations.status`
- `referrals.referrer_id`
- `referrals.referee_id`

### Unique Constraints
- `users.email`
- `users.referral_code`
- `constellations.code`
- `transactions.transaction_id`
- `tours (user_id, constellation_id, tour_number)`
- `referrals (referrer_id, referee_id)`

---

## 🔒 Data Integrity Rules

1. **Users must have valid email** (unique, verified)
2. **Constellation type determines max_members** (3 or 7)
3. **Tour requirements match constellation type**
4. **Transaction amounts must be positive**
5. **Referrals cannot be self-referential**
6. **Payouts require verified payment methods**
7. **Tours follow sequential order** (1 → 7)
8. **Alcyone receives from all constellation members**

---

## 💾 Soft Deletes

The following tables use soft deletes (`deleted_at` column):
- `users`
- `constellations`
- `constellation_members`
- `tours`
- `transactions`
- `payouts`
- `referrals`
- `payment_methods`

This allows for data recovery and audit trails.

---

## 🎯 Cascade Rules

**ON DELETE CASCADE:**
- `transactions.user_id` → deletes all user transactions
- `tours.user_id` → deletes all user tours
- `referrals.referrer_id` / `referee_id` → deletes referral records

**ON DELETE SET NULL:**
- `users.constellation_id` → removes constellation association
- `transactions.tour_id` → keeps transaction but removes tour link

---

## 📝 JSON Columns

Several tables use JSON columns for flexible data storage:

- `users.preferences` - notification settings, theme, language
- `users.kyc_data` - KYC verification documents/data
- `constellations.metadata` - additional constellation data
- `tours.metadata` - tour-specific notes and data
- `transactions.metadata` - additional transaction details
- `user_settings.metadata` - custom user preferences

---

## 🚀 Query Optimization Tips

1. **Use eager loading** for relationships:
   ```php
   User::with(['constellation', 'tours', 'transactions'])->get();
   ```

2. **Use query scopes** for common filters:
   ```php
   User::active()->inConstellation()->get();
   Transaction::completed()->payments()->get();
   ```

3. **Index composite queries**:
   - (`user_id`, `status`)
   - (`constellation_id`, `tour_number`)
   - (`type`, `created_at`)

4. **Paginate large datasets**:
   ```php
   Transaction::paginate(50);
   ```

---

**This schema supports the complete 7 Ensemble platform with scalability and data integrity! 🌟**
