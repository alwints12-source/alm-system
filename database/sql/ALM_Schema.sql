-- ============================================================================
-- DOST-PES ASSET LIFECYCLE MANAGEMENT SYSTEM
-- PostgreSQL Database Schema — Phase 2
-- ============================================================================

-- ============================================================================
-- ENUM TYPES
-- ============================================================================

CREATE TYPE user_role AS ENUM (
    'administrative_admin',
    'technical_admin',
    'asset_holder',
    'technician'
);

CREATE TYPE asset_status AS ENUM (
    'active',
    'in_maintenance',
    'pending_acknowledgement',
    'in_storage',
    'disposed',
    'lost'
);

CREATE TYPE asset_condition AS ENUM (
    'excellent',
    'good',
    'fair',
    'poor',
    'unserviceable'
);

CREATE TYPE work_order_status AS ENUM (
    'pending',
    'assigned',
    'in_progress',
    'on_hold',
    'completed',
    'overdue',
    'cancelled'
);

CREATE TYPE maintenance_type AS ENUM (
    'preventive',
    'corrective',
    'predictive',
    'emergency'
);

CREATE TYPE priority_level AS ENUM (
    'critical',
    'high',
    'medium',
    'low'
);

CREATE TYPE schedule_frequency AS ENUM (
    'daily',
    'weekly',
    'monthly',
    'quarterly',
    'semi_annual',
    'annual'
);

CREATE TYPE assignment_status AS ENUM (
    'pending_acknowledgement',
    'acknowledged',
    'returned',
    'transferred'
);

CREATE TYPE notification_channel AS ENUM (
    'in_app',
    'email',
    'both'
);

CREATE TYPE mediable_type AS ENUM (
    'asset',
    'work_order',
    'maintenance_history'
);


-- ============================================================================
-- TABLE 1: users
-- ============================================================================

CREATE TABLE users (
    id                  BIGSERIAL PRIMARY KEY,
    employee_id         VARCHAR(50) UNIQUE NOT NULL,
    first_name          VARCHAR(100) NOT NULL,
    last_name           VARCHAR(100) NOT NULL,
    email               VARCHAR(255) UNIQUE NOT NULL,
    password_hash       VARCHAR(255) NOT NULL,
    role                user_role NOT NULL,
    department          VARCHAR(150),
    position            VARCHAR(150),
    contact_number      VARCHAR(30),
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,

    -- MFA
    mfa_enabled         BOOLEAN NOT NULL DEFAULT FALSE,
    mfa_secret          VARCHAR(255),
    mfa_confirmed_at    TIMESTAMPTZ,

    last_login_at       TIMESTAMPTZ,
    email_verified_at   TIMESTAMPTZ,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    deleted_at          TIMESTAMPTZ,

    CONSTRAINT chk_users_email CHECK (email ~* '^[^@\s]+@[^@\s]+\.[^@\s]+$'),
    CONSTRAINT chk_users_mfa CHECK (
        (mfa_enabled = FALSE) OR (mfa_enabled = TRUE AND mfa_secret IS NOT NULL)
    )
);

CREATE INDEX idx_users_email      ON users(email);
CREATE INDEX idx_users_role       ON users(role);
CREATE INDEX idx_users_active     ON users(is_active) WHERE deleted_at IS NULL;


-- ============================================================================
-- TABLE 2: permissions
-- ============================================================================

CREATE TABLE permissions (
    id              BIGSERIAL PRIMARY KEY,
    slug            VARCHAR(100) UNIQUE NOT NULL,   -- e.g. 'asset.create'
    resource        VARCHAR(50)  NOT NULL,          -- e.g. 'asset'
    action          VARCHAR(50)  NOT NULL,          -- e.g. 'create'
    description     TEXT,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT uq_permissions_resource_action UNIQUE (resource, action)
);


-- ============================================================================
-- TABLE 3: role_permissions  (RBAC matrix)
-- ============================================================================

CREATE TABLE role_permissions (
    id              BIGSERIAL PRIMARY KEY,
    role            user_role NOT NULL,
    permission_id   BIGINT NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    granted_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT uq_role_permission UNIQUE (role, permission_id)
);

CREATE INDEX idx_role_permissions_role ON role_permissions(role);


-- ============================================================================
-- TABLE 4: asset_categories
-- ============================================================================

CREATE TABLE asset_categories (
    id                      BIGSERIAL PRIMARY KEY,
    parent_id               BIGINT REFERENCES asset_categories(id) ON DELETE SET NULL,
    code                    VARCHAR(30) UNIQUE NOT NULL,
    name                    VARCHAR(150) NOT NULL,
    description             TEXT,
    default_useful_life_yrs INTEGER,
    default_salvage_rate    NUMERIC(5,4) DEFAULT 0.0000,
    created_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT chk_category_life    CHECK (default_useful_life_yrs IS NULL OR default_useful_life_yrs > 0),
    CONSTRAINT chk_category_salvage CHECK (default_salvage_rate >= 0 AND default_salvage_rate < 1),
    CONSTRAINT chk_category_no_self_parent CHECK (parent_id IS NULL OR parent_id <> id)
);

CREATE INDEX idx_categories_parent ON asset_categories(parent_id);


-- ============================================================================
-- TABLE 5: locations
-- ============================================================================

CREATE TABLE locations (
    id              BIGSERIAL PRIMARY KEY,
    parent_id       BIGINT REFERENCES locations(id) ON DELETE SET NULL,
    code            VARCHAR(30) UNIQUE NOT NULL,
    name            VARCHAR(150) NOT NULL,
    building        VARCHAR(150),
    floor           VARCHAR(50),
    room            VARCHAR(50),
    address         TEXT,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT chk_location_no_self_parent CHECK (parent_id IS NULL OR parent_id <> id)
);

CREATE INDEX idx_locations_parent ON locations(parent_id);


-- ============================================================================
-- TABLE 6: assets
-- ============================================================================

CREATE TABLE assets (
    id                      BIGSERIAL PRIMARY KEY,
    asset_tag               VARCHAR(50) UNIQUE NOT NULL,     -- e.g. 'AST-015'
    serial_number           VARCHAR(120),
    name                    VARCHAR(200) NOT NULL,
    description             TEXT,

    category_id             BIGINT NOT NULL REFERENCES asset_categories(id) ON DELETE RESTRICT,
    location_id             BIGINT REFERENCES locations(id) ON DELETE SET NULL,

    manufacturer            VARCHAR(150),
    model                   VARCHAR(150),

    status                  asset_status NOT NULL DEFAULT 'active',
    condition               asset_condition NOT NULL DEFAULT 'good',

    -- Acquisition & depreciation
    acquisition_date        DATE NOT NULL,
    acquisition_cost        NUMERIC(14,2) NOT NULL,
    supplier                VARCHAR(200),
    purchase_order_ref      VARCHAR(100),
    useful_life_years       INTEGER NOT NULL,
    salvage_value           NUMERIC(14,2) NOT NULL DEFAULT 0.00,
    current_book_value      NUMERIC(14,2),
    warranty_expiry_date    DATE,

    -- Disposal
    disposal_date           DATE,
    disposal_reason         TEXT,

    created_by              BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    deleted_at              TIMESTAMPTZ,

    CONSTRAINT chk_assets_cost          CHECK (acquisition_cost >= 0),
    CONSTRAINT chk_assets_salvage       CHECK (salvage_value >= 0 AND salvage_value <= acquisition_cost),
    CONSTRAINT chk_assets_life          CHECK (useful_life_years > 0),
    CONSTRAINT chk_assets_warranty      CHECK (warranty_expiry_date IS NULL OR warranty_expiry_date >= acquisition_date),
    CONSTRAINT chk_assets_disposal_date CHECK (disposal_date IS NULL OR disposal_date >= acquisition_date),
    CONSTRAINT chk_assets_disposed      CHECK (
        (status <> 'disposed') OR (status = 'disposed' AND disposal_date IS NOT NULL)
    )
);

CREATE INDEX idx_assets_tag        ON assets(asset_tag);
CREATE INDEX idx_assets_status     ON assets(status) WHERE deleted_at IS NULL;
CREATE INDEX idx_assets_category   ON assets(category_id);
CREATE INDEX idx_assets_location   ON assets(location_id);
CREATE INDEX idx_assets_serial     ON assets(serial_number);


-- ============================================================================
-- TABLE 7: asset_assignments
-- ============================================================================

CREATE TABLE asset_assignments (
    id                  BIGSERIAL PRIMARY KEY,
    asset_id            BIGINT NOT NULL REFERENCES assets(id) ON DELETE CASCADE,
    holder_id           BIGINT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    assigned_by         BIGINT REFERENCES users(id) ON DELETE SET NULL,

    status              assignment_status NOT NULL DEFAULT 'pending_acknowledgement',
    assigned_at         TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    acknowledged_at     TIMESTAMPTZ,
    returned_at         TIMESTAMPTZ,

    assignment_notes    TEXT,
    return_condition    asset_condition,
    return_notes        TEXT,

    created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT chk_assignment_ack CHECK (
        (status <> 'acknowledged') OR acknowledged_at IS NOT NULL
    ),
    CONSTRAINT chk_assignment_return CHECK (
        (status <> 'returned') OR returned_at IS NOT NULL
    ),
    CONSTRAINT chk_assignment_dates CHECK (
        returned_at IS NULL OR returned_at >= assigned_at
    )
);

-- One active holder per asset at a time
CREATE UNIQUE INDEX uq_asset_active_assignment
    ON asset_assignments(asset_id)
    WHERE status IN ('pending_acknowledgement', 'acknowledged');

CREATE INDEX idx_assignments_holder ON asset_assignments(holder_id);
CREATE INDEX idx_assignments_asset  ON asset_assignments(asset_id);


-- ============================================================================
-- TABLE 8: maintenance_schedules
-- ============================================================================

CREATE TABLE maintenance_schedules (
    id                      BIGSERIAL PRIMARY KEY,
    asset_id                BIGINT NOT NULL REFERENCES assets(id) ON DELETE CASCADE,
    title                   VARCHAR(200) NOT NULL,
    description             TEXT,
    maintenance_type        maintenance_type NOT NULL DEFAULT 'preventive',
    frequency               schedule_frequency NOT NULL,
    interval_days           INTEGER NOT NULL,

    start_date              DATE NOT NULL,
    end_date                DATE,
    next_due_date           DATE NOT NULL,
    last_generated_at       TIMESTAMPTZ,

    default_assignee_id     BIGINT REFERENCES users(id) ON DELETE SET NULL,
    estimated_duration_hrs  NUMERIC(6,2),
    is_active               BOOLEAN NOT NULL DEFAULT TRUE,

    created_by              BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT chk_schedule_interval CHECK (interval_days > 0),
    CONSTRAINT chk_schedule_dates    CHECK (end_date IS NULL OR end_date >= start_date),
    CONSTRAINT chk_schedule_duration CHECK (estimated_duration_hrs IS NULL OR estimated_duration_hrs > 0)
);

CREATE INDEX idx_schedules_asset    ON maintenance_schedules(asset_id);
CREATE INDEX idx_schedules_due      ON maintenance_schedules(next_due_date) WHERE is_active = TRUE;


-- ============================================================================
-- TABLE 9: work_orders
-- ============================================================================

CREATE TABLE work_orders (
    id                      BIGSERIAL PRIMARY KEY,
    work_order_number       VARCHAR(50) UNIQUE NOT NULL,     -- e.g. 'WO-2026-0142'
    asset_id                BIGINT NOT NULL REFERENCES assets(id) ON DELETE RESTRICT,
    schedule_id             BIGINT REFERENCES maintenance_schedules(id) ON DELETE SET NULL,

    title                   VARCHAR(200) NOT NULL,
    description             TEXT,
    maintenance_type        maintenance_type NOT NULL,
    priority                priority_level NOT NULL DEFAULT 'medium',
    status                  work_order_status NOT NULL DEFAULT 'pending',

    requested_by            BIGINT REFERENCES users(id) ON DELETE SET NULL,
    assigned_to             BIGINT REFERENCES users(id) ON DELETE SET NULL,
    approved_by             BIGINT REFERENCES users(id) ON DELETE SET NULL,

    reported_at             TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    due_date                TIMESTAMPTZ NOT NULL,
    started_at              TIMESTAMPTZ,
    completed_at            TIMESTAMPTZ,

    -- SLA
    sla_id                  BIGINT,      -- FK added after sla_policies is created
    sla_breached            BOOLEAN NOT NULL DEFAULT FALSE,
    sla_breached_at         TIMESTAMPTZ,

    estimated_cost          NUMERIC(14,2),
    actual_cost             NUMERIC(14,2),
    downtime_hours          NUMERIC(8,2),

    resolution_notes        TEXT,
    created_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT chk_wo_costs      CHECK (
        (estimated_cost IS NULL OR estimated_cost >= 0) AND
        (actual_cost    IS NULL OR actual_cost    >= 0)
    ),
    CONSTRAINT chk_wo_downtime   CHECK (downtime_hours IS NULL OR downtime_hours >= 0),
    CONSTRAINT chk_wo_started    CHECK (started_at IS NULL OR started_at >= reported_at),
    CONSTRAINT chk_wo_completed  CHECK (completed_at IS NULL OR completed_at >= reported_at),
    CONSTRAINT chk_wo_completion CHECK (
        (status <> 'completed') OR (completed_at IS NOT NULL AND assigned_to IS NOT NULL)
    ),
    CONSTRAINT chk_wo_assigned   CHECK (
        (status NOT IN ('assigned', 'in_progress')) OR assigned_to IS NOT NULL
    ),
    CONSTRAINT chk_wo_breach     CHECK (
        (sla_breached = FALSE) OR (sla_breached = TRUE AND sla_breached_at IS NOT NULL)
    )
);

CREATE INDEX idx_wo_asset     ON work_orders(asset_id);
CREATE INDEX idx_wo_status    ON work_orders(status);
CREATE INDEX idx_wo_assignee  ON work_orders(assigned_to);
CREATE INDEX idx_wo_due       ON work_orders(due_date) WHERE status NOT IN ('completed', 'cancelled');
CREATE INDEX idx_wo_number    ON work_orders(work_order_number);


-- ============================================================================
-- TABLE 10: maintenance_history
-- ============================================================================

CREATE TABLE maintenance_history (
    id                  BIGSERIAL PRIMARY KEY,
    work_order_id       BIGINT NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
    asset_id            BIGINT NOT NULL REFERENCES assets(id) ON DELETE CASCADE,
    technician_id       BIGINT REFERENCES users(id) ON DELETE SET NULL,

    performed_at        TIMESTAMPTZ NOT NULL,
    maintenance_type    maintenance_type NOT NULL,

    work_performed      TEXT NOT NULL,
    parts_replaced      TEXT,
    technician_notes    TEXT,

    labour_hours        NUMERIC(8,2),
    labour_cost         NUMERIC(14,2) DEFAULT 0.00,
    parts_cost          NUMERIC(14,2) DEFAULT 0.00,
    other_cost          NUMERIC(14,2) DEFAULT 0.00,
    total_cost          NUMERIC(14,2) GENERATED ALWAYS AS
                        (COALESCE(labour_cost,0) + COALESCE(parts_cost,0) + COALESCE(other_cost,0)) STORED,

    condition_before    asset_condition,
    condition_after     asset_condition,

    created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT chk_history_costs CHECK (
        COALESCE(labour_cost,0) >= 0 AND
        COALESCE(parts_cost,0)  >= 0 AND
        COALESCE(other_cost,0)  >= 0
    ),
    CONSTRAINT chk_history_hours CHECK (labour_hours IS NULL OR labour_hours >= 0)
);

CREATE INDEX idx_history_asset  ON maintenance_history(asset_id);
CREATE INDEX idx_history_wo     ON maintenance_history(work_order_id);
CREATE INDEX idx_history_date   ON maintenance_history(performed_at);


-- ============================================================================
-- TABLE 11: sla_policies
-- ============================================================================

CREATE TABLE sla_policies (
    id                      BIGSERIAL PRIMARY KEY,
    name                    VARCHAR(150) NOT NULL,
    description             TEXT,
    maintenance_type        maintenance_type,
    priority                priority_level NOT NULL,
    category_id             BIGINT REFERENCES asset_categories(id) ON DELETE CASCADE,

    response_time_hours     INTEGER NOT NULL,
    resolution_time_hours   INTEGER NOT NULL,

    is_active               BOOLEAN NOT NULL DEFAULT TRUE,
    created_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT chk_sla_response   CHECK (response_time_hours > 0),
    CONSTRAINT chk_sla_resolution CHECK (resolution_time_hours >= response_time_hours),
    CONSTRAINT uq_sla_scope UNIQUE (priority, maintenance_type, category_id)
);

ALTER TABLE work_orders
    ADD CONSTRAINT fk_wo_sla FOREIGN KEY (sla_id)
    REFERENCES sla_policies(id) ON DELETE SET NULL;


-- ============================================================================
-- TABLE 12: media  (AWS S3 file references — polymorphic)
-- ============================================================================

CREATE TABLE media (
    id                  BIGSERIAL PRIMARY KEY,

    -- Polymorphic owner
    mediable_type       mediable_type NOT NULL,
    mediable_id         BIGINT NOT NULL,

    -- S3 storage
    s3_bucket           VARCHAR(150) NOT NULL,
    s3_key              VARCHAR(500) UNIQUE NOT NULL,
    s3_region           VARCHAR(50)  NOT NULL DEFAULT 'ap-southeast-2',

    original_filename   VARCHAR(255) NOT NULL,
    mime_type           VARCHAR(100) NOT NULL,
    file_size_bytes     BIGINT NOT NULL,
    checksum_sha256     CHAR(64),

    -- Image-specific (NULL for non-images)
    width_px            INTEGER,
    height_px           INTEGER,

    is_primary          BOOLEAN NOT NULL DEFAULT FALSE,
    caption             VARCHAR(255),
    sort_order          INTEGER NOT NULL DEFAULT 0,

    uploaded_by         BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    deleted_at          TIMESTAMPTZ,

    -- Max 10 MB per file
    CONSTRAINT chk_media_size CHECK (file_size_bytes > 0 AND file_size_bytes <= 10485760),

    -- Only these MIME types are accepted
    CONSTRAINT chk_media_mime CHECK (
        mime_type IN (
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/heic',
            'application/pdf'
        )
    ),

    -- Images must carry dimensions; PDFs must not
    CONSTRAINT chk_media_dimensions CHECK (
        (mime_type = 'application/pdf' AND width_px IS NULL AND height_px IS NULL)
        OR
        (mime_type <> 'application/pdf' AND width_px > 0 AND height_px > 0)
    ),

    CONSTRAINT chk_media_sort CHECK (sort_order >= 0)
);

-- Exactly one primary image per owning record
CREATE UNIQUE INDEX uq_media_primary
    ON media(mediable_type, mediable_id)
    WHERE is_primary = TRUE AND deleted_at IS NULL;

CREATE INDEX idx_media_owner ON media(mediable_type, mediable_id) WHERE deleted_at IS NULL;
CREATE INDEX idx_media_key   ON media(s3_key);


-- ============================================================================
-- TABLE 13: notifications
-- ============================================================================

CREATE TABLE notifications (
    id                  BIGSERIAL PRIMARY KEY,
    recipient_id        BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,

    type                VARCHAR(100) NOT NULL,   -- e.g. 'work_order.assigned'
    channel             notification_channel NOT NULL DEFAULT 'in_app',
    title               VARCHAR(200) NOT NULL,
    body                TEXT NOT NULL,

    -- Optional deep link to a record
    related_type        VARCHAR(50),
    related_id          BIGINT,

    is_read             BOOLEAN NOT NULL DEFAULT FALSE,
    read_at             TIMESTAMPTZ,
    emailed_at          TIMESTAMPTZ,

    created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT chk_notification_read CHECK (
        (is_read = FALSE AND read_at IS NULL) OR (is_read = TRUE AND read_at IS NOT NULL)
    ),
    CONSTRAINT chk_notification_related CHECK (
        (related_type IS NULL AND related_id IS NULL) OR
        (related_type IS NOT NULL AND related_id IS NOT NULL)
    )
);

CREATE INDEX idx_notifications_recipient ON notifications(recipient_id, is_read);
CREATE INDEX idx_notifications_created   ON notifications(created_at DESC);


-- ============================================================================
-- TABLE 14: audit_logs
-- ============================================================================

CREATE TABLE audit_logs (
    id              BIGSERIAL PRIMARY KEY,
    user_id         BIGINT REFERENCES users(id) ON DELETE SET NULL,

    action          VARCHAR(50)  NOT NULL,   -- 'create' | 'update' | 'delete' | 'login' | 'export'
    entity_type     VARCHAR(50)  NOT NULL,   -- 'asset' | 'work_order' | 'user' | ...
    entity_id       BIGINT,

    old_values      JSONB,
    new_values      JSONB,

    ip_address      INET,
    user_agent      TEXT,

    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_audit_user    ON audit_logs(user_id);
CREATE INDEX idx_audit_entity  ON audit_logs(entity_type, entity_id);
CREATE INDEX idx_audit_created ON audit_logs(created_at DESC);


-- ============================================================================
-- TABLE 15: depreciation_records
-- ============================================================================

CREATE TABLE depreciation_records (
    id                      BIGSERIAL PRIMARY KEY,
    asset_id                BIGINT NOT NULL REFERENCES assets(id) ON DELETE CASCADE,

    period_year             INTEGER NOT NULL,
    period_month            INTEGER NOT NULL,

    opening_book_value      NUMERIC(14,2) NOT NULL,
    depreciation_amount     NUMERIC(14,2) NOT NULL,
    accumulated_depreciation NUMERIC(14,2) NOT NULL,
    closing_book_value      NUMERIC(14,2) NOT NULL,

    method                  VARCHAR(30) NOT NULL DEFAULT 'straight_line',
    calculated_at           TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT uq_depreciation_period UNIQUE (asset_id, period_year, period_month),
    CONSTRAINT chk_depr_month  CHECK (period_month BETWEEN 1 AND 12),
    CONSTRAINT chk_depr_year   CHECK (period_year BETWEEN 1900 AND 2200),
    CONSTRAINT chk_depr_values CHECK (
        depreciation_amount >= 0 AND
        closing_book_value  >= 0 AND
        opening_book_value  >= 0
    ),
    CONSTRAINT chk_depr_method CHECK (method IN ('straight_line', 'declining_balance', 'sum_of_years'))
);

CREATE INDEX idx_depreciation_asset  ON depreciation_records(asset_id);
CREATE INDEX idx_depreciation_period ON depreciation_records(period_year, period_month);


-- ============================================================================
-- TABLE 16: password_reset_tokens
-- ============================================================================

CREATE TABLE password_reset_tokens (
    id          BIGSERIAL PRIMARY KEY,
    user_id     BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    token_hash  VARCHAR(255) NOT NULL,
    expires_at  TIMESTAMPTZ NOT NULL,
    used_at     TIMESTAMPTZ,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT chk_reset_expiry CHECK (expires_at > created_at)
);

CREATE INDEX idx_reset_user  ON password_reset_tokens(user_id);
CREATE INDEX idx_reset_token ON password_reset_tokens(token_hash);


-- ============================================================================
-- TRIGGER: auto-update updated_at
-- ============================================================================

CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DO $$
DECLARE
    t TEXT;
BEGIN
    FOREACH t IN ARRAY ARRAY[
        'users', 'asset_categories', 'locations', 'assets',
        'asset_assignments', 'maintenance_schedules', 'work_orders',
        'sla_policies', 'media'
    ]
    LOOP
        EXECUTE format(
            'CREATE TRIGGER trg_%I_updated_at
             BEFORE UPDATE ON %I
             FOR EACH ROW EXECUTE FUNCTION set_updated_at();', t, t
        );
    END LOOP;
END $$;
